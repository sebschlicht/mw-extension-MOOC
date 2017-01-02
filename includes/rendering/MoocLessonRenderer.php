<?php

/**
 * Renderer for MOOC lessons.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocLessonRenderer extends MoocContentRenderer {

    /**
     * Adds the MOOC lesson sections to the output.
     */
    protected function addSections() {
        $this->addLearningGoalsSection();
        $this->addVideoSection();
        $this->addScriptSection();
        $this->addQuizSection();
        $this->addChildrenSection();
        $this->addFurtherReadingSection();
    }

    protected function addChildrenSection() {
        $sectionKey = 'units';
        $this->beginSection($sectionKey);

        if ($this->item->hasChildren()) {
            // list child units if any
            foreach ($this->item->children as $unit) {
                echo "adding child...";
                $this->addChildUnit($unit);
            }
        } else {
            // show info box if no child units added yet
            $this->addEmptySectionBox($sectionKey);
        }
        // TODO add controls to add units somewhere

        $this->endSection();
    }

    /**
     * Adds an unit to the units section.
     *
     * @param MoocItem $unit unit to add
     */
    protected function addChildUnit($unit) {
        $this->out->addHTML('<div class="child unit col-xs-12">');

        // video thumbnail
        $this->out->addHTML('<div class="video-thumbnail col-xs-12 col-sm-5">');
        if (isset($unit->video)) {
            // TODO re-calc max width
            $this->out->addWikiText('[[File:' . $unit->video . '|frameless|300x170px|link=' . $unit->title . ']]');
        } else {
            // TODO make clickable without JS
            $this->out->addHTML('<span>' . $this->loadMessage('units-no-video') . '</span>');
        }
        $this->out->addHTML('</div>');
        $this->parserOutput->addLink($unit->title);

        $this->out->addHTML('<div class="col-xs-12 col-sm-7">');
        $this->out->addHTML('<div class="title">');
        $this->out->addWikiText('[[' . $unit->title . '|'. $unit->title->getSubpageText() . ']]');
        $this->out->addHTML('</div>');

        // links TODO

        // learning goals
        $learningGoals = $this->generateLearningGoalsWikiText($unit);
        if ($learningGoals != null) {
            $this->out->addWikiText($learningGoals);
        } else {
            $this->out->addHTML($this->loadMessage('section-' . 'learning-goals' . '-empty-description'));
        }

        // meta TODO

        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    protected function addSectionActions($sectionKey, $sectionName) {
        switch ($sectionKey) {
            // TODO make this a const somewhere
            case 'units':
                // TODO add button "Add Unit"

            default:
                parent::addSectionActions($sectionKey, $sectionName);
        }
    }

    protected function getSectionIconFilename($sectionKey) {
        switch ($sectionKey) {
            case 'units':
                return parent::getSectionIconFilename('children');

            default:
                return parent::getSectionIconFilename($sectionKey);
        }
    }
}
