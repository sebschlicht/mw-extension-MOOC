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
     * MOOC item type for lessons
     */
    const ITEM_TYPE_LESSON = 'lesson';

    /**
     * @param Title $title page title
     * @param mixed $moocContentJson JSON (associative array) representing a MOOC item
     */
    public function __construct(Title $title, $moocContentJson) {
        parent::__construct($title, $moocContentJson);
        $this->children = $moocContentJson[self::JFIELD_CHILDREN];
    }
}
