<?php

/**
 * MOOC item model
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 */

/**
 * Represents a MOOC item.
 */
class MoocItem {

    protected $title;

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
        $this->title = $title;
        $this->learningGoals = $moocContentJson['learning-goals'];
        $this->video = $moocContentJson['video'];
        $this->scriptTitle = Title::newFromText($title . '/script');
        $this->quizTitle = Title::newFromText($title . '/quiz');
        $this->furtherReading = $moocContentJson['further-reading'];
        $this->children = $moocContentJson['children'];
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
        return $this->title->getSubpageText();
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
        return (count($this->children) > 0);
    }

    /**
     *
     * @return Array(string) names of child items
     */
    public function getChildren() {
        return $this->children;
    }
}
