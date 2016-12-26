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

    protected $title;

    protected $type;

    protected $learningGoals;

    protected $video;

    protected $scriptTitle;

    protected $quizTitle;

    protected $furtherReading;

    protected $children;

    /**
     * Creates a new MOOC item from JSON.
     *
     * @param Title $title
     *            page title
     * @param mixed $moocContentJson
     *            decoded JSON string
     */
    public function __construct($title, $moocContentJson) {
        // TODO separate Title from MoocItem?
        $this->title = $title;
        if ($title != null) {
            $this->scriptTitle = Title::newFromText($title . '/script');
            $this->quizTitle = Title::newFromText($title . '/quiz');
        }
        // common MOOC item fields
        $this->type = $moocContentJson[self::JFIELD_TYPE];
        $this->video = $moocContentJson[self::JFIELD_VIDEO];
        $this->learningGoals = $moocContentJson[self::JFIELD_LEARNING_GOALS];
        $this->furtherReading = $moocContentJson[self::JFIELD_FURTHER_READING];
    }

    /**
     *
     * @return Title page title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     *
     * @return string name of the item (extracted from page title)
     */
    public function getName() {
        return ($this->title == null) ? null : $this->title->getSubpageText();
    }

    /**
     * @return string type of the MOOC item
     */
    public function getType() {
        return $this->type;
    }

    /**
     *
     * @return Array(string) learning goals that should be fulfilled at the end of this item
     */
    public function getLearningGoals() {
        return $this->learningGoals;
    }

    /**
     *
     * @return string name of the video file
     */
    public function getVideo() {
        return $this->video;
    }

    /**
     *
     * @return Title title of the script associated with this item
     */
    public function getScriptTitle() {
        return $this->scriptTitle;
    }

    /**
     *
     * @return Title title of the quiz associated with this item
     */
    public function getQuizTitle() {
        return $this->quizTitle;
    }

    /**
     *
     * @return Array(string) resources of further reading
     */
    public function getFurtherReading() {
        return $this->furtherReading;
    }

    /**
     *
     * @return boolean whether the item has children
     */
    public function hasChildren() {
        return isset($this->children) && !empty($this->children);
    }

    /**
     *
     * @return Array(string) names of child items
     */
    public function getChildren() {
        return $this->children;
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
