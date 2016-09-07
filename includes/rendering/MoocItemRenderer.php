<?php

class MoocItemRenderer {

    private $htmlGen;

    private $item;

    private $mooc;

    private $sections;

    function __construct($outputPage, $text) {
        $this->htmlGen = new HTMLGenerator($outputPage, $text);
        $this->item = MoocItemHeader::newFromTitle($outputPage->getTitle());
        // TODO pass root MOOC item
        $this->mooc = $this->loadMoocStructure($this->item, null);
        
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
        
        // build and inject sections
        $moocSections = $this->htmlGen->buildSections();
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

    private function loadMoocStructure($item, $base) {
        $structureProvider = new MoocStructureProvider();
        return $structureProvider->loadMoocStructure($item->getTitle(), $base);
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
