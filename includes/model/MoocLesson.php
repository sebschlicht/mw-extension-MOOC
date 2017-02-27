<?php

/**
 * Lesson of a MOOC. Is part of a MOOC and contains atomic units.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocLesson extends MoocItem {

    /**
     * MOOC entity type for lessons
     */
    const ENTITY_TYPE_LESSON = 'lesson';

    /**
     * @param Title $title page title
     * @param mixed $moocContentJson JSON (associative array) representing a MOOC lesson
     */
    public function __construct($title, $moocContentJson) {
        parent::__construct($title, $moocContentJson);

        // child units
        if (array_key_exists(self::JFIELD_CHILDREN, $moocContentJson)) {
            $this->childNames = $moocContentJson[self::JFIELD_CHILDREN];
        }
    }
}
