<?php

class MoocStructureItem {

    protected $item;

    protected $children;

    public function __construct($item, $children) {
        $this->item = $item;
        $this->children = $children;
    }

    public function getItem() {
        return $this->item;
    }

    public function getChildren() {
        return $this->children;
    }
}
