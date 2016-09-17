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

    protected $script;

    protected $quiz;

    protected $furtherReading;

    public function __construct($title, $moocContentJson) {
        $this->title = $title;
        $this->learningGoals = $moocContentJson['learning-goals'];
        $this->video = $moocContentJson['video'];
        $this->script = $moocContentJson['script'];
        $this->quiz = $moocContentJson['quiz'];
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

    public function getScript() {
        return $this->script;
    }

    public function getQuiz() {
        return $this->quiz;
    }

    public function getFurtherReading() {
        return $this->furtherReading;
    }
}
