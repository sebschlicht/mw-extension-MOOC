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
     * Creates an empty MOOC entity.
     *
     * @param string $type entity type identifier
     * @param Title $title page title
     */
    public function __construct( $type, $title = null ) {
        $this->type = $type;
        // TODO completely separate page Title from MOOC entities?
        $this->setTitle( $title );
    }

    /**
     * Sets the page title.
     *
     * @param Title $title page title
     */
    public function setTitle( $title ) {
        $this->title = $title;
    }

    /**
     * @return bool whether the entity has child entities
     */
    public function hasChildren() {
        return false;
    }

    /**
     * Loads the entity fields from a JSON array.
     *
     * @param array $jsonArray associative array (e.g. from json_decode)
     */
    abstract protected function loadJson( $jsonArray );

    /**
     * Converts this MOOC entity into JSON content.
     *
     * @return array JSON (associative array) representing a MOOC entity
     */
    abstract public function toJson();

    /**
     * Loads a MOOC entity from a JSON array.
     *
     * @param array $jsonArray associative array (e.g. from json_decode)
     * @param Title $title page title
     * @return MoocLesson|MoocOverview|MoocQuiz|MoocScript|MoocUnit|null MOOC entity or <i>null</i> if the entity type
     * was not specified or is unknown
     */
    public static function fromJson( $jsonArray, $title = null ) {
        // extract entity type
        if ( !array_key_exists( self::JFIELD_TYPE, $jsonArray ) ) {
            return null;
        }
        $type = $jsonArray[self::JFIELD_TYPE];

        // instantiate entity by type and load values from JSON array
        $entity = self::instantiate( $type, $title );
        if ( $entity !== null ) {
            $entity->loadJson( $jsonArray );
        }
        return $entity;
    }

    /**
     * Instantiates a MOOC entity by its type.
     *
     * @param string $type MOOC entity type identifier
     * @param Title $title page title
     * @return MoocLesson|MoocOverview|MoocQuiz|MoocScript|MoocUnit|null MOOC entity or <i>null</i> if type is unknown
     */
    protected static function instantiate ( $type, $title = null ) {
        switch ( $type ) {
            case MoocUnit::ENTITY_TYPE_UNIT:
                return new MoocUnit( $type, $title );

            case MoocLesson::ENTITY_TYPE_LESSON:
                return new MoocLesson( $type, $title );

            case MoocOverview::ENTITY_TYPE_MOOC:
                return new MoocOverview( $type, $title );

            case MoocScript::ENTITY_TYPE_SCRIPT:
                return new MoocScript( $type, $title );

            case MoocQuiz::ENTITY_TYPE_QUIZ:
                return new MoocQuiz( $type, $title );

            // unknown MOOC entity type
            default:
                return null;
        }
    }
}