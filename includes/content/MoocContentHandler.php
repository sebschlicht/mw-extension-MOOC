<?php

/**
 * Content handler for MOOC items.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 * @ingroup Content
 */
class MoocContentHandler extends CodeContentHandler {

    /**
     * @param string $modelId identifier of the page's content model
     */
    public function __construct( $modelId = MoocContent::CONTENT_MODEL_MOOC_ITEM ) {
        parent::__construct( $modelId, [
            'CONTENT_FORMAT_JSON'
        ] );
    }

    /**
     * @return string name of the content class
     */
    protected function getContentClass() {
        return MoocContent::class;
    }
}
