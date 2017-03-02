<?php

/**
 * Basic class for all pages/entities related to a MOOC.
 * Known entity types are:
 * <ol>
 * <li>MOOC item (unit, lesson, MOOC)</li>
 * <li>MOOC resource (script, quiz)</li>
 * </ol>
 */
abstract class MoocEntity {

    /**
     * JSON field identifier for the MOOC entity type
     */
    const JFIELD_TYPE = 'type';

    /**
     * @var Title page title
     */
    public $title;

    /**
     * @var string type of the MOOC entity
     */
    public $type;

    /**
     * Creates a new MOOC entity from JSON.
     *
     * @param Title $title page title
     * @param array $moocContentJson JSON (associative array) representing a MOOC entity
     */
    public function __construct( $title, $moocContentJson ) {
        // TODO completely separate page Title from MOOC entities?
        $this->title = $title;
        $this->type = $moocContentJson[self::JFIELD_TYPE];
    }

    /**
     * Converts this MOOC entity into JSON content.
     *
     * @return array JSON (associative array) representing a MOOC entity
     */
    abstract function toJson();

    /**
     * Loads a MOOC entity from JSON content.
     *
     * @param Title $title title of the MOOC entity page
     * @param array $moocContentJson JSON (associative array) representing a MOOC entity
     * @return MoocEntity MOOC entity instance or null on error
     */
    public static function loadFromJson( $title, $moocContentJson ) {
        if ( !array_key_exists( self::JFIELD_TYPE, $moocContentJson ) ) {
            return null;
        }

        $type = $moocContentJson[self::JFIELD_TYPE];
        switch ( $type ) {
            case MoocUnit::ENTITY_TYPE_UNIT:
                return new MoocUnit( $title, $moocContentJson );

            case MoocLesson::ENTITY_TYPE_LESSON:
                return new MoocLesson( $title, $moocContentJson );

            case MoocOverview::ENTITY_TYPE_MOOC:
                return new MoocOverview( $title, $moocContentJson );

            case MoocScript::ENTITY_TYPE_SCRIPT:
                return new MoocScript( $title, $moocContentJson );

            case MoocQuiz::ENTITY_TYPE_QUIZ:
                return new MoocQuiz( $title, $moocContentJson );

            // unknown MOOC entity type
            default:
                return null;
        }
    }
}