<?php

/**
 * Lesson of a MOOC. Is part of a MOOC and contains atomic units.
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 */
class MoocLesson extends MoocItem {

    const ITEM_TYPE_LESSON = 'lesson';

    public function __construct(Title $title, $moocContentJson) {
        parent::__construct($title, $moocContentJson);
        $this->children = $moocContentJson[self::JFIELD_CHILDREN];
    }
}
