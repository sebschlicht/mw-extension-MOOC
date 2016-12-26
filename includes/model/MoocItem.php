<?php

/**
 * Abstract model for all types of MOOC items.
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 */
abstract class MoocItem {

    const JFIELD_TYPE = 'type';

    const JFIELD_LEARNING_GOALS = 'learning-goals';

    const JFIELD_VIDEO = 'video';

    const JFIELD_FURTHER_READING = 'further-reading';

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
     * @var Array(string) learning goals that should be fulfilled at the end of this item
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
     * @var Array(string) resources of further reading
     */
    public $furtherReading;

    /**
     * @var Array(string) names of child items
     */
    public $children;

    /**
     * Creates a new MOOC item from JSON.
     *
     * @param Title $title
     *            page title
     * @param mixed $moocContentJson
     *            decoded JSON string
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
     * @param $title Title title of the MOOC item page
     * @param $moocContentJson JSON JSON content (associative array) representing a MOOC item
     * @return MoocLesson|MoocUnit|null MOOC item instance or null on error
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
