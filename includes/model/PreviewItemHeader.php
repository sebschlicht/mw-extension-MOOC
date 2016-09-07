<?php

/**
 * Header of a MOOC item that is part of the current MOOC and can be previewed within the current MOOC item.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sebastian Schlicht, jablab.de
 * @copyright © 2016 Sebastian Schlicht
 * @license GNU General Public Licence 2.0
 */
class PreviewItemHeader extends MoocItemHeader {

    private $thumbnail;

    public function getThumbnail() {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
    }
}
