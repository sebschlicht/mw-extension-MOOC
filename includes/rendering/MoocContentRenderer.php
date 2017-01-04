<?php

/**
 * Renderer for MOOC items.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
abstract class MoocContentRenderer {

    /**
     * key of the learning goals section
     */
    const SECTION_KEY_LEARNING_GOALS = 'learning-goals';

    /**
     * key of the video section
     */
    const SECTION_KEY_VIDEO = 'video';

    /**
     * key of the script section
     */
    const SECTION_KEY_SCRIPT = 'script';

    /**
     * key of the quiz section
     */
    const SECTION_KEY_QUIZ = 'quiz';

    /**
     * key of the further reading section
     */
    const SECTION_KEY_FURTHER_READING = 'further-reading';

    /**
     * @var ParserOutput parser output to manipulate the result
     */
    protected $parserOutput;

    /**
     * @var OutputPage output page used for Wikitext processing internally
     */
    protected $out;

    /**
     * @var MoocItem MOOC item being rendered
     */
    protected $item;

    public function __construct() {
        $this->out = new OutputPage();
        // TODO necessary?
        $this->out->enableTOC(false);
    }

    /**
     * Retrieves the appropriate renderer for a certain MOOC item type.
     *
     * @param string $type MOOC item type
     * @return MoocLessonRenderer|MoocUnitRenderer|null appropriate renderer for the item type or null
     */
    protected static function getRenderer($type) {
        // TODO use some registration process for flexibility
        switch($type) {
            case MoocUnit::ITEM_TYPE_UNIT:
                return new MoocUnitRenderer();

            case MoocLesson::ITEM_TYPE_LESSON:
                return new MoocLessonRenderer();

            default:
                return null;
        }
    }

    /**
     * Renders a MOOC item.
     * The appropriate renderer is looked up for the MOOC item by its type.
     *
     * @param ParserOutput $parserOutput parser output
     * @param MoocItem $item MOOC item to render
     * @return string|null body HTML
     */
    public static function renderItem(&$parserOutput, $item) {
        $renderer = self::getRenderer($item->type);
        return ($renderer == null) ? null : $renderer->render($parserOutput, $item);
    }

    /**
     * Renders a MOOC item into a HTML document.
     *
     * @param ParserOutput $parserOutput parser output
     * @param MoocItem $item MOOC item to render
     * @return string body HTML
     */
    public function render(&$parserOutput, $item) {
        $this->parserOutput = $parserOutput;
        $this->item = $item;
        MoocContentStructureProvider::loadMoocStructure($this->item);

        $this->out->addHTML('<div id="mooc" class="container-fluid">');
        
        // # navigation
        $this->out->addHTML('<div id="mooc-navigation-bar" class="col-xs-12 col-sm-3">');
        $this->addNavigation($this->item->baseItem);
        $this->out->addHTML('</div>');
        
        // # content
        $this->out->addHTML('<div id="mooc-content" class="col-xs-12 col-sm-9">');
        
        // ## sections
        $this->out->addHTML('<div id="mooc-sections" class="row">');
        $this->addSections();
        $this->out->addHTML('</div>');
        
        // ## categories
        $rootTitle = $this->item->title->getRootTitle();
        $categoryNS = $rootTitle->getNsText();
        $this->out->addWikiText('[[Category:' . $categoryNS . ']]');
        $this->parserOutput->addCategory($categoryNS, 0);
        $categoryMooc = $categoryNS . ':' . $rootTitle->getText();
        $this->out->addWikiText('[[Category:' . $categoryMooc . ']]');
        $this->parserOutput->addCategory($categoryMooc, 1);
        
        $this->out->addHTML('</div>');
        
        $this->out->addHTML('</div>');
        // TODO call parserOutput->setText from here
        return $this->out->getHTML();
    }

    /**
     * Adds the sections of the MOOC item to the current output.
     */
    abstract protected function addSections();

    protected function addLearningGoalsSection() {
        $this->beginSection(self::SECTION_KEY_LEARNING_GOALS);

        $learningGoals = $this->generateLearningGoalsWikiText($this->item);
        if ($learningGoals != null) {
            $this->out->addWikiText($learningGoals);
        } else {
            // show info box if no learning goal added yet
            $this->addEmptySectionBox(self::SECTION_KEY_LEARNING_GOALS);
        }
        
        $this->endSection();
    }

    protected function generateLearningGoalsWikiText($item) {
        if (count($item->learningGoals) == 0) {
            return null;
        }
        $learningGoals = '';
        foreach ($item->learningGoals as $learningGoal) {
            $learningGoals .= "\n" . '# ' . $learningGoal;
        }
        return $learningGoals;
    }

    protected function addVideoSection() {
        $this->beginSection(self::SECTION_KEY_VIDEO);
        
        if ($this->item->video) {
            // show video player if video set
            $this->out->addWikiText('[[File:' . $this->item->video. '|800px]]');
        } else {
            // show info box if video not set yet
            $this->addEmptySectionBox(self::SECTION_KEY_VIDEO);
        }
        
        $this->endSection();
    }

    protected function addScriptSection() {
        $this->beginSection(self::SECTION_KEY_SCRIPT);
        
        if ($this->item->scriptTitle->exists()) {
            // transclude script if existing
            $this->out->addWikiText('{{:' . $this->item->scriptTitle . '}}');
        } else {
            // show info box if script not created yet
            $this->addEmptySectionBox(self::SECTION_KEY_SCRIPT, $this->item->scriptTitle);
        }
        
        $this->endSection();
    }

    protected function addQuizSection() {
        $this->beginSection(self::SECTION_KEY_QUIZ);
        
        if ($this->item->quizTitle->exists()) {
            // transclude quiz if existing
            $this->out->addWikiText('{{:' . $this->item->quizTitle . '}}');
        } else {
            // show info box if quiz not created yet
            $this->addEmptySectionBox(self::SECTION_KEY_QUIZ, $this->item->quizTitle);
        }
        
        $this->endSection();
    }

    protected function addFurtherReadingSection() {
        $this->beginSection(self::SECTION_KEY_FURTHER_READING);
        
        if (count($this->item->furtherReading) > 0) {
            // show further reading as ordered list if any
            $furtherReading = '';
            foreach ($this->item->furtherReading as $furtherReadingEntry) {
                $furtherReading .= "\n" . '# ' . $furtherReadingEntry;
            }
            $this->out->addWikiText($furtherReading);
        } else {
            // show info box if no further reading added yet
            $this->addEmptySectionBox(self::SECTION_KEY_FURTHER_READING);
        }
        
        $this->endSection();
    }

    /**
     * Adds an info box emphasising users to contribute to a currently empty section to the output.
     *
     * @param string $sectionKey key of the empty section
     * @param array ...$params additional parameters passed to the message loading of the info box description
     */
    protected function addEmptySectionBox($sectionKey, ...$params) {
        // TODO can we automatically prefix classes/ids? at least in LESS?
        $this->out->addHTML('<div class="section-empty-box">');
        
        $this->out->addHTML('<span class="description">');
        $this->out->addHTML($this->loadMessage('section-' . $sectionKey . '-empty-description', $params));
        $this->out->addHTML('</span> ');
        $this->out->addHTML('<a class="edit-link">');
        $this->out->addHTML($this->loadMessage('section-' . $sectionKey . '-empty-edit-link'));
        $this->out->addHTML('</a>');
        // TODO do we need an additional text to point at external resources such as /script or general hints?
        
        $this->out->addHTML('</div>');
    }

    /**
     * Starts a section in the output in order to make it ready for the section content to be added.
     *
     * @param string $sectionKey section key
     */
    protected function beginSection($sectionKey) {
        global $wgMOOCSectionConfig;
        $sectionConfig = $wgMOOCSectionConfig[$sectionKey];
        
        $classes = 'section';
        // trigger collapsing of selected, large sections
        if ($sectionConfig['collapsed']) {
            $classes .= ' default-collapsed';
        }
        
        $this->out->addHTML('<div id="' . $sectionKey . '" class="' . $classes . ' col-xs-12">');
        $this->addSectionHeader($sectionKey);
        $this->out->addHTML('<div class="content">');
    }

    /**
     * Adds the header of a section to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionHeader($sectionKey) {
        $sectionTitle = $this->loadMessage("section-$sectionKey-title");
        $this->out->addHTML('<div class="header">');
        
        // actions
        $this->out->addHTML('<div class="actions">');
        $this->addSectionActions($sectionKey);
        $this->out->addHTML('</div>');
        
        // icon
        $this->addSectionIcon($sectionKey);
        
        // heading
        $this->out->addHTML("<h2>$sectionTitle</h2>");
        
        $this->out->addHTML('</div>');
    }

    /**
     * Adds the action buttons for a section header to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActions($sectionKey) {
        // edit section content
        $this->addSectionActionEdit($sectionKey);
    }

    /**
     * Adds the UI elements to the units section header that allow to edit the section content.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionEdit($sectionKey) {
        $btnHref = '/SpecialPage:MoocEdit?title=' . $this->item->title . '&section=' . $sectionKey;
        $btnTitle = $this->loadMessage("section-$sectionKey-button-title-edit");

        $this->addSectionActionButton('edit', $btnTitle, $btnHref);
        $this->addModalBox($sectionKey, 'edit');
    }

    /**
     * Adds the modal box for a certain action.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function addModalBox($sectionKey, $action) {
        $this->out->addHTML("<div class=\"modal-wrapper\">");
        $this->out->addHTML("<div class=\"modal-bg\"></div>");
        $this->out->addHTML("<div class=\"modal-box\">");
        $this->addModalBoxContent($sectionKey, $action);
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    /**
     * Adds the content of the modal box for a certain action.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function addModalBoxContent($sectionKey, $action) {
        $modalTitle = $this->loadMessage("modal-box-title-$sectionKey");
        $this->out->addHTML("<h3>$modalTitle</h3>");
        $this->out->addHTML("<form class=\"$action\">");
        $this->fillModalBoxForm($sectionKey, $action);
        $this->out->addHTML('</form>');
    }

    /**
     * Fills the form of a modal box.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function fillModalBoxForm($sectionKey, $action) {
        $this->out->addHTML('<textarea class="value" rows="1"></textarea>');
    }

    /**
     * Adds an action button to the section header in the current output.
     *
     * @param string $action action the button is intended for
     * @param string $btnTitle button title
     * @param string $btnHref button target link
     */
    protected function addSectionActionButton($action, $btnTitle, $btnHref) {
        global $wgMOOCImagePath;
        // TODO do this in CSS
        $icSize = '32px';
        $icAction = $wgMOOCImagePath . $this->getSectionActionIconFilename($action);

        $this->out->addHTML("<div class=\"btn btn-$action\">");
        // TODO ensure to link to the special page allowing to perform this action
        // TODO replace href with link that allows tab-browsing with modal boxes
        $this->out->addHTML("<a href=\"$btnHref\" title=\"$btnTitle\">");
        $this->out->addHTML("<img src=\"$icAction\" width=\"$icSize\" height=\"$icSize\" alt=\"$btnTitle\" />");
        $this->out->addHTML('</a>');
        $this->out->addHTML('</div>');
    }

    protected function getSectionActionIconFilename($action) {
        return 'ic_' . $action . '.svg';
    }

    /**
     * Adds the icon for a section header to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionIcon($sectionKey) {
        $this->out->addHTML('<div class="icon">');
        
        global $wgMOOCImagePath;
        $this->out->addHTML(
            '<img src="' . $wgMOOCImagePath . $this->getSectionIconFilename($sectionKey) . '" width="32px" height="32px" alt="" />');
        
        $this->out->addHTML('</div>');
    }

    /**
     * Determines the name of the icon file for a certain section.
     *
     * @param string $sectionKey section key
     * @return string name of the section icon file
     */
    protected function getSectionIconFilename($sectionKey) {
        return 'ic_' . $sectionKey . '.svg';
    }

    /**
     * Finishes the current section output.
     */
    protected function endSection() {
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
    }

    /**
     * Adds the navigation bar for the MOOC to the output.
     *
     * @param MoocItem $baseItem MOOC's base item
     */
    protected function addNavigation($baseItem) {
        $this->out->addHTML('<div id="mooc-navigation">');
        // header
        $title = $this->loadMessage('navigation-title');
        $this->out->addHTML('<div class="header">');
        
        // ## icon
        $this->addSectionIcon('navigation');
        
        // ## heading
        $this->out->addHTML('<h2>' . $title . '</h2>');
        
        $this->out->addHTML('</div>');
        
        // content
        $this->out->addHTML('<ul class="content">');
        $this->addNavigationItem($baseItem);
        $this->out->addHTML('</ul>');
        
        $this->out->addHTML('</div>');
    }

    /**
     * Adds a navigation item for a MOOC item to the navigation bar output.
     *
     * @param MoocItem $item MOOC item to add
     */
    protected function addNavigationItem($item) {
        $this->out->addHTML('<li>');
        $this->out->addWikiText('[[' . $item->title . '|' . $item->getName() . ']]');
        // register link for interwiki meta data
        $this->parserOutput->addLink($item->title);
        // TODO do this for next/previous links and displayed children as well
        
        // add menu items for children - if any
        if ($item->hasChildren()) {
            $this->out->addHTML('<ul>');
            foreach ($item->children as $childItem) {
                $this->addNavigationItem($childItem);
            }
            $this->out->addHTML('</ul>');
        }
        $this->out->addHTML('</li>');
    }

    /**
     * Loads a message in context of the MOOC extension.
     *
     * @param string $key message key
     * @param array ...$params message parameters
     * @return string internationalized message built
     */
    protected function loadMessage($key, ...$params) {
        $key = 'mooc-' . $key;
        $wfMessage = wfMessage($key, $params);
        return $wfMessage->text();
    }
}
