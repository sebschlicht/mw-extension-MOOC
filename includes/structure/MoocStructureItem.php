<?php

class MoocStructureItem {

    public $item;

    public $children;

    public function __construct($item, $children) {
        $this->item = $item;
        $this->children = $children;
    }
}
