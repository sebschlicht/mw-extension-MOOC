<?php

class MoocStructureProvider {

    private $outputPage;

    private $htmlGen;

    private $item;

    function __construct() {
        $this->outputPage = new OutputPage();
        $this->htmlGen = new HTMLGenerator($this->outputPage, '');
    }

    /**
     * Loads the MOOC structure.
     * This is used to create the navigation menu.
     * The MOOC structure basically consists of {@link MoocItemHeader }s that hold basic information about the
     * respective item. However, items that should be linked to with a thumbnail of their video (e.g. children or
     * previous/next item) need to be provided as {@link PreviewItemHeader }.
     *
     * @param string $item
     *            title of the current MOOC item
     * @param string $base
     *            title of the root MOOC item
     */
    public function loadMoocStructure($item, $base) {
        $this->item = $item;
        // TODO start with root MOOC item
        return $this->loadMoocStructureItem($item);
    }

    private function loadMoocStructureItem($title) {
        $item = PreviewItemHeader::newFromTitle($title);
        $text = $this->loadPageText($title);
        
        $html = $this->outputPage->parseInline($text);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        $video = null;
        $childNames = [];
        $sections = $dom->getElementsByTagName('h2');
        foreach ($sections as $section) {
            switch (strtolower($this->htmlGen->getSectionName($section))) {
                // FIXME internationalisation
                case 'lessons':
                case 'units':
                    // load subsections as children
                    $current = $section->nextSibling;
                    while ($current != null) {
                        if ($current->nodeName === 'h2') {
                            break;
                        } elseif ($current->nodeName === 'h3') {
                            array_push($childNames, $this->htmlGen->getSectionName($current));
                        }
                        $current = $current->nextSibling;
                    }
                    break;
                
                case 'video':
                    $video = null;
                    break;
            }
        }
        if ($video !== null) {
            // TODO build WikiText
            $item->setThumbnail($video);
        }
        
        $childItems = [];
        foreach ($childNames as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $childItem = $this->loadMoocStructureItem($childTitle);
            array_push($childItems, $childItem);
        }
        if (count($childItems)) {
            $item->setChildren($childItems);
        }
        return $item;
    }

    private function loadSections($dom) {}

    private function extractChildren($text) {
        // check sections
        $children = [];
        
        return $children;
    }

    private function loadPageText($title) {
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