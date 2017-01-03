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
     * key of the child units section
     */
    const SECTION_KEY_UNITS = 'units';

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
        $this->beginSection(self::SECTION_KEY_UNITS);

        if ($this->item->hasChildren()) {
            // list child units if any
            foreach ($this->item->children as $unit) {
                $this->addChildUnit($unit);
            }
        } else {
            // show info box if no child units added yet
            $this->addEmptySectionBox(self::SECTION_KEY_UNITS);
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

        // links
        $this->addChildLinkBar();

        // title
        $this->out->addHTML('<div class="title">');
        $this->out->addWikiText('[[' . $unit->title . '|'. $unit->title->getSubpageText() . ']]');
        $this->out->addHTML('</div>');

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

    protected function addChildLinkBar() {
        global $wgMOOCImagePath;
        $iconSize = '32px';
        $this->out->addHTML('<div class="links">');

        // video
        $icVideo = $wgMOOCImagePath . $this->getSectionIconFilename(self::SECTION_KEY_VIDEO);
        $titleVideo = $this->loadMessage('link-child-unit-video');
        // TODO absolute href
        $this->out->addHTML("<a href=\"{$this->item->title}#video\">");
        // TODO do this in CSS only
        $this->out->addHTML("<img src=\"$icVideo\" width=\"$iconSize\" height=\"$iconSize\" title=\"$titleVideo\" alt=\"$titleVideo\" />");
        $this->out->addHTML("</a>");

        // TODO other sections

        $this->out->addHTML('</div>');
    }

    protected function addSectionActions($sectionKey, $sectionName) {
        switch ($sectionKey) {
            case self::SECTION_KEY_UNITS:
                // TODO add button "Add Unit"

            default:
                parent::addSectionActions($sectionKey, $sectionName);
        }
    }

    protected function getSectionIconFilename($sectionKey) {
        switch ($sectionKey) {
            case self::SECTION_KEY_UNITS:
                return parent::getSectionIconFilename('children');

            default:
                return parent::getSectionIconFilename($sectionKey);
        }
    }
}
