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
     * edit action identifier
     */
    const ACTION_EDIT = 'edit';

    /**
     * @var ParserOutput parser output to manipulate the result
     */
    protected $parserOutput;

    /**
     * @var OutputPage output page used for Wikitext processing internally
     */
    protected $out;

    /**
     * @var MoocUnit|MoocLesson|MoocOverview MOOC item being rendered
     */
    protected $item;

    public function __construct() {
        $tmpRequestContext = new RequestContext();
        $this->out = $tmpRequestContext->getOutput();
    }

    /**
     * Retrieves the appropriate renderer for a certain MOOC item type.
     *
     * @param string $type MOOC item type
     * @return MoocLessonRenderer|MoocUnitRenderer|null appropriate renderer for the item type or null
     */
    protected static function getRenderer( $type ) {
        // TODO use some registration process for flexibility
        switch ( $type ) {
            case MoocUnit::ENTITY_TYPE_UNIT:
                return new MoocUnitRenderer();

            case MoocLesson::ENTITY_TYPE_LESSON:
                return new MoocLessonRenderer();

            case MoocOverview::ENTITY_TYPE_MOOC:
                return new MoocOverviewRenderer();

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
     * @return bool whether the item has been successfully rendered
     */
    public static function renderItem( &$parserOutput, $item ) {
        $renderer = self::getRenderer( $item->type );
        if ( $renderer !== null ) {
            $renderer->render( $parserOutput, $item );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Renders a MOOC item into a HTML document.
     *
     * @param ParserOutput $parserOutput parser output
     * @param MoocItem $item MOOC item to render
     */
    public function render( &$parserOutput, $item ) {
        $this->parserOutput = $parserOutput;
        $this->item = $item;
        MoocContentStructureProvider::loadMoocStructure( $this->item );

        $this->out->addHTML( '<div id="mooc" class="container-fluid">' );
        
        // # navigation
        $this->out->addHTML( '<div id="mooc-navigation-bar" class="col-xs-12 col-sm-3">' );
        $this->addNavigation( $this->item->baseItem );
        $this->out->addHTML( '</div>' );
        
        // # content
        $this->out->addHTML( '<div id="mooc-content" class="col-xs-12 col-sm-9">' );
        
        // ## sections
        $this->out->addHTML( '<div id="mooc-sections" class="row">' );
        $this->addSections();
        $this->out->addHTML( '</div>' );
        
        // ## categories
        $rootTitle = $this->item->title->getRootTitle();
        $categoryNS = $rootTitle->getNsText();
        $this->out->addWikiText( "[[Category:$categoryNS]]" );
        $this->parserOutput->addCategory( $categoryNS, 0 );
        $categoryMooc = "$categoryNS:{$rootTitle->getText()}";
        $this->out->addWikiText( "[[Category:$categoryMooc]]" );
        $this->parserOutput->addCategory( $categoryMooc, 1 );
        
        $this->out->addHTML( '</div>' );
        $this->out->addHTML( '</div>' );

        $parserOutput->setText( $this->out->getHTML() );
    }

    // ########################################################################
    // # section content
    // ########################################################################

    /**
     * Adds the sections of the MOOC item to the current output.
     */
    abstract protected function addSections();

    protected function addLearningGoalsSection() {
        $this->beginSection( self::SECTION_KEY_LEARNING_GOALS );

        // add learning goals as unordered list, if any
        $learningGoals = self::generateUnorderedList( $this->item->learningGoals );
        if ( $learningGoals !== null ) {
            $this->out->addWikiText( $learningGoals );
        } else {
            // show info box if no learning goal added yet
            $this->addEmptySectionBox( self::SECTION_KEY_LEARNING_GOALS );
        }
        
        $this->endSection();
    }

    protected function addVideoSection() {
        $this->beginSection( self::SECTION_KEY_VIDEO );
        
        if ( $this->item->hasVideo() ) {
            // show video player if video set
            $this->out->addWikiText( '[[File:' . $this->item->video. '|800px]]' );
        } else {
            // show info box if video not set yet
            $this->addEmptySectionBox( self::SECTION_KEY_VIDEO );
        }
        
        $this->endSection();
    }

    protected function addScriptSection() {
        $this->beginSection( self::SECTION_KEY_SCRIPT );

        // add script content if existing
        if ( $this->item->script !== null ) {
            $this->out->addWikiText( $this->item->script->content );
        } else {
            // show info box when script has not been created yet
            // TODO pass link to edit script resource page
            $this->addEmptySectionBox( self::SECTION_KEY_SCRIPT );
        }
        
        $this->endSection();
    }

    protected function addQuizSection() {
        $this->beginSection( self::SECTION_KEY_QUIZ );

        // add quiz content if existing
        if ( $this->item->quiz !== null ) {
            $this->out->addWikiText( $this->item->quiz->content );
        } else {
            // show info box when quiz has not been created yet
            // TODO pass link to edit quiz resource page
            $this->addEmptySectionBox( self::SECTION_KEY_QUIZ );
        }
        
        $this->endSection();
    }

    protected function addFurtherReadingSection() {
        $this->beginSection( self::SECTION_KEY_FURTHER_READING );

        // add further readings as unordered list, if any
        $furtherReading = self::generateUnorderedList( $this->item->furtherReading );
        if ( $furtherReading !== null ) {
            $this->out->addWikiText( $furtherReading );
        } else {
            // show info box when no further readings have been added yet
            $this->addEmptySectionBox( self::SECTION_KEY_FURTHER_READING );
        }

        $this->endSection();
    }

    // ########################################################################
    // # section helper functions
    // ########################################################################

    /**
     * Generates an unordered list from an array.
     *
     * @param array $array array to generate the list from
     * @return null|string Wikitext for an unordered list containing the items specified
     */
    protected static function generateUnorderedList( $array ) {
        if ( isset( $array ) && !empty( $array ) ) {
            return '#' . implode( "\n#", $array );
        } else {
            // list unset or empty
            return null;
        }
    }

    /**
     * Adds an info box emphasising users to contribute to a currently empty section to the output.
     *
     * @param string $sectionKey key of the empty section
     * @param bool $showActionAddContent whether to show controls to add content
     * @param string $editHref edit link
     */
    protected function addEmptySectionBox( $sectionKey, $showActionAddContent = true, $editHref = null ) {
        $this->out->addHTML( '<div class="section-empty-box">' );

        // inform about missing content
        $this->out->addHTML( "<span class='description'>{$this->loadMessage( "section-$sectionKey-empty-description" )}</span>" );

        // add controls to add content, if desired
        if ( $showActionAddContent ) {
            // build edit link
            $editHrefAttr = ( $editHref === null ) ? '' : " href='$editHref'";

            // TODO do we need an additional text to point at external resources such as /script or general hints?
            $this->out->addHTML( " <a class='edit-link' $editHrefAttr>" );
            $this->out->addHTML( $this->loadMessage( "section-$sectionKey-empty-edit-link" ) );
            $this->out->addHTML( '</a>' );
        }

        $this->out->addHTML( '</div>' );
    }

    /**
     * Starts a section in the output in order to make it ready for the section content to be added.
     *
     * @param string $sectionKey section key
     */
    protected function beginSection( $sectionKey ) {
        global $wgMOOCSectionConfig;
        $sectionConfig = $wgMOOCSectionConfig[$sectionKey];
        
        $classes = 'section';
        // trigger collapsing of selected, large sections
        if ( $sectionConfig['collapsed'] ) {
            $classes .= ' default-collapsed';
        }
        
        $this->out->addHTML( "<div id='$sectionKey' class='$classes col-xs-12'>" );
        $this->addSectionHeader( $sectionKey );
        $this->out->addHTML( '<div class="content">' );
    }

    /**
     * Finishes the current section output.
     */
    protected function endSection() {
        // add section expander
        $this->out->addHTML( "<div class='expander'>{$this->loadMessage( 'button-expand-section' )}</div>" );

        // finish section
        $this->out->addHTML( '</div>' );
        $this->out->addHTML( '</div>' );
    }

    // ########################################################################
    // # section header
    // ########################################################################

    /**
     * Adds the header of a section to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionHeader( $sectionKey ) {
        $sectionTitle = $this->loadMessage( "section-$sectionKey-title" );
        $this->out->addHTML( '<div class="header">' );

        // actions
        $this->out->addHTML( '<div class="actions">' );
        $this->addSectionActions( $sectionKey );
        $this->out->addHTML( '</div>' );

        // icon
        $this->addSectionIcon( $sectionKey );

        // heading
        $this->out->addHTML( "<h2>$sectionTitle</h2>" );

        $this->out->addHTML( '</div>' );
    }

    /**
     * Adds the icon for a section header to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionIcon( $sectionKey ) {
        $this->out->addHTML( '<div class="icon">' );

        global $wgMOOCImagePath;
        $iconPath = $wgMOOCImagePath . $this->getSectionIconFilename( $sectionKey );
        $this->out->addHTML( "<img src='$iconPath' alt='' />" );

        $this->out->addHTML( '</div>' );
    }

    /**
     * Determines the name of the icon file for a certain section.
     *
     * @param string $sectionKey section key
     * @return string name of the section icon file
     */
    protected function getSectionIconFilename( $sectionKey ) {
        return "ic_$sectionKey.svg";
    }

    // ########################################################################
    // # section header.actions
    // ########################################################################

    /**
     * Adds the action buttons for a section header to the output.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActions( $sectionKey ) {
        // edit section content
        $this->addSectionActionEdit( $sectionKey );
    }

    /**
     * Adds the UI elements to the units section header that allow to edit the section content.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionEdit( $sectionKey ) {
        $btnHref = "/SpecialPage:MoocEdit?title={$this->item->title}&section=$sectionKey";
        $btnTitle = $this->loadMessage( "section-$sectionKey-edit-title" );

        $this->addSectionActionButton( self::ACTION_EDIT, $btnTitle, $btnHref );
        $this->addModalBox( $sectionKey, self::ACTION_EDIT );
    }

    /**
     * Adds an action button to the section header in the current output.
     *
     * @param string $action action the button is intended for
     * @param string $btnTitle button title
     * @param string $btnHref button target link
     */
    protected function addSectionActionButton( $action, $btnTitle, $btnHref ) {
        global $wgMOOCImagePath;
        $icAction = $wgMOOCImagePath . $this->getActionIconFilename( $action );

        $this->out->addHTML( "<div class='btn btn-$action'>" );
        $this->out->addHTML( "<a href='$btnHref'' title='$btnTitle'>" );
        $this->out->addHTML( "<img src='$icAction' alt='$btnTitle'/>" );
        $this->out->addHTML( '</a>' );
        $this->out->addHTML( '</div>' );
    }

    /**
     * Determines the name of the icon file for an action.
     *
     * @param string $action action
     * @return string name of the action icon file
     */
    protected function getActionIconFilename( $action ) {
        return "ic_$action.svg";
    }

    // ########################################################################
    // # modal box
    // ########################################################################

    /**
     * Adds the modal box for a certain action.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function addModalBox( $sectionKey, $action ) {
        $this->out->addHTML( '<div class="modal-wrapper">' );
        $this->out->addHTML( '<div class="modal-bg"></div>' );
        $this->out->addHTML( '<div class="modal-box">' );
        $this->addModalBoxContent( $sectionKey, $action );
        $this->out->addHTML( '</div>' );
        $this->out->addHTML( '</div>' );
    }

    /**
     * Adds the content of the modal box for a certain action.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function addModalBoxContent( $sectionKey, $action ) {
        $modalTitle = $this->loadMessage( "section-$sectionKey-$action-title" );
        $this->out->addHTML( "<h3>$modalTitle</h3>" );
        $this->out->addHTML( "<form class='$action container-fluid'>" );

        // form components
        $this->out->addHTML( '<div class="form-group row">' );
        $this->fillModalBoxForm( $sectionKey, $action );
        $this->out->addHTML( '</div>' );

        // form actions
        $this->out->addHTML( '<div class="form-group row">' );
        $this->addModalBoxActions( $sectionKey, $action );
        $this->out->addHTML( '</div>' );

        $this->out->addHTML( '</form>' );
    }

    /**
     * Fills the form fields to a modal box.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function fillModalBoxForm( $sectionKey, $action ) {
        if ( $action != self::ACTION_EDIT ) {
            // default implementation only supports editing
            return;
        }

        switch ( $sectionKey ) {
            // video
            case self::SECTION_KEY_VIDEO:
                // simple text input field
                $this->out->addHTML( '<div class="input-group">' );
                $this->out->addHTML( '<div class="input-group-addon">File:</div>' );
                $placeholderVideoFile = $this->loadMessage( 'unit-edit-video-modal-placeholder-value' );
                $this->out->addHTML( "<input type='text' class='value form-control' value='{$this->item->video}' placeholder='$placeholderVideoFile'/>" );
                $this->out->addHTML( '</div>' );
                break;

            // ordered lists
            case self::SECTION_KEY_LEARNING_GOALS:
            case self::SECTION_KEY_FURTHER_READING:
                $placeholderList = $this->loadMessage( "unit-edit-$sectionKey-modal-placeholder-value" );
                $this->out->addHTML( "<ol class='value' data-placeholder='$placeholderList'></ol>" );
                break;

            // external resources
            case self::SECTION_KEY_SCRIPT:
            case self::SECTION_KEY_QUIZ:
                // auto-growing textarea
                $placeholderResourceContent = $this->loadMessage( "unit-edit-$sectionKey-modal-placeholder-value" );
                $entity = ( $sectionKey === self::SECTION_KEY_SCRIPT ) ? $this->item->script : $this->item->quiz;
                $textareaValue = ( $entity === null ) ? '' : $entity->content;
                $this->out->addHTML( "<textarea class='value auto-grow form-control' rows='1' placeholder='$placeholderResourceContent'>$textareaValue</textarea>" );
                break;

            default:
                // unknown form components, leave empty
                break;
        }
    }

    /**
     * Adds the form actions to a modal box.
     *
     * @param string $sectionKey section key
     * @param string $action action the modal box is intended for
     */
    protected function addModalBoxActions( $sectionKey, $action ) {
        if ( $action == self::ACTION_EDIT && $sectionKey !== null ) {
            // save button
            $titleSave = $this->loadMessage( 'modal-button-title-save' );
            $this->out->addHTML( "<input type='submit' class='btn btn-save btn-submit' value='$titleSave'/>" );
        }
        // cancel button
        $titleCancel = $this->loadMessage( 'modal-button-title-cancel' );
        $this->out->addHTML( "<input type=\"button\" class=\"btn btn-cancel\" value=\"$titleCancel\" />" );
        // reset button
        $titleReset = $this->loadMessage( 'modal-button-title-reset' );
        $this->out->addHTML( "<input type='reset' class='btn btn-reset' value='$titleReset'/>" );
    }

    // ########################################################################
    // # MOOC navigation
    // ########################################################################

    /**
     * Adds the navigation bar for the MOOC to the output.
     *
     * @param MoocItem $baseItem MOOC's base item
     */
    protected function addNavigation( $baseItem ) {
        $this->out->addHTML( '<div id="mooc-navigation">' );
        // header
        $title = $this->loadMessage( 'navigation-title' );
        $this->out->addHTML( '<div class="header">' );
        
        // ## icon
        $this->addSectionIcon( 'navigation' );
        
        // ## heading
        $this->out->addHTML( "<h2>$title</h2>" );
        
        $this->out->addHTML( '</div>' );
        
        // content
        $this->out->addHTML( '<ul class="content">' );
        $this->addNavigationItem( $baseItem );
        $this->out->addHTML( '</ul>' );
        
        $this->out->addHTML( '</div>' );
    }

    /**
     * Adds a navigation item for a MOOC item to the navigation bar output.
     *
     * @param MoocItem $item MOOC item to add
     */
    protected function addNavigationItem( $item ) {
        $this->out->addHTML( '<li>' );
        $this->out->addWikiText( "[[$item->title|{$item->getName()}]]" );

        // register link for interwiki meta data
        $this->parserOutput->addLink( $item->title );

        // TODO do this for next/previous links and displayed children as well
        
        // add menu items for children - if any
        if ( $item->hasChildren() ) {
            // limit navigation to MoocItems
            $children = [];
            foreach ( $item->children as $childItem ) {
                if ( $childItem instanceof MoocItem ) {
                    array_push( $children, $childItem );
                }
            }

            if ( !empty( $children ) ) {
                $this->out->addHTML( '<ul>' );
                foreach ( $children as $childItem ) {
                    $this->addNavigationItem( $childItem );
                }
                $this->out->addHTML( '</ul>' );
            }
        }
        $this->out->addHTML( '</li>' );
    }

    // ########################################################################
    // # Mediawiki-API wrappers
    // ########################################################################

    /**
     * Loads a message in context of the MOOC extension.
     * Additional parameters will be passed to wfMessage in background.
     *
     * @param string $key message key
     * @return string internationalized message built
     */
    protected function loadMessage( $key /*...*/ ) {
        $params = func_get_args();
        array_shift( $params );
        $key = 'mooc-' . $key;
        $wfMessage = wfMessage( $key, $params );
        return $wfMessage->text();
    }
}
