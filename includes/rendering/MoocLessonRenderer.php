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
                $this->addChildItem($unit);
            }
        } else {
            // show info box if no child units added yet
            $this->addEmptySectionBox($sectionKey);
        }
        // TODO add controls to add units somewhere

        $this->endSection();
    }

    protected function addChildItem() {
        // TODO implement
    }
}