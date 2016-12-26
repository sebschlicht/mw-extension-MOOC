<?php

/**
 * Structure information container for MOOC items
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
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
     * Creates the structure information container for a MOOC item.
     *
     * @param $item MoocItem MOOC item
     * @param $children string[] names of child pages
     */
    public function __construct($item, $children) {
        $this->item = $item;
        $this->children = $children;
    }
}
