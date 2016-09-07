<?php

class HTMLGenerator {

    public $outputPage;

    public $dom;

    function __construct($outputPage, $text) {
        $this->outputPage = $outputPage;
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($text);
    }

    public function generateHTML() {
        return $this->dom->saveHTML();
    }

    public function createElement($tag, $attributes) {
        $node = $this->dom->createElement($tag);
        foreach ($attributes as $key => $value) {
            $node->setAttribute($key, $value);
        }
        return $node;
    }

    public function createLink($href, $text = null) {
        $wikiText = '[[' . $href;
        if ($text !== null) {
            $wikiText .= '|' . $text;
        }
        $wikiText .= ']]';
        return $this->parseWikiText($wikiText);
    }

    public function buildSections() {
        global $wgMOOCSections;
        $sections = $this->createElement('div', [
            'id' => 'mooc-sections'
        ]);
        
        $contentRoot = $this->dom->getElementById('mw-content-text');
        $headers = $this->dom->getElementsByTagName('h2');
        $numHeaders = $headers->length;
        
        for ($i = $numHeaders - 1; $i >= 0; $i --) {
            $header = $headers->item($i);
            
            // skip non-top-level sections
            $parent = $header->parentNode;
            if ($parent === null || $parent->nodeName !== 'body') {
                continue;
            }
            
            // create section (prepending as iterating reversely)
            $section = $this->createElement('div', [
                'class' => 'section'
            ]);
            $sectionHeader = $this->createElement('div', [
                'class' => 'header'
            ]);
            $sectionContent = $this->createElement('div', [
                'class' => 'content'
            ]);
            $sections->insertBefore($section, $sections->firstChild);
            $section->appendChild($sectionHeader);
            $section->appendChild($sectionContent);
            
            // inject section header controls
            $sectionName = $this->getSectionName($header);
            $sectionKey = $this->getSectionConfig($sectionName);
            if ($sectionKey !== null) {
                $sectionConfig = $wgMOOCSections[$sectionKey];
                $section->setAttribute('id', $sectionKey);
                
                // inject action buttons
                $nActions = $this->createElement('div', [
                    'class' => 'actions'
                ]);
                $sectionHeader->appendChild($nActions);
                
                // insert section edit button
                $hrefEditSection = $header->lastChild->firstChild->nextSibling->getAttribute('href');
                $sectionEditButton = $this->createSectionEditButton($sectionName, $hrefEditSection);
                $header->removeChild($header->lastChild);
                $nActions->appendChild($sectionEditButton);
                
                // insert section header icon
                $sectionIcon = $this->createSectionHeaderIcon($sectionConfig['icon']);
                $sectionHeader->appendChild($sectionIcon);
            }
            
            // move section header
            $element = $header->nextSibling;
            $sectionHeader->appendChild($header);
            
            // move section content
            while ($element != null) {
                if ($element->nodeName !== 'h2') {
                    $temp = $element;
                    $element = $element->nextSibling;
                    $sectionContent->appendChild($temp);
                } else {
                    break;
                }
            }
        }
        return $sections;
    }

    private function createSectionEditButton($sectionName, $href) {
        $nWrapper = $this->createElement('div', [
            'class' => 'btn-edit'
        ]);
        $title = 'edit ' . strtolower($sectionName);
        // workaround: set link manually via href attribute to allow MW API links
        $wikiText = '[[File:Wikiversity-Mooc-Icon-Edit.svg|32x32px|link=Mooc|' . $title . ']]';
        $nEditBtn = $this->parseWikiText($wikiText);
        $nEditBtn = $nWrapper->appendChild($nEditBtn);
        $nEditBtn->setAttribute('href', $href);
        return $nWrapper;
    }

    private function createSectionHeaderIcon($icon) {
        $nWrapper = $this->createElement('div', [
            'class' => 'icon'
        ]);
        $wikiText = '[[File:' . $icon . '|32x32px|link=]]';
        $nIcon = $this->parseWikiText($wikiText);
        $nWrapper->appendChild($nIcon);
        return $nWrapper;
    }

    private function parseWikiText($wikiText) {
        $node = $this->dom->createDocumentFragment();
        $node->appendXML($this->outputPage->parseInline($wikiText));
        return $node;
    }

    public function getSectionName($node) {
        $headline = $node->firstChild;
        // TODO ensure this is correct via testing
        return $headline->nodeValue;
    }

    private function getSectionConfig($sectionName) {
        global $wgMOOCSections;
        foreach ($wgMOOCSections as $key => $section) {
            if (strcasecmp($section['title'], $sectionName) == 0) {
                return $key;
            }
        }
        return null;
    }
}
