<?php

/**
 * Abstract model for all types of MOOC items.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
abstract class MoocItem {

    /**
     * JSON field identifier for the MOOC item type
     */
    const JFIELD_TYPE = 'type';

    /**
     * JSON field identifier for learning goals
     */
    const JFIELD_LEARNING_GOALS = 'learning-goals';

    /**
     * JSON field identifier for the video
     */
    const JFIELD_VIDEO = 'video';

    /**
     * JSON field identifier for further reading material
     */
    const JFIELD_FURTHER_READING = 'further-reading';

    /**
     * JSON field identifier for child items
     */
    const JFIELD_CHILDREN = 'children';

    /**
     * @var Title page title
     */
    public $title;

    /**
     * @var string type of the MOOC item
     */
    public $type;

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
     * @var string[] names of child items
     */
    public $children;

    /**
     * Creates a new MOOC item from JSON.
     *
     * @param Title $title page title
     * @param array $moocContentJson JSON (associative array) representing a MOOC item
     */
    public function __construct($title, $moocContentJson) {
        // TODO completely separate Title from MoocItem?
        $this->setTitle($title);

        // common MOOC item fields
        $this->type = $moocContentJson[self::JFIELD_TYPE];
        $this->video = $moocContentJson[self::JFIELD_VIDEO];
        $this->learningGoals = $moocContentJson[self::JFIELD_LEARNING_GOALS];
        $this->furtherReading = $moocContentJson[self::JFIELD_FURTHER_READING];
    }

    /**
     * @param $title Title page title
     */
    public function setTitle($title) {
        $this->title = $title;
        if ($title != null) {
            $this->scriptTitle = Title::newFromText($title . '/script');
            $this->quizTitle = Title::newFromText($title . '/quiz');
        }
    }

    /**
     * @return string name of the item (extracted from page title)
     */
    public function getName() {
        return ($this->title == null) ? null : $this->title->getSubpageText();
    }

    /**
     * @return boolean whether the item has children
     */
    public function hasChildren() {
        return isset($this->children) && !empty($this->children);
    }

    /**
     * Loads a MOOC item from JSON content.
     *
     * @param Title $title title of the MOOC item page
     * @param array $moocContentJson JSON (associative array) representing a MOOC item
     * @return MoocItem MOOC item instance or null on error
     */
    public static function loadItemFromJson($title, $moocContentJson) {
        if (!array_key_exists(self::JFIELD_TYPE, $moocContentJson)) {
            return null;
        }

        $type = $moocContentJson[self::JFIELD_TYPE];
        switch ($type) {
            case MoocUnit::ITEM_TYPE_UNIT:
                return new MoocUnit($title, $moocContentJson);

            case MoocLesson::ITEM_TYPE_LESSON:
                return new MoocLesson($title, $moocContentJson);

            // unknown MOOC item type
            default:
                return null;
        }
    }
}
