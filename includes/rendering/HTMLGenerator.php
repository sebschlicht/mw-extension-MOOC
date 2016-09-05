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
            
            // move section header
            $element = $header->nextSibling;
            $sectionHeader->appendChild($header);
            // TODO remove textual edit-link
            // TODO insert edit image button
            
            // move section content
            while ($element != null) {
                if ($element->nodeName !== 'h2') {
                    $temp = $element;
                    $element = $element->nextSibling;
                    $sectionContent->appendChild($element);
                } else {
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
