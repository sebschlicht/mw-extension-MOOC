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

    /**
     * Adds the MOOC overview sections to the output.
     */
    protected function addSections() {
        $this->addLessonsSection();
    }

    protected function addLessonsSection() {
        $this->beginSection( self::SECTION_KEY_LESSONS );

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

        $this->endSection();
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
        $this->out->addWikiText( sprintf( '[[%s|%d. %s]]', $lesson->title, $iLesson, $lesson->getName() ) );
        $this->out->addHTML( '</div>' );

        // units
        $this->addUnitsSectionContent( $lesson, false );

        $this->out->addHTML( '</div>' );
    }

    protected function fillModalBoxForm( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS && $action == self::ACTION_ADD ) {
            $this->out->addHTML( '<input type="text" class="value form-control" />' );
        } else {
            parent::fillModalBoxForm( $sectionKey, $action );
        }
    }

    protected function addModalBoxActions( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS && $action == self::ACTION_ADD ) {
            $titleAdd = $this->loadMessage( 'modal-button-title-add' );
            $this->out->addHTML( "<input type=\"submit\" class=\"btn btn-add btn-submit\" value=\"$titleAdd\" />" );
            $titleCancel = $this->loadMessage( 'modal-button-title-cancel' );
            $this->out->addHTML( "<input type=\"button\" class=\"btn btn-cancel\" value=\"$titleCancel\" />" );
        } else {
            parent::addModalBoxActions( $sectionKey, $action );
        }
    }

    /**
     * Adds the UI elements to the lessons section header that allow to add a lesson.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionAddLesson( $sectionKey ) {
        // TODO link to add lesson function instead
        $btnHref = '/SpecialPage:MoocEdit?title=' . $this->item->title . '&section=' . $sectionKey;
        $btnTitle = $this->loadMessage( "section-$sectionKey-add-title" );

        $this->addSectionActionButton( 'add', $btnTitle, $btnHref );
        $this->addModalBox( $sectionKey, 'add' );
    }

    protected function addSectionActions( $sectionKey ) {
        if ( $sectionKey == self::SECTION_KEY_LESSONS ) {
            // add lesson
            $this->addSectionActionAddLesson( $sectionKey );
        } else {
            // TODO always add edit button
            parent::addSectionActions( $sectionKey );
        }
    }

    protected function getSectionActionIconFilename( $action ) {
        if ( $action == 'add' ) {
            return "ic_$action.png";
        } else {
            return "ic_$action.svg";
        }
    }

    protected function getSectionIconFilename( $sectionKey ) {
        switch ( $sectionKey ) {
            case self::SECTION_KEY_LESSONS:
                return parent::getSectionIconFilename( 'children' );

            default:
                return parent::getSectionIconFilename( $sectionKey );
        }
    }
}