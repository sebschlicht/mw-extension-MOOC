<?php

/**
 * Renderer for MOOC units.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocUnitRenderer extends MoocContentRenderer {

    /**
     * Adds the MOOC unit sections to the output.
     */
    protected function addSections() {
        $this->addLearningGoalsSection();
        $this->addVideoSection();
        $this->addScriptSection();
        $this->addQuizSection();
        $this->addFurtherReadingSection();
    }
}