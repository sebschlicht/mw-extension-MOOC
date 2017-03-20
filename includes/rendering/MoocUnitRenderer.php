<?php

/**
 * Renderer for MOOC units.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocUnitRenderer extends MoocContentRenderer {

    protected function getSections() {
        return [
            self::SECTION_KEY_LEARNING_GOALS,
            self::SECTION_KEY_VIDEO,
            self::SECTION_KEY_SCRIPT,
            self::SECTION_KEY_QUIZ,
            self::SECTION_KEY_FURTHER_READING
        ];
    }
}
