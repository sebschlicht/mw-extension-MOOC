<?php

class MoocContentStructureProvider {

    public static function loadMoocStructure($item) {
        $baseTitle = $item->getBaseTitle();
        return MoocContentStructureProvider::loadMoocStructureFromTitle($baseTitle);
    }

    private static function loadMoocStructureFromTitle($title) {
        // TODO more performant to select all sub pages in one query and filter later on
        $itemHeader = MoocItemHeader::newFromTitle($title);
        
        $text = MoocContentStructureProvider::loadPageText($title);
        $contentModel = new MoocContent($text);
        $json = $contentModel->getJsonData();
        $item = new MoocItem($json);
        
        $children = [];
        foreach ($item->getChildren() as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $childStructure = MoocContentStructureProvider::loadMoocStructureFromTitle($childTitle);
            array_push($children, $childStructure);
        }
        $itemHeader->setChildren($children);
        
        return $itemHeader;
    }

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
