<?php

class MoocContentRenderer {

    private $out;

    private $item;

    public function __construct($item) {
        $this->item = $item;
        $this->out = new OutputPage();
        $this->out->enableTOC(false);
        
        // FIXME enable caching
        $this->out->enableClientCache(false);
    }

    public function render() {
        $this->out->addHTML('<div id="mooc">');
        
        // navigation
        $this->out->addHTML('<div id="mooc-navigation-bar" class="col-xs-12 col-sm-3">');
        $structure = $this->loadStructure($this->item);
        $this->addNavigation($structure);
        $this->out->addHTML('</div>');
        
        $this->out->addHTML('<div id="mooc-content" class="col-xs-12 col-sm-9">');
        // sections
        $this->out->addHTML('<div id="mooc-sections">');
        $this->addLearningGoalsSection($this->item);
        $this->addVideoSection($this->item);
        $this->addScriptSection($this->item);
        $this->addQuizSection($this->item);
        $this->addFurtherReadingSection($this->item);
        // TODO show info box if no content -- if allowed ever
        $this->out->addHTML('</div>');
        
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    private function addLearningGoalsSection($item) {
        $this->beginSection('learning-goals');
        
        $learningGoals = '';
        foreach ($item->getLearningGoals() as $learningGoal) {
            $learningGoals .= "\n" . '# ' . $learningGoal;
        }
        $this->out->addWikiText($learningGoals);
        
        $this->endSection();
    }

    private function addVideoSection($item) {
        $this->beginSection('video');
        
        $this->out->addWikiText('[[File:' . $item->getVideo() . ']]');
        
        $this->endSection();
    }

    private function addScriptSection($item) {
        $this->beginSection('script');
        
        $this->out->addWikiText('{{:' . $item->getScriptTitle() . '}}');
        
        $this->endSection();
    }

    private function addQuizSection($item) {
        $this->beginSection('quiz');
        
        $this->out->addWikiText('{{:' . $item->getQuizTitle() . '}}');
        
        $this->endSection();
    }

    private function addFurtherReadingSection($item) {
        $this->beginSection('further-reading');
        
        $furtherReading = '';
        foreach ($item->getFurtherReading() as $furtherReadingEntry) {
            $furtherReading .= "\n" . '# ' . $furtherReadingEntry;
        }
        $this->out->addWikiText($furtherReading);
        
        $this->endSection();
    }

    protected function beginSection($sectionKey) {
        $this->out->addHTML('<div id="' . $sectionKey . '" class="section">');
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

    protected function addNavigation($baseHeader) {
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
        // TODO
        
        $this->out->addHTML('</div>');
    }

    private function loadStructure($item) {
        return MoocContentStructureProvider::loadMoocStructure($this->item);
    }

    private function loadMessage($key, ...$params) {
        $key = 'mwe-mooc-' . $key;
        $wfMessage = wfMessage($key, $params);
        return $wfMessage->text();
    }

    public function getHTML() {
        return $this->out->getHTML();
    }
}
