<?php

class MoocContentStructureProvider {

    public static function loadMoocStructure($item) {
        $rootTitle = $item->getTitle()->getRootTitle();
        // TODO use getSubpages (once working) to fetch subpages and build a query to get their content?
        // TODO if not working (e.g delayed) fetch all pages LIKE title/*, filter children and query their content
        return MoocContentStructureProvider::loadMoocStructureFromTitle($rootTitle);
    }

    /**
     * Recursively loads the whole structure of the MOOC.
     * Currently the structure contains all properties of MOOC items, thus the whole MOOC is loaded.
     * This allows to render children, previous and next items flawlessy.
     *
     * @param Title $title
     *            title of the root MOOC item
     */
    private static function loadMoocStructureFromTitle($title) {
        // load single page from DB
        $text = MoocContentStructureProvider::loadPageText($title);
        // load MoocItem from page content (JSON)
        $contentModel = new MoocContent($text);
        $json = $contentModel->getJsonData();
        $item = new MoocItem($title, $json);
        
        // recursively load children via title
        $children = [];
        foreach ($item->getChildren() as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $childStructure = MoocContentStructureProvider::loadMoocStructureFromTitle($childTitle);
            array_push($children, $childStructure);
        }
        
        return new MoocStructureItem($item, $children);
    }

    /**
     * Loads the content of a single page from the database.
     *
     * @param Title $title
     *            page title
     */
    private static function loadPageText($title) {
        $db = wfGetDB(DB_SLAVE);
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
