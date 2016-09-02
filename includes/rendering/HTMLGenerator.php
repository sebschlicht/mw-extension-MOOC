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
        $sections = [];
        $h2s = $this->dom->getElementsByTagName('h2');
        foreach ($h2s as $h2) {
            // build section content
            $sectionContent = $this->createElement('div', [
                'class' => 'content'
            ]);
            $element = $h2->nextSibling;
            while ($element !== null) {
                if ($element->nodeName === 'h2') {
                    break;
                }
                // TODO move element to new destination
                if ($element->parent) {
                    $element->parent->removeChild($element);
                }
                $sectionContent->appendChild($element);
                $element = $element->nextSibling;
            }
            
            // build section header
            $sectionHeader = $this->createElement('div', [
                'class' => 'header'
            ]);
            if ($h2->parent) {
                $h2->parent->removeChild($h2);
            }
            $sectionHeader->appendChild($h2);
            
            // build section
            $section = $this->createElement('div', [
                'class' => 'section'
            ]);
            $section->appendChild($sectionHeader);
            $section->appendChild($sectionContent);
            array_push($sections, $section);
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
