<?php

/**
 * MOOC Item Content Model
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 */

/**
 * Represents the content of a MOOC item.
 */
class MoocContent extends JsonContent {

    const CONTENT_MODEL_MOOC_ITEM = 'mooc-item';

    /**
     *
     * @param string $text
     *            MOOC Item JSON
     */
    public function __construct($text, $modelId = MoocContent::CONTENT_MODEL_MOOC_ITEM) {
        parent::__construct($text, $modelId);
    }

    /**
     *
     * @return bool Whether content is valid.
     */
    public function isValid() {
        if (parent::isValid()) {
            $json = parent::getJsonData();
            $item = new MoocItem('', $json);
            
            if (! isset($item->getVideo())) {
                return false;
            }
            if (! is_array($item->getLearningGoals())) {
                return false;
            }
            if (! is_array($item->getFurtherReading())) {
                return false;
            }
            // TODO can each item have children?
            // TODO different content types for mooc, lesson and unit?
            return true;
        }
        return false;
    }

    /**
     * Set the HTML and add the appropriate styles.
     *
     * @param Title $title            
     * @param int $revId            
     * @param ParserOptions $options            
     * @param bool $generateHtml            
     * @param ParserOutput $output            
     */
    protected function fillParserOutput(Title $title, $revId, ParserOptions $options, $generateHtml, 
        ParserOutput &$output) {
        // FIXME: WikiPage::doEditContent generates parser output before validation.
        // As such, native data may be invalid (though output is discarded later in that case).
        if ($generateHtml && $this->isValid()) {
            $json = parent::getJsonData();
            $item = new MoocItem($title, $json);
            
            $renderer = new MoocContentRenderer($item);
            $renderer->render($item);
            
            $output->setText($renderer->getHTML());
            // $output->addModuleScripts('ext.mooc');
            $output->addModuleStyles('ext.mooc');
        } else {
            $output->setText('');
        }
    }
}
