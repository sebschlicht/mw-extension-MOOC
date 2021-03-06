<?php

/**
 * Structure information loader for MOOCs.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocContentStructureProvider {

    /**
     * Loads the structure of a MOOC and stores it in the MOOC item passed.
     * Currently the structure contains all properties of MOOC items, thus the whole MOOC is loaded.
     * This allows to render children, previous and next items flawlessly.
     *
     * @param MoocItem $item MOOC item that the structure is needed for
     */
    public static function loadMoocStructure(&$renderedItem) {
        // load all MOOC items from database
        $rootPageTitle = $renderedItem->title->getRootTitle()->getDBkey();
        //TODO we know the ID pretty well
        $namespace = $renderedItem->title->getNamespace();
        $contentModelId = MoocContent::CONTENT_MODEL_MOOC_ITEM;

        $db = wfGetDB(DB_SLAVE);
        $res = $db->select( array(
                'text',
                'revision',
                'page'
            ), array(
                'page_title',
                'old_text'
            ), "(page_title = '$rootPageTitle' OR page_title LIKE '$rootPageTitle/%')"
                . " AND page_namespace = '$namespace' AND page_content_model = '$contentModelId'",
            __METHOD__, array(
                'ORDER_BY' => 'page_title ASC'
            ), array(
                'page' => array(
                    'INNER JOIN',
                    array(
                        'rev_id=page_latest'
                    )
                ), 'revision' => array(
                    'INNER JOIN',
                    array(
                        'old_id=rev_text_id'
                    )
                )
            ));

        $items = [];
        foreach ( $res as $row ) {
            $contentModel = new MoocContent( $row->old_text );
            if ( $contentModel->isValid() ) {
                $item = $contentModel->loadItem();
                $item->setTitle( Title::newFromText( $row->page_title, $namespace ) );
                // this adds the rendered item as well
                array_push( $items, $item );
            }
        }

        // load structure from item titles
        //TODO this requires the items to be sorted by title while children arrays MUST maintain the original creation ordering
        $rootItem = array_shift( $items );
        if ( $rootItem === null ) {
            // the MOOC is not existing yet -> simulate MOOC overview and parental lesson, if necessary
            if ( $renderedItem instanceof  MoocOverview ) {
                $rootItem = $renderedItem;
            } else {
                $rootItem = new MoocOverview( $renderedItem->title->getRootTitle() );

                // create parental lesson if unit
                if ( $renderedItem instanceof MoocUnit ) {
                    $lesson = new MoocLesson( $renderedItem->title->getBaseTitle() );
                    array_push( $items, $lesson );
                }
                array_push( $items, $renderedItem );
            }
        }

        // build item hierarchy
        $prevLesson = null;
        foreach ( $items as $item ) {
            // determine item parent
            $parent = $rootItem;
            if ( $prevLesson !== null && $item->title->isSubpageOf( $prevLesson->title ) ) {
                // child unit of previous lesson
                $parent = $prevLesson;
            } else {
                // lesson
                $prevLesson = $item;
            }

            if ( $item instanceof MoocItem ) {
                // register item as child of parent
                if ( !isset( $parent->children ) ) {
                    $parent->children = [];
                }
                array_push( $parent->children, $item );
            } elseif ( $item instanceof MoocResource
                && $parent->title !== null && $parent->title->equals( $renderedItem->title ) ) {
                // register resource to rendered item
                if ( $item instanceof MoocScript ) {
                    $renderedItem->script = $item;
                } else if ( $item instanceof MoocQuiz ) {
                    $renderedItem->quiz = $item;
                }
            }
        }
        $renderedItem->baseItem = $rootItem;

        // inject values of the rendered item
        $queue = [$rootItem];
        while ( !empty( $queue ) ) {
            $crr = array_pop( $queue ); // LIFO until reversing array
            // check if item is what we are searching for
            if ( $renderedItem->title->equals( $crr->title ) ) {
                $renderedItem->children = $crr->children;
                return;
            } elseif ( $crr->hasChildren() ) {
                foreach ( $crr->children as $child ) {
                    array_push( $queue, $child );
                }
            }
        }
    }
}
