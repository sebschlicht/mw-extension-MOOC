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

    /**
     * @param Title $title resource page title
     * @param mixed $moocContentJson JSON (associative array) representing a MOOC resource
     */
    public function __construct( $title, $moocContentJson ) {
        parent::__construct( $title, $moocContentJson );

        // resource file content - if any
        if ( array_key_exists( self::JFIELD_CONTENT, $moocContentJson ) ) {
            $this->content = $moocContentJson[self::JFIELD_CONTENT];
        }
    }

    public function toJson() {
        return [
            self::JFIELD_TYPE => $this->type,
            self::JFIELD_CONTENT => $this->content
        ];
    }
}