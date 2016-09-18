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

    protected $learningGoals;

    protected $video;

    protected $scriptTitle;

    protected $quizTitle;

    protected $furtherReading;

    public function __construct($title, $moocContentJson) {
        $this->title = $title;
        $this->learningGoals = $moocContentJson['learning-goals'];
        $this->video = $moocContentJson['video'];
        $this->scriptTitle = Title::newFromText($title . '/script');
        $this->quizTitle = Title::newFromText($title . '/quiz');
        $this->furtherReading = $moocContentJson['furtherReading'];
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLearningGoals() {
        return $this->learningGoals;
    }

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
     * @return Array
     */
    public function getFurtherReading() {
        return $this->furtherReading;
    }
}
