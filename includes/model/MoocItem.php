<?php

/**
 * Abstract model for all types of MOOC items.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
abstract class MoocItem extends MoocEntity {

    /**
     * JSON field identifier for learning goals
     */
    const JFIELD_LEARNING_GOALS = 'learning-goals';

    /**
     * JSON field identifier for the video
     */
    const JFIELD_VIDEO = 'video';

    /**
     * JSON field identifier for the script
     */
    const JFIELD_SCRIPT = 'script';
    /**
     * JSON field identifier for the quiz
     */
    const JFIELD_QUIZ = 'quiz';

    /**
     * JSON field identifier for further reading material
     */
    const JFIELD_FURTHER_READING = 'further-reading';

    /**
     * JSON field identifier for child items
     */
    const JFIELD_CHILDREN = 'children';

    /**
     * @var MoocItem base item of the MOOC this item belongs to
     */
    public $baseItem;

    /**
     * @var string[] learning goals that should be fulfilled at the end of this item
     */
    public $learningGoals;

    /**
     * @var string name of the video file
     */
    public $video;

    /**
     * @var array video data array containing
     * <ul>
     * <li>url: URL of the video</li>
     * <li>thumbUrl: URL to the video thumbnail</li>
     * </ul>
     */
    public $videoData;

    /**
     * @var MoocResource|null script associated with this item
     */
    public $script;

    /**
     * @var MoocResource|null quiz associated with this item
     */
    public $quiz;

    /**
     * @var string[] resources of further reading
     */
    public $furtherReading;

    /**
     * @var MoocUnit[]|MoocLesson[] child items
     */
    public $children;

    public function __construct( $type, $title = null ) {
        parent::__construct( $type, $title );
    }

    protected function loadJson( $jsonArray ) {
        if ( array_key_exists( self::JFIELD_VIDEO, $jsonArray ) ) {
            $this->video = $jsonArray[self::JFIELD_VIDEO];
        }
        if ( array_key_exists( self::JFIELD_LEARNING_GOALS, $jsonArray ) ) {
            $this->learningGoals = $jsonArray[self::JFIELD_LEARNING_GOALS];
        }
        if ( array_key_exists( self::JFIELD_FURTHER_READING, $jsonArray ) ) {
            $this->furtherReading = $jsonArray[self::JFIELD_FURTHER_READING];
        }
    }

    public function setTitle( $title ) {
        parent::setTitle( $title );
        $this->scriptTitle = ( $title === null ) ? null : Title::newFromText( $title . '/script' );
        $this->quizTitle = ( $title === null ) ? null : Title::newFromText( $title . '/quiz' );
    }

    /**
     * @return string name of the item (extracted from page title)
     */
    public function getName() {
        return ( $this->title == null ) ? null : $this->title->getSubpageText();
    }

    /**
     * @return bool whether the item has a video
     */
    public function hasVideo() {
        return isset( $this->video );
    }

    /**
     * @return bool whether the item has children
     */
    public function hasChildren() {
        return !empty( $this->children );
    }

    public function toJson() {
        return [
            self::JFIELD_TYPE => $this->type,
            self::JFIELD_LEARNING_GOALS => $this->learningGoals,
            self::JFIELD_VIDEO => $this->video,
            self::JFIELD_SCRIPT => ( $this->script !== null ) ? $this->script->toJson() : null,
            self::JFIELD_QUIZ => ( $this->quiz !== null ) ? $this->quiz->toJson() : null,
            self::JFIELD_FURTHER_READING => $this->furtherReading
        ];
    }
}
