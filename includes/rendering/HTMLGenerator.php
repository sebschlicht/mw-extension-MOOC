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
        $linkNode = $this->dom->createDocumentFragment();
        $linkNode->appendXML($this->outputPage->parseInline($wikiText));
        return $linkNode;
    }

    public function buildSections() {
        $sections = $this->createElement('div', [
            'id' => 'sections'
        ]);
        
        $h2s = $this->dom->getElementsByTagName('h2');
        foreach ($h2s as $h2) {
            // TODO filter top-level h2 (parent is #mw-content-text)
            $sections->nodeValue .= $h2->firstChild->nodeValue . '<br>';
            
            $element = $h2->nextSibling;
            while ($element !== null && $element->nodeName !== 'h2') {
                $element = $element->nextSibling;
            }
        }
        
        if ($h2s->length > 0) {
            $element = $h2s->item(1); // TODO find more fail safe way to skip TOC
            $section = null;
            $sectionContent = null;
            
            while ($element !== null) {
                switch ($element->nodeName) {
                    case 'h2':
                        $sectionHeading = $element;
                        $element = $element->nextSibling;
                        
                        // start new section
                        $section = $this->createElement('div', 
                            [
                                'class' => 'section'
                            ]);
                        $sections->appendChild($section);
                        // header
                        $sectionHeader = $this->createElement('div', 
                            [
                                'class' => 'header'
                            ]);
                        $sectionHeader->appendChild($sectionHeading);
                        $section->appendChild($sectionHeader);
                        // content
                        $sectionContent = $this->createElement('div', 
                            [
                                'class' => 'content'
                            ]);
                        $section->appendChild($sectionContent);
                        break;
                    
                    default:
                        $contentItem = $element;
                        $element = $element->nextSibling;
                        $sectionContent->appendChild($contentItem);
                        break;
                }
            }
        }
        return $sections;
    }

    public function getSectionName($node) {
        $headline = $node->firstChild;
        // TODO ensure this is correct via testing
        return $headline->nodeValue;
    }

    public function extractChildren($text) {
        $html = $this->outputPage->parseInline($text);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        // check sections
        $children = [];
        $sections = $dom->getElementsByTagName('h2');
        foreach ($sections as $section) {
            switch (strtolower($this->getSectionName($section))) {
                // TODO internationalisation
                case 'lessons':
                case 'units':
                    // load subsections as children
                    $current = $section->nextSibling;
                    while ($current != null) {
                        if ($current->nodeName === 'h2') {
                            break;
                        } elseif ($current->nodeName === 'h3') {
                            array_push($children, $this->getSectionName($current));
                        }
                        $current = $current->nextSibling;
                    }
            }
        }
        return $children;
    }
}
