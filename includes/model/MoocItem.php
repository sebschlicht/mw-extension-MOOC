<?php

/**
 * MOOC Item Model
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

    protected $baseTitle;

    protected $learningGoals;

    protected $video;

    protected $scriptTitle;

    protected $quizTitle;

    protected $furtherReading;

    protected $children;

    public function __construct($title, $moocContentJson) {
        $this->title = $title;
        // FIXME determine real base title
        $this->baseTitle = $title;
        $this->learningGoals = $moocContentJson['learning-goals'];
        $this->video = $moocContentJson['video'];
        $this->scriptTitle = Title::newFromText($title . '/script');
        $this->quizTitle = Title::newFromText($title . '/quiz');
        $this->furtherReading = $moocContentJson['further-reading'];
        $this->children = $moocContentJson['children'];
    }

    public function getTitle() {
        return $this->title;
    }

    public function getBaseTitle() {
        return $this->baseTitle;
    }

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
     * @return Array(string) names of child items
     */
    public function getChildren() {
        return $this->children;
    }
}
