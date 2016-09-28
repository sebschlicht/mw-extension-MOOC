<?php

class MoocContentRenderer {

    private $parserOutput;

    private $out;

    private $item;

    public function __construct(&$parserOutput, $item) {
        $this->parserOutput = $parserOutput;
        $this->item = $item;
        $this->out = new OutputPage();
        $this->out->enableTOC(false);
    }

    public function render() {
        $this->out->addHTML('<div id="mooc">');
        
        // # navigation
        $this->out->addHTML('<div id="mooc-navigation-bar" class="col-xs-12 col-sm-3">');
        $structure = MoocContentStructureProvider::loadMoocStructure($this->item);
        $this->addNavigation($structure);
        $this->out->addHTML('</div>');
        
        // # content
        $this->out->addHTML('<div id="mooc-content" class="col-xs-12 col-sm-9">');
        
        // ## sections
        $this->out->addHTML('<div id="mooc-sections">');
        $this->addLearningGoalsSection($this->item);
        $this->addVideoSection($this->item);
        $this->addScriptSection($this->item);
        $this->addQuizSection($this->item);
        $this->addFurtherReadingSection($this->item);
        $this->out->addHTML('</div>');
        
        // ## categories
        $rootTitle = $this->item->getTitle()->getRootTitle();
        $categoryNS = $rootTitle->getNsText();
        $this->out->addWikiText('[[Category:' . $categoryNS . ']]');
        $this->parserOutput->addCategory($categoryNS);
        $categoryMooc = $categoryNS . ':' . $rootTitle->getText();
        $this->out->addWikiText('[[Category:' . $categoryMooc . ']]');
        $this->parserOutput->addCategory($categoryMooc);
        
        $this->out->addHTML('</div>');
        
        $this->out->addHTML('</div>');
    }

    private function addLearningGoalsSection($item) {
        $sectionKey = 'learning-goals';
        $this->beginSection($sectionKey);
        
        if (count($item->getLearningGoals()) > 0) {
            // show learning goals as ordered list if any
            $learningGoals = '';
            foreach ($item->getLearningGoals() as $learningGoal) {
                $learningGoals .= "\n" . '# ' . $learningGoal;
            }
            $this->out->addWikiText($learningGoals);
        } else {
            // show info box if no learning goal added yet
            $this->addEmptySectionBox($sectionKey);
        }
        
        $this->endSection();
    }

    private function addVideoSection($item) {
        $sectionKey = 'video';
        $this->beginSection($sectionKey);
        
        if ($item->getVideo()) {
            // show video player if video set
            $this->out->addWikiText('[[File:' . $item->getVideo() . '|800px]]');
        } else {
            // show info box if video not set yet
            $this->addEmptySectionBox($sectionKey);
        }
        
        $this->endSection();
    }

    private function addScriptSection($item) {
        $sectionKey = 'script';
        $this->beginSection($sectionKey);
        
        if ($item->getScriptTitle()->exists()) {
            // transclude script if existing
            $this->out->addWikiText('{{:' . $item->getScriptTitle() . '}}');
        } else {
            // show info box if script not created yet
            $this->addEmptySectionBox($sectionKey, $item->getScriptTitle());
        }
        
        $this->endSection();
    }

    private function addQuizSection($item) {
        $sectionKey = 'quiz';
        $this->beginSection($sectionKey);
        
        if ($item->getQuizTitle()->exists()) {
            // transclude quiz if existing
            $this->out->addWikiText('{{:' . $item->getQuizTitle() . '}}');
        } else {
            // show info box if script not created yet
            $this->addEmptySectionBox($sectionKey, $item->getScriptTitle());
        }
        
        $this->endSection();
    }

    private function addFurtherReadingSection($item) {
        $sectionKey = 'further-reading';
        $this->beginSection($sectionKey);
        
        if (count($item->getFurtherReading()) > 0) {
            // show further reading as ordered list if any
            $furtherReading = '';
            foreach ($item->getFurtherReading() as $furtherReadingEntry) {
                $furtherReading .= "\n" . '# ' . $furtherReadingEntry;
            }
            $this->out->addWikiText($furtherReading);
        } else {
            // show info box if no further reading added yet
            $this->addEmptySectionBox($sectionKey);
        }
        
        $this->endSection();
    }

