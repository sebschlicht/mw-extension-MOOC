<?php

/**
 * MOOC item content model
 *
 * @file
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 */

/**
 * Represents the content of a MOOC item.
 */
class MoocContent extends JsonContent {

    protected $item;

    /**
     * @param string $text
     *            MOOC item JSON
     */
    public function __construct($text, $modelId = self::CONTENT_MODEL_MOOC_UNIT) {
        parent::__construct($text, $modelId);
    }

    /**
     * @return MoocItem MOOC item
     */
    public function getItem() {
        return $this->item;
    }

    /**
     * @param $item MoocItem MOOC item
     */
    public function setItem($item) {
        $this->item = $item;
    }

    /**
     * @return MoocItem MOOC item loaded from the content
     */
    public function loadItem() {
        $json = parent::getJsonData();
        return new MoocUnit(null, $json);
    }

    /**
     * @return bool whether content is valid
     */
    public function isValid() {
        if (parent::isValid()) {
            $item = $this->getItem();

            // load MOOC item if not loaded yet
            if (!isset($item)) {
                $item = $this->loadItem();
                $this->setItem($item);
            }

            // validate MOOC item
            if (! isset($item->getVideo())) {
                return false;
            }
            if (! is_array($item->getLearningGoals())) {
                return false;
            }
            if (! is_array($item->getFurtherReading())) {
                return false;
            }
            // validate MOOC lesson
            if ($item->getType() === MoocLesson::ITEM_TYPE_LESSON) {
                if (! is_array($item->getChildren())) {
                    return false;
                }
            }
            return true;
        }// else: invalid JSON
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
            $item = $this->getItem();
            $item->setTitle($title);
            
            $renderer = new MoocContentRenderer($output, $item);
            $renderer->render($item);
            
            $output->setEnableOOUI(true);
            $output->setTOCEnabled(false);
            $output->setText($renderer->getHTML());
            
            $output->addModuleScripts('ext.mooc');
            $output->addModuleStyles('ext.mooc');
            $output->addCategory('Course');
        } else {
            $output->setText('');
        }
    }
}
