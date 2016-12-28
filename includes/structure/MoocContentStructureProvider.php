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
     * Loads the structure of a MOOC.
     * Currently the structure contains all properties of MOOC items, thus the whole MOOC is loaded.
     * This allows to render children, previous and next items flawlessly.
     *
     * @param MoocItem $item base item of the MOOC
     * @return MoocStructureItem structure information of the MOOC base item
     */
    public static function loadMoocStructure($item) {
        $rootTitle = $item->title->getRootTitle();
        // TODO use getSubpages (once working) to fetch subpages and build a query to get their content?
        // TODO if not working (e.g delayed) fetch all pages LIKE title/*, filter children and query their content
        return self::loadMoocStructureFromTitle($rootTitle);
    }

    /**
     * Loads the structure of a MOOC item and its children.
     *
     * @param Title $title title of the MOOC item
     * @return MoocStructureItem structure information of the MOOC item
     */
    private static function loadMoocStructureFromTitle($title) {
        // load single page from DB
        $text = MoocContentStructureProvider::loadPageText($title);
        // load MoocItem from page content (JSON)
        $contentModel = new MoocContent($text);
        $item = $contentModel->loadItem();
        $item->setTitle($title);

        // recursively load children via title
        $children = [];
        foreach ($item->children as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $childStructure = MoocContentStructureProvider::loadMoocStructureFromTitle($childTitle);
            array_push($children, $childStructure);
        }
        
        return new MoocStructureItem($item, $children);
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
