<?php

/**
 * Renderer for MOOC overview pages.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocOverviewRenderer extends MoocLessonRenderer {

    /**
     * key of the child lessons section
     */
    const SECTION_KEY_LESSONS = 'lessons';

    /**
     * add action identifier
     */
    const ACTION_ADD = 'add';

    // ########################################################################
    // # section content
    // ########################################################################

    protected function getSections() {
        return [
            self::SECTION_KEY_LESSONS
        ];
    }

    protected function addSection( $sectionId ) {
        switch ( $sectionId ) {
            case self::SECTION_KEY_LESSONS:
                $this->addLessonsSection();
                break;

            default:
                parent::addSection( $sectionId );
                break;
        }
    }

    protected function addLessonsSection() {
        if ( $this->item->hasChildren() ) {
            // list child lessons if any
            $i = 1;
            foreach ( $this->item->children as $lesson ) {
                $this->addChildLesson( $lesson, $i );
                $i += 1;
            }
        } else {
            // show info box if no child lessons added yet
            $this->addEmptySectionBox( self::SECTION_KEY_LESSONS );
        }
    }

    /**
     * Adds a lesson to the lessons section.
     *
     * @param MoocLesson $lesson lesson to add
     * @param int $iLesson (1-based) lesson position
     */
    protected function addChildLesson( $lesson, $iLesson ) {
        $this->out->addHTML( '<div class="lesson">' );

        // header
        $this->out->addHTML( '<div class="title">' );
        $this->out->addWikiText( "[[$lesson->title|$iLesson. {$lesson->getName()}]]" );
        $this->out->addHTML( '</div>' );

        // units
        $this->addUnitsSectionContent( $lesson, false );

        $this->out->addHTML( '</div>' );
    }

    // ########################################################################
    // # section header
    // ########################################################################

    protected function getSectionIconFilename( $sectionKey ) {
        switch ( $sectionKey ) {
            case self::SECTION_KEY_LESSONS:
                return parent::getSectionIconFilename( 'children' );

            default:
                return parent::getSectionIconFilename( $sectionKey );
        }
    }

    // ########################################################################
    // # section header.actions
    // ########################################################################

    protected function addSectionActions( $sectionKey ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS ) {
            // add lesson
            $this->addSectionActionAddLesson( $sectionKey );
            // TODO edit lessons?
        } else {
            parent::addSectionActions( $sectionKey );
        }
    }

    /**
     * Adds the UI elements to the lessons section header that allow to add a lesson.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionAddLesson( $sectionKey ) {
        // TODO link to add lesson function instead
        $btnHref = "/SpecialPage:MoocEdit?title={$this->item->title}&section=$sectionKey";
        $btnTitle = $this->loadMessage( "section-$sectionKey-add-title" );

        $this->addSectionActionButton( self::ACTION_ADD, $btnTitle, $btnHref );
        $this->addModalBox( $sectionKey, self::ACTION_ADD );
    }

    protected function getActionIconFilename( $action ) {
        if ( $action == self::ACTION_ADD ) {
            return "ic_$action.png";
        } else {
            return parent::getActionIconFilename( $action );
        }
    }

    // ########################################################################
    // # modal box
    // ########################################################################

    protected function fillModalBoxForm( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS && $action == self::ACTION_ADD ) {
            $placeholderLessonName = $this->loadMessage( 'overview-add-lesson-modal-placeholder-name' );
            $this->out->addHTML( "<input type='text' class='value form-control' placeholder='$placeholderLessonName'/>" );
        } else {
            parent::fillModalBoxForm( $sectionKey, $action );
        }
    }

    protected function addModalBoxActions( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS && $action == self::ACTION_ADD ) {
            // add button
            $titleAdd = $this->loadMessage( 'modal-button-title-add' );
            $this->out->addHTML( "<input type='submit' class='btn btn-add btn-submit' value='$titleAdd'/>" );
            // cancel button
            $titleCancel = $this->loadMessage( 'modal-button-title-cancel' );
            $this->out->addHTML( "<input type='button' class='btn btn-cancel' value='$titleCancel'/>" );
        } else {
            parent::addModalBoxActions( $sectionKey, $action );
        }
    }
}