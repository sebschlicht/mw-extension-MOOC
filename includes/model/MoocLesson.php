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

    public function __construct( $title = null ) {
        parent::__construct( self::ENTITY_TYPE_LESSON, $title );
    }

    protected function loadJson($jsonArray) {
        parent::loadJson( $jsonArray );

        // child units
        if ( array_key_exists( self::JFIELD_CHILDREN, $jsonArray ) ) {
            $this->childNames = $jsonArray[self::JFIELD_CHILDREN];
        }
    }
}
