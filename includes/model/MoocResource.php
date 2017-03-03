<?php

/**
 * Basic model for a resource used in a MOOC. This model is necessary to allow resource files to be saved within the
 * MOOC namespace without content model changes.
 * This is achieved by storing the resource file content within the content field of this entity.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
abstract class MoocResource extends MoocEntity {

    /**
     * JSON field identifier for the resource file content
     */
    const JFIELD_CONTENT = 'content';

    /**
     * @var string resource file content
     */
    public $content;

    protected function loadJson( $jsonArray ) {
        // resource file content - if any
        if ( array_key_exists( self::JFIELD_CONTENT, $jsonArray ) ) {
            $this->content = $jsonArray[self::JFIELD_CONTENT];
        }
    }

    public function toJson() {
        return [
            self::JFIELD_TYPE => $this->type,
            self::JFIELD_CONTENT => $this->content
        ];
    }
}