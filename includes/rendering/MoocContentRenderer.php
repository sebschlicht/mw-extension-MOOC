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
        $this->out->addHTML('<div id="mooc-sections">');
        
        $this->addLearningGoals($this->item);
        $this->addVideoSection($this->item);
        
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    private function addLearningGoals($item) {
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
        
        $scriptTitle = Title::newFromText($item->getTitle() . '/script');
        $this->out->addWikiText('{{:' . $scriptTitle . '}}');
        
        $this->endSection();
    }

    protected function beginSection($sectionKey) {
        $sectionTitle = ucfirst($this->loadMessage('section-' . $sectionKey));
        $this->out->addHTML('<div class="section">');
        $this->out->addHTML('<div class="header">');
        $this->out->addHTML('<h2>' . $sectionTitle . '</h2>');
        $this->out->addHTML('</div>');
        $this->out->addHTML('<div class="content">');
    }

    protected function endSection() {
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
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