    protected function addEmptySectionBox($sectionKey, ...$params) {
        // TODO can we automatically prefix classes/ids? at least in LESS?
        $this->out->addHTML('<div class="section-empty-box">');
        
        $this->out->addHTML('<span class="description">');
        $this->out->addHTML($this->loadMessage('section-' . $sectionKey . '-empty-description', $params));
        $this->out->addHTML('</span> ');
        $this->out->addHTML('<a class="edit-link">');
        $this->out->addHTML($this->loadMessage('section-' . $sectionKey . '-empty-edit-link'));
        $this->out->addHTML('</a>');
        // TODO do we need an additional text to point at external resources such as /script or general hints?
        
        $this->out->addHTML('</div>');
    }

    protected function beginSection($sectionKey) {
        global $wgMOOCSectionConfig;
        $sectionConfig = $wgMOOCSectionConfig[$sectionKey];
        
        $classes = 'section';
        // trigger collapsing of selected, large sections
        if ($sectionConfig['collapsed']) {
            $classes .= ' default-collapsed';
        }
        
        $this->out->addHTML('<div id="' . $sectionKey . '" class="' . $classes . '">');
        $this->addSectionHeader($sectionKey);
        $this->out->addHTML('<div class="content">');
    }

    protected function addSectionHeader($sectionKey) {
        $sectionName = $this->loadMessage('section-' . $sectionKey);
        $this->out->addHTML('<div class="header">');
        
        // actions
        $this->addSectionActions($sectionKey, $sectionName);
        
        // icon
        $this->addSectionIcon($sectionKey);
        
        // heading
        $this->out->addHTML('<h2>' . ucfirst($sectionName) . '</h2>');
        
        $this->out->addHTML('</div>');
    }

    protected function addSectionActions($sectionKey, $sectionName) {
        $this->out->addHTML('<div class="actions">');
        
        // edit button
        global $wgMOOCImagePath;
        $btnHref = '/SpecialPage:MoocEdit?title=' . $this->item->getTitle() . '&section=' . $sectionKey;
        $btnTitle = $this->loadMessage('edit-section-button-title', $sectionName);
        $this->out->addHTML('<div class="btn-edit">');
        // TODO ensure to link to the special page allowing to edit this section
        // TODO replace href with link that allows tab-browsing with modal boxes
        $this->out->addHTML('<a href="' . $btnHref . '" title="' . $btnTitle . '">');
        $this->out->addHTML(
            '<img src="' . $wgMOOCImagePath . 'ic_edit.svg" width="32px" height="32px" alt="' . $btnTitle . '" />');
        $this->out->addHTML('</a>');
        $this->out->addHTML('</div>');
        
        $this->out->addHTML('</div>');
    }

    protected function addSectionIcon($sectionKey) {
        $this->out->addHTML('<div class="icon">');
        
        global $wgMOOCImagePath;
        $this->out->addHTML(
            '<img src="' . $wgMOOCImagePath . 'ic_' . $sectionKey . '.svg" width="32px" height="32px" alt="" />');
        
        $this->out->addHTML('</div>');
    }

    protected function endSection() {
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    protected function addNavigation($baseStructureItem) {
        $this->out->addHTML('<div id="mooc-navigation">');
        // header
        $title = $this->loadMessage('navigation-title');
        $this->out->addHTML('<div class="header">');
        
        // ## icon
        $this->addSectionIcon('navigation');
        
        // ## heading
        $this->out->addHTML('<h2>' . $title . '</h2>');
        
        $this->out->addHTML('</div>');
        
        // content
        $this->out->addHTML('<ul class="content">');
        $this->addNavigationItem($baseStructureItem);
        $this->out->addHTML('</ul>');
        
        $this->out->addHTML('</div>');
    }

    protected function addNavigationItem($structureItem) {
        $item = $structureItem->getItem();
        
        $this->out->addHTML('<li>');
        $this->out->addWikiText('[[' . $item->getTitle() . '|' . $item->getName() . ']]');
        // register link for interwiki meta data
        $this->parserOutput->addLink($item->getTitle());
        
        // add menu items for children - if any
        if ($item->hasChildren()) {
            $this->out->addHTML('<ul>');
            foreach ($structureItem->getChildren() as $childStructureItem) {
                $this->addNavigationItem($childStructureItem);
            }
            $this->out->addHTML('</ul>');
        }
        $this->out->addHTML('</li>');
    }

    private function loadMessage($key, ...$params) {
        $key = 'mooc-' . $key;
        $wfMessage = wfMessage($key, $params);
        return $wfMessage->text();
    }

    public function getHTML() {
        return $this->out->getHTML();
    }
}
