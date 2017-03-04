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

    protected function addUnitsSectionContent( $lesson ) {
        if ( $lesson->hasChildren() ) {
            // list child units if any
            foreach ( $lesson->children as $unit ) {
                $this->addChildUnit( $unit );
            }
        } else {
            // show info box if no child units added yet
            $this->addEmptySectionBox( self::SECTION_KEY_UNITS );
        }
    }

    /**
     * Adds an unit to the units section.
     *
     * @param MoocItem $unit unit to add
     */
    protected function addChildUnit($unit) {
        $this->out->addHTML('<div class="child unit col-xs-12">');

        $this->out->addHTML('<div class="left col-xs-12 col-sm-5">');
        // video thumbnail
        $this->out->addHTML('<div class="video-thumbnail">');
        if (isset($unit->video)) {
            // TODO re-calc max width
            $this->out->addWikiText('[[File:' . $unit->video . '|frameless|300x170px|link=' . $unit->title . ']]');
        } else {
            // TODO make clickable without JS
            $this->out->addHTML('<span>' . $this->loadMessage('units-no-video') . '</span>');
        }
        $this->out->addHTML('</div>');
        $this->out->addHTML('</div>');
        $this->parserOutput->addLink($unit->title);

        $this->out->addHTML('<div class="col-xs-12 col-sm-7">');

        // links
        $this->addChildLinkBar($unit);

        // title
        $this->out->addHTML('<div class="title">');
        $this->out->addWikiText('[[' . $unit->title . '|'. $unit->title->getSubpageText() . ']]');
        $this->out->addHTML('</div>');

        // learning goals
        $this->out->addHTML('<div class="learning-goals">');
        $learningGoals = $this->generateLearningGoalsWikiText($unit);
        if ($learningGoals != null) {
            $this->out->addWikiText($learningGoals);
        } else {
            $this->out->addHTML($this->loadMessage('section-' . 'learning-goals' . '-empty-description'));
        }
        $this->out->addHTML('</div>');

        // meta TODO add discussion meta data overlay

        $this->out->addHTML('</div>');

        $this->out->addHTML('</div>');
    }

    /**
     * Adds the link bar to the child unit output.
     *
     * @param MoocUnit $unit child unit the link bar should be added for
     */
    protected function addChildLinkBar($unit) {
        $this->out->addHTML('<div class="links">');

        // video
        $this->addChildLinkBarSectionLink($unit, self::SECTION_KEY_VIDEO);
        // download video
        $this->addChildLinkBarDownloadLink($unit);
        // script
        $this->addChildLinkBarSectionLink($unit, self::SECTION_KEY_SCRIPT);
        // quiz
        $this->addChildLinkBarSectionLink($unit, self::SECTION_KEY_QUIZ);

        $this->out->addHTML('</div>');
    }

    /**
     * Adds the link to download the unit's video file to the child unit's link bar.
     *
     * @param MoocUnit $unit unit the download link is added for
     */
    protected function addChildLinkBarDownloadLink($unit) {
        global $wgMOOCImagePath;
        $icon = $wgMOOCImagePath . 'ic_download.svg';
        $title = $this->loadMessage("section-units-unit-link-download-video");
        $href = isset($unit->video) ? $this->resolveMediaUrl(Title::newFromText("Media:{$unit->video}")) : null;
        $classes = ($href == null) ? ['disabled'] : null;
        $this->addChildLinkBarLink($icon, $href, $title, $classes);
    }

    /**
     * Resolves the full URL to the original file of a media.
     *
     * @param Title $title page title of the media file
     * @return string full URL to the original media file
     */
    protected function resolveMediaUrl($title) {
        // TODO resolve media file title to full URL to original file
        return $title->getLinkURL();
    }

    /**
     * Adds the link to a unit's section to the child unit link bar.
     *
     * @param MoocUnit $unit child unit
     * @param string $sectionKey section key
     */
    protected function addChildLinkBarSectionLink($unit, $sectionKey) {
        global $wgMOOCImagePath;
        $icon = $wgMOOCImagePath . $this->getSectionIconFilename($sectionKey);
        $href = "{$unit->title->getLinkURL()}#$sectionKey";
        $title = $this->loadMessage("section-units-unit-link-$sectionKey");
        $this->addChildLinkBarLink($icon, $href, $title);
    }

    protected function addChildLinkBarLink($icon, $href, $title, $classes=null) {
        $attrClass = '';
        if (!empty($classes)) {
            $attrClass = ' class="' . implode(' ', $classes) . '"';
        }
        $this->out->addHTML("<a href=\"$href\"$attrClass>");
        $this->out->addHTML("<img src=\"$icon\" title=\"$title\" alt=\"$title\" />");
        $this->out->addHTML("</a>");
    }

    protected function fillModalBoxForm( $sectionKey, $action ) {
        if ( $sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD ) {
            $this->out->addHTML( '<input type="text" class="value form-control" />' );
        } else {
            parent::fillModalBoxForm( $sectionKey, $action );
        }
    }

    protected function addModalBoxActions($sectionKey, $action) {
        if ($sectionKey == self::SECTION_KEY_UNITS && $action == self::ACTION_ADD) {
            $titleAdd = $this->loadMessage('modal-button-title-add');
            $this->out->addHTML("<input type=\"submit\" class=\"btn btn-add btn-submit\" value=\"$titleAdd\" />");
            $titleCancel = $this->loadMessage('modal-button-title-cancel');
            $this->out->addHTML("<input type=\"button\" class=\"btn btn-cancel\" value=\"$titleCancel\" />");
        } else {
            parent::addModalBoxActions($sectionKey, $action);
        }
    }

    /**
     * Adds the UI elements to the units section header that allow to add an unit.
     *
     * @param string $sectionKey section key
     */
    protected function addSectionActionAdd($sectionKey) {
        // TODO link to add unit function instead
        $btnHref = '/SpecialPage:MoocEdit?title=' . $this->item->title . '&section=' . $sectionKey;
        $btnTitle = $this->loadMessage("section-$sectionKey-add-title");

        $this->addSectionActionButton('add', $btnTitle, $btnHref);
        $this->addModalBox($sectionKey, 'add');
    }

    protected function addSectionActions($sectionKey) {
        if ($sectionKey == self::SECTION_KEY_UNITS) {
            // add unit
            $this->addSectionActionAdd($sectionKey);
        } else {
            // TODO always add edit button
            parent::addSectionActions($sectionKey);
        }
    }

    protected function getSectionActionIconFilename($action) {
        if ($action == 'add') {
            return "ic_$action.png";
        } else {
            return "ic_$action.svg";
        }
    }

    protected function getSectionIconFilename($sectionKey) {
        switch ($sectionKey) {
            case self::SECTION_KEY_UNITS:
                return parent::getSectionIconFilename('children');

            default:
                return parent::getSectionIconFilename($sectionKey);
        }
    }
}
