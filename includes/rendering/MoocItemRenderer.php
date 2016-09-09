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
        $moocNavigationBar->appendChild($this->htmlGen->createNavigation($this->mooc));
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
}
