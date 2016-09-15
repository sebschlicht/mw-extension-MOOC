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
            $sectionName = $this->getSectionName($header);
            $sectionKey = $this->getSectionConfig($sectionName);
            $hrefEditSection = $header->lastChild->firstChild->nextSibling->getAttribute('href');
            
            $section = $this->createElement('div', [
                'class' => 'section'
            ]);
            $sectionHeader = $this->createSectionHeader($sectionKey, $hrefEditSection);
            $sectionContent = $this->createElement('div', [
                'class' => 'content'
            ]);
            $sections->insertBefore($section, $sections->firstChild);
            $section->appendChild($sectionHeader);
            $section->appendChild($sectionContent);
            
            // remove edit section link from heading
            $header->removeChild($header->lastChild);
            
            // set section id if known
            if ($sectionKey !== null) {
                $section->setAttribute('id', 'mooc-' . $sectionKey);
                $this->setSectionName($header, $sectionKey);
                $collapseSection = $wgMOOCSections[$sectionKey]['collapsed'];
                if ($collapseSection) {
                    $section->setAttribute('class', $section->getAttribute('class') . ' default-collapsed');
                }
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
            
            // add expander
            $expander = $this->createElement('div', [
                'class' => 'expander'
            ]);
            $expander->nodeValue = $this->loadMessage('button-expand-section');
            $section->appendChild($expander);
        }
        return $sections;
    }

    public function createNavigation($mooc) {
        $navigation = $this->createElement('div', [
            'id' => 'mooc-navigation'
        ]);
        
        $header = $this->createElement('div', [
            'class' => 'header'
        ]);
        $navigation->appendChild($header);
        $headerIcon = $this->createHeaderIcon('Wikiversity-Mooc-Icon-Navigation.svg');
        $header->appendChild($headerIcon);
        $headerHeading = $this->createElement('h2', []);
        $header->appendChild($headerHeading);
        $headerText = $this->createElement('span', [
            'class' => 'mw-headline'
        ]);
        $headerText->nodeValue = $this->loadMessage('navigation-title');
        $headerHeading->appendChild($headerText);
        
        // TODO ALL lessons have to be top-level
        $nLessons = $this->createElement('ul', [
            'class' => 'content'
        ]);
        for ($i = 0; $i < 9; $i ++) {
            $nLesson = $this->createNavigationItem($mooc);
            $nLessons->appendChild($nLesson);
        }
        $navigation->appendChild($nLessons);
        
        return $navigation;
    }

    private function createNavigationItem($item) {
        $nItem = $this->createElement('li', []);
        $nItem->appendChild($this->createNavigationLink($item));
        
        if ($item->hasChildren()) {
            $nChildren = $this->createElement('ul', []);
            foreach ($item->getChildren() as $child) {
                $nChild = $this->createNavigationItem($child);
                $nChildren->appendChild($nChild);
            }
            $nItem->appendChild($nChildren);
        }
        return $nItem;
    }

    private function createNavigationLink($item) {
        return $this->createLink($item->getTitle(), $item->getName());
    }

    private function createSectionHeader($sectionKey, $hrefEditSection) {
        global $wgMOOCSections;
        
        $sectionHeader = $this->createElement('div', [
            'class' => 'header'
        ]);
        
        // inject controls
        if ($sectionKey !== null) {
            $sectionConfig = $wgMOOCSections[$sectionKey];
            
            // inject action buttons
            $nActions = $this->createElement('div', [
                'class' => 'actions'
            ]);
            $sectionHeader->appendChild($nActions);
            
            // insert section edit button
            // TODO make this work even when sectionKey null
            $sectionEditButton = $this->createSectionEditButton($sectionKey, $hrefEditSection);
            $nActions->appendChild($sectionEditButton);
            
            // insert modal edit box
            $sectionHeader->appendChild($this->createEditBox($sectionKey));
            
            // insert section header icon
            $sectionIcon = $this->createHeaderIcon($sectionConfig['icon']);
            $sectionHeader->appendChild($sectionIcon);
        }
        return $sectionHeader;
    }

    private function createSectionEditButton($sectionKey, $href) {
        $nWrapper = $this->createElement('div', [
            'class' => 'btn-edit'
        ]);
        $iSectionName = $this->loadMessage('section-' . $sectionKey);
        $iTitle = $this->loadMessage('edit-section-button-title', $iSectionName);
        
        // workaround: set link manually via href attribute to allow MW API links
        $wikiText = '[[File:Wikiversity-Mooc-Icon-Edit.svg|32x32px|link=Main|' . $iTitle . ']]';
        $nEditBtn = $this->parseWikiText($wikiText);
        $nEditBtn = $nWrapper->appendChild($nEditBtn);
        $nEditBtn->setAttribute('href', $href);
        return $nWrapper;
    }

    private function createHeaderIcon($icon) {
        $nWrapper = $this->createElement('div', [
            'class' => 'icon'
        ]);
        $wikiText = '[[File:' . $icon . '|32x32px|link=]]';
        $nIcon = $this->parseWikiText($wikiText);
        $nWrapper->appendChild($nIcon);
        return $nWrapper;
    }

    private function createEditBox($sectionKey) {
        global $wgMOOCSections;
        $sectionConfig = $wgMOOCSections[$sectionKey];
        
        // build modal box wrapper
        $nWrapper = $this->createModalBox('edit-' . $sectionKey);
        $nBox = $nWrapper->lastChild;
        
        // fill modal box with control elements
        // * button to close
        $nButtonClose = $this->createElement('button', 
            [
                'class' => 'close',
                'type' => 'button',
                'aria-label' => 'Close'
            ]);
        $nSpanClose = $this->createElement('span', [
            'aria-hidden' => 'true'
        ]);
        $nSpanClose->nodeValue = '&times;';
        $nButtonClose->appendChild($nSpanClose);
        $nBox->appendChild($nButtonClose);
        
        // * title to determine data being edited
        $title = ucfirst($this->loadMessage('section-' . $sectionKey));
        $nTitle = $this->createElement('label', [
            'for' => 'modal-box-content-' . $sectionKey
        ]);
        $nTitle->nodeValue = $title . ':';
        $nBox->appendChild($nTitle);
        
        // * text field to edit data
        $nTextfield = $this->createElement('textarea', 
            [
                'id' => 'modal-box-content-' . $sectionKey,
                'class' => 'form-control',
                'autofocus' => 'autofocus'
            ]);
        $nBox->appendChild($nTextfield);
        
        // * button to "submit form"
        $nButtonSubmit = $this->createElement('button', 
            [
                'class' => 'btn btn-primary btn-save',
                'type' => 'button'
            ]);
        $nButtonSubmit->nodeValue = $this->loadMessage('button-save-edit');
        $nBox->appendChild($nButtonSubmit);
        
        // * button to cancel
        $nButtonCancel = $this->createElement('button', 
            [
                'class' => 'btn btn-default btn-cancel',
                'type' => 'button'
            ]);
        $nButtonCancel->nodeValue = $this->loadMessage('button-cancel');
        $nBox->appendChild($nButtonCancel);
        
        return $nWrapper;
    }

    private function createModalBox($id) {
        // TODO move to JS when able to use mw.message to reduce page size for non-JS users
        $nWrapper = $this->createElement('div', [
            'class' => 'modal-box-wrapper'
        ]);
        $nBg = $this->createElement('div', [
            'class' => 'modal-box-bg'
        ]);
        $nWrapper->appendChild($nBg);
        $nBox = $this->createElement('div', 
            [
                'id' => 'modal-box-' . $id,
                'class' => 'modal-box form-group'
            ]);
        $nWrapper->appendChild($nBox);
        return $nWrapper;
    }

    public function getSectionName($node) {
        $headline = $node->firstChild;
        // TODO ensure this is correct via testing
        return $headline->nodeValue;
    }

    private function setSectionName($node, $sectionKey) {
        $headline = $node->firstChild;
        $sectionTitle = ucfirst($this->loadMessage('section-' . $sectionKey));
        $headline->nodeValue = $sectionTitle;
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

    private function loadMessage($key, ...$params) {
        $key = 'mwe-mooc-' . $key;
        $wfMessage = wfMessage($key, $params);
        return $wfMessage->text();
    }

    private function parseWikiText($wikiText) {
        $node = $this->dom->createDocumentFragment();
        $node->appendXML($this->outputPage->parseInline($wikiText));
        return $node;
    }
}
