<?php

/**
 * Renderer for MOOC lessons.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocLessonRenderer extends MoocContentRenderer {

    /**
     * key of the child units section
     */
    const SECTION_KEY_UNITS = 'units';

    /**
     * add action identifier
     */
    const ACTION_ADD = 'add';

    /**
     * Adds the MOOC lesson sections to the output.
     */
    protected function addSections() {
        $this->addLearningGoalsSection();
        $this->addVideoSection();
        $this->addScriptSection();
        $this->addQuizSection();
        $this->addChildrenSection();
        $this->addFurtherReadingSection();
    }

    protected function addChildrenSection() {
        $this->beginSection( self::SECTION_KEY_UNITS );

        $this->addUnitsSectionContent( $this->item );
        // TODO add controls to add units somewhere

        $this->endSection();
    }

    /**
     * Adds the content of the units section.
     *
     * @param MoocLesson $lesson lesson which units to list
     * @param bool $showActionAddUnit whether to show controls to add units if none have been added yet
     */
    protected function addUnitsSectionContent( $lesson, $showActionAddUnit = true ) {
        if ( $lesson->hasChildren() ) {
            // list child units if any
            foreach ( $lesson->children as $unit ) {
                $this->addChildUnit( $unit );
            }
        } else {
            // show info box if no child units added yet
            $this->addEmptySectionBox( self::SECTION_KEY_UNITS, $showActionAddUnit );
        }
    }

    /**
     * Adds an unit to the units section.
     *
     * @param MoocItem $unit unit to add
     */
    protected function addChildUnit( $unit ) {
        $this->out->addHTML( '<div class="child unit col-xs-12">' );

        // left column: clickable video thumbnail
        $this->out->addHTML( '<div class="left col-xs-12 col-sm-5">' );
        $videoThumbClasses = 'video-thumbnail';
        if ( !$unit->hasVideo() ) {
            $videoThumbClasses .= ' no-video';
        }
        $this->out->addHTML( "<a href='{$unit->title->getLinkURL()}' class='$videoThumbClasses'>" );
        if ( $unit->hasVideo() ) {
            // TODO what is the max-width here? fine to use fixed width?
            $this->out->addWikiText( "[[File:$unit->video|frameless|300x170px|link=$unit->title]]" );
        } else {
            $this->out->addHTML( "<span>{$this->loadMessage( 'unit-no-video' )}</span>" );
        }
        $this->out->addHTML( '</a>' );
        $this->out->addHTML( '</div>' );

        // right column: links, clickable title, learning goals
        $this->out->addHTML( '<div class="col-xs-12 col-sm-7">' );

        // links
        $this->addChildLinkBar( $unit );

        // title
        $this->out->addHTML( '<div class="title">' );
        $this->out->addWikiText( "[[$unit->title|{$unit->title->getSubpageText()}]]" );
        $this->out->addHTML( '</div>' );

        // learning goals
        $this->out->addHTML( '<div class="learning-goals">' );
        $learningGoals = $this->generateLearningGoalsWikiText( $unit );
        if ( $learningGoals !== null ) {
            $this->out->addWikiText( $learningGoals );
        } else {
            $this->out->addHTML( $this->loadMessage( 'section-learning-goals-empty-description' ) );
        }
        $this->out->addHTML( '</div>' );

        // meta TODO add discussion meta data overlay?

        $this->out->addHTML( '</div>' );

        $this->out->addHTML( '</div>' );

        // register external link to unit
        $this->parserOutput->addLink( $unit->title );
    }

    /**
     * Adds the link bar to the child unit output.
     *
     * @param MoocUnit $unit child unit the link bar should be added for
     */
    protected function addChildLinkBar( $unit ) {
        $this->out->addHTML( '<div class="links">' );

        // video
        $this->addChildLinkBarSectionLink( $unit, self::SECTION_KEY_VIDEO );
        // download video
        $this->addChildLinkBarDownloadLink( $unit );
        // script
        $this->addChildLinkBarSectionLink( $unit, self::SECTION_KEY_SCRIPT );
        // quiz
        $this->addChildLinkBarSectionLink( $unit, self::SECTION_KEY_QUIZ );

        $this->out->addHTML( '</div>' );
    }

    /**
     * Adds the link to download the unit's video file to the child unit's link bar.
     *
     * @param MoocUnit $unit unit the download link is added for
     */
    protected function addChildLinkBarDownloadLink( $unit ) {
        global $wgMOOCImagePath;
        $icon = $wgMOOCImagePath . 'ic_download.svg';
        $title = $this->loadMessage( 'section-units-unit-link-download-video' );

        // retrieve video file URL
        $href = null;
        if ( $unit->hasVideo() ) {
            $videoTitle = Title::newFromText( "File:$unit->video" );
            $file = wfFindFile( $videoTitle, [ 'time' => false ] );
            if ( $file->exists() ) {
                $href = $file->getUrl();

                // register link to the file
                $this->parserOutput->addLink( $videoTitle );
            }
        }

        $classes = ['download-video'];
        if ( $href === null ) {
            array_push( $classes, 'disabled' );
        }
        $this->addChildLinkBarLink( $icon, $href, $title, $classes );
    }

    /**
     * Resolves the full URL to the original file of a media.
     *
     * @param Title $title page title of the media file
     * @return string full URL to the original media file
     */
    protected function resolveMediaUrl( $title ) {
        // TODO resolve media file title to full URL to original file
        return $title->getLinkURL();
    }

    /**
     * Adds the link to a unit's section to the child unit link bar.
     *
     * @param MoocUnit $unit child unit
     * @param string $sectionKey section key
     */
    protected function addChildLinkBarSectionLink( $unit, $sectionKey ) {
        global $wgMOOCImagePath;
        $icon = $wgMOOCImagePath . $this->getSectionIconFilename( $sectionKey );
        $href = "{$unit->title->getLinkURL()}#$sectionKey";
        $title = $this->loadMessage( "section-units-unit-link-$sectionKey" );
        $this->addChildLinkBarLink( $icon, $href, $title );
    }

    protected function addChildLinkBarLink( $icon, $href, $title, $classes = null ) {
        $attrClass = '';
        if ( !empty( $classes ) ) {
            $attrClass = ' class="' . implode( ' ', $classes ) . '"';
        }
        $this->out->addHTML( "<a href='$href'$attrClass>" );
        $this->out->addHTML( "<img src='$icon' title='$title' alt='$title' />" );
        $this->out->addHTML( '</a>' );
    }

    protected function fillModalBoxForm( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD ) {
            $this->out->addHTML( '<input type="text" class="value form-control" />' );
        } else {
            parent::fillModalBoxForm( $sectionKey, $action );
        }
    }

    protected function addModalBoxActions( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD ) {
            $titleAdd = $this->loadMessage( 'modal-button-title-add' );
            $this->out->addHTML( "<input type='submit' class='btn btn-add btn-submit' value='$titleAdd' />" );
            $titleCancel = $this->loadMessage( 'modal-button-title-cancel' );
            $this->out->addHTML( "<input type='button' class='btn btn-cancel' value='$titleCancel' />" );
        } else {
            parent::addModalBoxActions( $sectionKey, $action );
        }
    }

    /**
     * Adds the UI elements to the units section header that allow to add an unit.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionAdd( $sectionKey ) {
        // TODO link to add unit function instead
        $btnHref = "/SpecialPage:MoocEdit?title={$this->item->title}&section=$sectionKey";
        $btnTitle = $this->loadMessage( "section-$sectionKey-add-title" );

        $this->addSectionActionButton( self::ACTION_ADD, $btnTitle, $btnHref );
        $this->addModalBox( $sectionKey, self::ACTION_ADD );
    }

    protected function addSectionActions( $sectionKey ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS ) {
            // add unit
            $this->addSectionActionAdd( $sectionKey );
        } else {
            // TODO always add edit button?
            parent::addSectionActions( $sectionKey );
        }
    }

    protected function getSectionActionIconFilename( $action ) {
        if ( $action == self::ACTION_ADD ) {
            return "ic_$action.png";
        } else {
            return "ic_$action.svg";
        }
    }

    protected function getSectionIconFilename( $sectionKey ) {
        switch ( $sectionKey ) {
            case self::SECTION_KEY_UNITS:
                return parent::getSectionIconFilename( 'children' );

            default:
                return parent::getSectionIconFilename( $sectionKey );
        }
    }
}
