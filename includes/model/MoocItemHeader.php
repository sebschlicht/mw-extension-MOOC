<?php

/**
 * Header of a MOOC item that is part of the current MOOC.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sebastian Schlicht, jablab.de
 * @copyright © 2016 Sebastian Schlicht
 * @license GNU General Public Licence 2.0
 */
class MoocItemHeader {

    private $title;

    private $name;

    private $children;

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getURL() {
        if ($this->title === null) {
            return null;
        }
        return $this->title->getLinkURL();
    }

    public function getName() {
        if (($this->name === null) && ($this->getTitle() !== null)) {
            // TODO WTF???
            return Title::newFromText('User:' . $this->title)->getSubpageText();
        }
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function hasChildren() {
        return count($this->children);
    }

    public function getChildren() {
        return $this->children;
    }

    public function setChildren($children) {
        $this->children = $children;
    }

    public static function newFromTitle($title) {
        $item = new MoocItemHeader();
        $item->setTitle($title);
        return $item;
    }
}
