<?php

class MoocItemRenderer {

    private $htmlGen;

    private $item;

    private $mooc;

    function __construct($outputPage, $text) {
        $this->htmlGen = new HTMLGenerator($outputPage, $text);
        $this->item = Item::newFromTitle($outputPage->getTitle());
        $this->mooc = $this->loadMoocStructure($this->item);
        
        // // load sections
        // $sections = $dom->getElementsByTagName('h2');
        // foreach ($sections as $section) {
        // // mark as MOOC section and add name to class
        // $sectionClass = '';
        
        // // extract section name from root level .mw-headline
        // $sectionName = null;
        // foreach ($section->childNodes as $node) {
        // if ($node->hasAttributes()) {
        // if ($node->hasAttribute('class') && $node->getAttribute('class') == 'mw-headline') {
        // $sectionName = $node->nodeValue;
        // }
        // }
        // }
        // if ($sectionName != null) {
        // $sectionClass .= ' ' . strtolower(str_replace(' ', '-', $sectionName));
        // }
        
        // $sectionClass .= ' ' . $wgMOOCClasses['section'];
        // if ($section->hasAttribute('class'))
        // $sectionClass = $section->getAttribute('class') . $sectionClass;
        // $section->setAttribute('class', $sectionClass);
        // }
        
        // // add section markers
        // // TODO move to MOOC.php
        // $sectionMarkers = $dom->createElement("ul");
        // $dom->insertBefore($sectionMarkers, $dom->firstChild);
        // $sectionMarkers->setAttribute("class", $wgMOOCClasses['section-markers']);
        // foreach ($wgMOOCSections as $predefinedSection) {
        // $sectionMarker = $dom->createElement("li", $predefinedSection['title']);
        
        // $sectionIcon = $dom->createDocumentFragment();
        // $sectionIcon->appendXML($out->parse('[[File:' . $predefinedSection['icon'] . ']]'));
        // $sectionMarker->insertBefore($sectionIcon, $sectionMarker->firstChild);
        
        // $sectionMarkers->appendChild($sectionMarker);
        // }
        
        // new root node with Bootstrap setup
        $moocPage = $this->htmlGen->createElement('div', [
            'id' => 'mooc'
        ]);
        $moocRow = $this->htmlGen->createElement('div', [
            'class' => 'row'
        ]);
        $moocPage->appendChild($moocRow);
        
        // inject navigation bar
        $moocNavigationBar = $this->htmlGen->createElement('div', 
            [
                'id' => 'mooc-navigation-bar',
                'class' => 'col-xs-12 col-sm-3'
            ]);
        $moocNavigationBar->appendChild($this->createNavigation());
        $moocRow->appendChild($moocNavigationBar);
        
        // inject enriched content
        $moocContent = $this->htmlGen->createElement('div', 
            [
                'id' => 'mooc-content',
                'class' => 'col-xs-12 col-sm-9'
            ]);
        
        // build sections
        $moocSections = $this->htmlGen->createElement('div', [
            'id' => 'sections'
        ]);
        foreach ($this->htmlGen->buildSections() as $section) {
            $moocSections->appendChild($section);
        }
        $moocContent->appendChild($moocSections);
        
        // TODO move existing content into
        // while (($node = $this->htmlGen->dom->documentElement) !== null) {
        // $moocContent->appendChild($node);
        // }
        $moocRow->appendChild($moocContent);
        $this->htmlGen->dom->appendChild($moocPage);
    }

    public function render() {
        return $this->htmlGen->generateHTML();
    }

    private function loadMoocStructure($item) {
        // TODO load base to get root MOOC item
        return $this->loadMoocStructureItem($item->getTitle());
    }

    private function loadMoocStructureItem($title) {
        $item = Item::newFromTitle($title);
        $text = $this->loadPageText($title);
        $children = [];
        foreach ($this->htmlGen->extractChildren($text) as $childName) {
            $childTitle = Title::newFromText($title . '/' . $childName);
            $child = $this->loadMoocStructureItem($childTitle);
            array_push($children, $child);
        }
        if (count($children)) {
            $item->setChildren($children);
        }
        return $item;
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

    private function createNavigation() {
        $navigation = $this->htmlGen->createElement('div', [
            'id' => 'mooc-navigation'
        ]);
        
        // TODO ALL lessons have to be top-level
        $nLessons = $this->htmlGen->createElement('ul', []);
        $nLesson = $this->createNavigationItem($this->mooc);
        $nLessons->appendChild($nLesson);
        $navigation->appendChild($nLessons);
        
        return $navigation;
    }

    private function createNavigationItem($item) {
        $nItem = $this->htmlGen->createElement('li', []);
        $nItem->appendChild($this->createNavigationLink($item));
        
        if ($item->hasChildren()) {
            $nChildren = $this->htmlGen->createElement('ul', []);
            foreach ($item->getChildren() as $child) {
                $nChild = $this->createNavigationItem($child);
                $nChildren->appendChild($nChild);
            }
            $nItem->appendChild($nChildren);
        }
        return $nItem;
    }

    private function createNavigationLink($item) {
        return $this->htmlGen->createLink($item->getTitle(), $item->getName());
    }
}
