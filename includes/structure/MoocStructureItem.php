<?php

/**
 * Structure information container for MOOC items.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocStructureItem {

    /**
     * @var MoocItem MOOC item
     */
    public $item;

    /**
     * @var string[] names of child pages
     */
    public $children;

    /**
     * @param $item MoocItem MOOC item
     * @param $children string[] names of child pages
     */
    public function __construct($item, $children) {
        $this->item = $item;
        $this->children = $children;
    }
}
