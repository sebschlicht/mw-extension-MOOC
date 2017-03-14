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

    // ########################################################################
    // # section content
    // ########################################################################

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
     * @param MoocUnit $unit unit to add
     */
    protected function addChildUnit( $unit ) {
        $this->out->addHTML( '<div class="child unit col-xs-12">' );

        // left column: clickable video thumbnail
        $this->out->addHTML( '<div class="left col-xs-12 col-sm-5">' );

        // load video
        // TODO make thumb width configurable? what is the max-width here? possible to keep dynamic?
        $hasVideo = $this->loadVideoData( $unit, 300 );
        $videoThumbClasses = 'video-thumbnail';
        if ( !$hasVideo ) {
            $videoThumbClasses .= ' no-video';
        }

        // add linked thumb or placeholder
        $this->out->addHTML( "<a href='{$unit->title->getLinkURL()}' class='$videoThumbClasses'>" );
        if ( $hasVideo ) {
            $this->out->addHTML( "<img src='{$unit->videoData['thumbUrl']}' />" );
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
        $learningGoals = self::generateUnorderedList( $unit->learningGoals );
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
     * Loads the video data of an item into the item.
     *
     * @param MoocItem $item item
     * @param int $thumbWidth targeted thumbnail width
     * @return bool whether the video data has been successfully loaded or not
     */
    protected function loadVideoData( $item, $thumbWidth ) {
        if ( $item->hasVideo() ) {
            // TODO support more than File:?
            $type = 'file';

            switch ( $type ) {
                case 'file':
                    // load file
                    $title = Title::newFromText( "File:$item->video" );
                    $file = wfFindFile( $title, [ 'time' => false ] );
                    if ( $file->exists() ) {
                        // load thumb
                        $thumbParams = [
                           'width' => min( $thumbWidth, $file->getWidth( true ) )
                        ];
                        $thumb = $file->transform( $thumbParams );

                        // register link to the file
                        $this->parserOutput->addLink( $title );

                        $item->videoData = [
                            'url' => $file->getUrl(),
                            'thumbUrl' => $thumb->getUrl()
                        ];
                        return true;
                    }
                    // file does not exist
                    return false;
                default:
                    // unknown video type
                    return false;
            }
        }
        // no video
        return false;
    }

    // ########################################################################
    // # section content.links
    // ########################################################################

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
        $href = ($unit->hasVideo() && $unit->videoData) ? $unit->videoData['url'] : null;

        $classes = ['download-video'];
        if ( $href === null ) {
            array_push( $classes, 'disabled' );
        }
        $this->addChildLinkBarLink( $icon, $href, $title, $classes );
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

    /**
     * Adds a link to the child unit link bar.
     *
     * @param string $icon icon file path
     * @param string $href link target
     * @param string $title link title
     * @param array $classes CSS classes to apply to the link
     */
    protected function addChildLinkBarLink( $icon, $href, $title, $classes = null ) {
        $attrClass = '';
        if ( !empty( $classes ) ) {
            $attrClass = 'class="' . implode( ' ', $classes ) . '"';
        }
        $this->out->addHTML( "<a href='$href' $attrClass>" );
        $this->out->addHTML( "<img src='$icon' title='$title' alt='$title' />" );
        $this->out->addHTML( '</a>' );
    }

    // ########################################################################
    // # section header
    // ########################################################################

    protected function getSectionIconFilename( $sectionKey ) {
        switch ( $sectionKey ) {
            case self::SECTION_KEY_UNITS:
                return parent::getSectionIconFilename( 'children' );

            default:
                return parent::getSectionIconFilename( $sectionKey );
        }
    }

    // ########################################################################
    // # section header.actions
    // ########################################################################

    protected function addSectionActions( $sectionKey ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS ) {
            // add unit
            $this->addSectionActionAdd( $sectionKey );
            // TODO edit units?
        } else {
            parent::addSectionActions( $sectionKey );
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
        if ( $sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD ) {
            $this->out->addHTML( '<input type="text" class="value form-control" />' );
        } else {
            parent::fillModalBoxForm( $sectionKey, $action );
        }
    }

    protected function addModalBoxActions( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD ) {
            // add button
            $titleAdd = $this->loadMessage( 'modal-button-title-add' );
            $this->out->addHTML( "<input type='submit' class='btn btn-add btn-submit' value='$titleAdd' />" );
            // cancel button
            $titleCancel = $this->loadMessage( 'modal-button-title-cancel' );
            $this->out->addHTML( "<input type='button' class='btn btn-cancel' value='$titleCancel' />" );
        } else {
            parent::addModalBoxActions( $sectionKey, $action );
        }
    }
}
