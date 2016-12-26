<?php

/**
 * Represents the content of a MOOC item.
 *
 * @author  Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 * @ingroup Content
 */
class MoocContent extends JsonContent {

    /**
     * identifier of the MOOC item content model
     */
    const CONTENT_MODEL_MOOC_ITEM = 'mooc-item';

    /**
     * @var MoocItem MOOC item being loaded
     */
    public $item;

    /**
     * @param string $text MOOC item JSON
     * @param string $modelId identifier of the page's content model
     */
    public function __construct($text, $modelId = self::CONTENT_MODEL_MOOC_ITEM) {
        parent::__construct($text, $modelId);
    }

    /**
     * Decodes the page content JSON into a MOOC item.
     *
     * @return MoocItem MOOC item loaded from the content
     */
    public function loadItem() {
        return MoocItem::loadItemFromJson(null, parent::getData());
    }

    /**
     * @return bool whether content is valid
     */
    public function isValid() {
        // check for valid JSON
        if (parent::isValid()) {
            // load MOOC item if not loaded yet
            if (!isset($this->item)) {
                $this->item = $this->loadItem();
            }
            $item = $this->item;

            // validate MOOC item
            if (! isset($item->video)) {
                return false;
            }
            if (! is_array($item->learningGoals)) {
                return false;
            }
            if (! is_array($item->furtherReading)) {
                return false;
            }
            // validate MOOC lesson
            if ($item->type === MoocLesson::ITEM_TYPE_LESSON) {
                if (! is_array($item->children)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Sets the HTML and add the appropriate styles.
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
            $this->item->setTitle($title);
            $renderer = new MoocContentRenderer($output, $this->item);
            $output->setEnableOOUI(true);
            $output->setTOCEnabled(false);
            $output->setText($renderer->render());
            
            $output->addModuleScripts('ext.mooc');
            $output->addModuleStyles('ext.mooc');
            // TODO internationalization
            $output->addCategory('Course', 0);
        } else {
            $output->setText('');
        }
    }
}
