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
    public static function loadMoocStructure(&$item) {
        $rootTitle = $item->title->getRootTitle();
        // TODO use getSubpages (once working) to fetch subpages and build a query to get their content?
        // TODO if not working (e.g delayed) fetch all pages LIKE title/*, filter children and query their content
        $baseItem = self::loadMoocStructureFromTitle($rootTitle);
        $item->baseItem = $baseItem;

        $queue = [ $baseItem ];
        while (!empty($queue)) {
            $crr = array_pop($queue); // LIFO until reversing array

            // check if item is what we are searching for
            if ($item->title->equals($crr->title)) {
                $item->children = $crr->children;
                return;
            }
            // continue with its children
            if ($crr->hasChildren()) {
                foreach ($crr->children as $child) {
                    array_push($queue, $child);
                }
            }
        }
        /*
         * approaches:
         * 1. single db-query to identifty sub-titles with appropriate content model
         * 2. use explicit child items that are sub-titles a) either using a single db-query or b) recursive queries
         *
         * currently: 2b (worst performance)
         * TODO use what is most reasonable
         */
    }

    /**
     * Loads the whole structure of a MOOC item (all its children down to the units).
     *
     * @param Title $title title of the MOOC item
     * @return MoocItem MOOC item
     */
    private static function loadMoocStructureFromTitle(&$title) {
        // load single page from DB
        $text = MoocContentStructureProvider::loadPageText($title);
        // load MoocItem from page content (JSON)
        $contentModel = new MoocContent($text);
        $item = $contentModel->loadItem();
        $item->setTitle($title);

        // recursively load children via title
        $children = [];
        foreach ($item->childNames as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $childStructure = MoocContentStructureProvider::loadMoocStructureFromTitle($childTitle);
            array_push($children, $childStructure);
        }
        $item->children = $children;
        return $item;
    }

    /**
     * Loads the content of a single page from the database.
     *
     * @param Title $title page title
     * @return string page content loaded from the database
     */
    private static function loadPageText($title) {
        $db = wfGetDB(DB_SLAVE);
        // TODO try this neat (cached) function
        //$rev = Revision::loadFromTitle($db, $title);
        //return $rev->getContent()->getNativeData();
        $row = $db->select(array(
            'text',
            'revision'
        ), array(
            'old_text'
        ), array(
            'rev_id' => $title->getLatestRevID()
        ), __METHOD__, array(), 
            array(
                'revision' => array(
                    'INNER JOIN',
                    array(
                        'old_id=rev_text_id'
                    )
                )
            ));
        return $row->current()->old_text;
    }
}
