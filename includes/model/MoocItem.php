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
     * JSON field identifier for the script page title
     */
    const JFIELD_SCRIPT_TITLE = 'scriptTitle';
    /**
     * JSON field identifier for the quiz page title
     */
    const JFIELD_QUIZ_TITLE = 'quizTitle';

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
     * @var Title title of the script associated with this item
     */
    public $scriptTitle;

    /**
     * @var Title title of the quiz associated with this item
     */
    public $quizTitle;

    /**
     * @var string[] resources of further reading
     */
    public $furtherReading;

    /**
     * @var MoocItem[] child items
     */
    public $children;

    public function __construct( $type, $title = null ) {
        parent::__construct( $type, $title );

        // set script and quiz title
        $this->setTitle( $title );
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

    /**
     * Sets the title and updates the titles of potentially associated resources.
     *
     * @param $title Title page title
     */
    public function setTitle( $title ) {
        $this->title = $title;
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
     * @return boolean whether the item has children
     */
    public function hasChildren() {
        return isset( $this->children ) && !empty( $this->children );
    }

    public function toJson() {
        return [
            self::JFIELD_TYPE => $this->type,
            self::JFIELD_LEARNING_GOALS => $this->learningGoals,
            self::JFIELD_VIDEO => $this->video,
            self::JFIELD_FURTHER_READING => $this->furtherReading
        ];
    }
}
