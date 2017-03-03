<?php

/**
 * Hooks for MOOC extension.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sebastian Schlicht (sebastian@jablab.de [User:sebschlicht]), Rene Pickhardt ([User:renepick])
 * @license GPLv2
 */
class MOOCHooks {

    /**
     * Fills the edit form of a MOOC page with the MOOC overview JSON to allow fluent creates of MOOCs.
     *
     * @param string $text text to pre-fill the edit form with
     * @param Title $title title of the new page
     */
    public static function onEditFormPreloadText( &$text, &$title ) {
        if ( $title->getContentModel() === MoocContent::CONTENT_MODEL_MOOC_ITEM ) {
            if ( $title->equals( $title->getRootTitle() ) ) {
                $text = json_encode( ( new MoocOverview() )->toJson() );
            }
        }
    }

    /**
     * Transforms a MOOC resource into Wikitext to allow users to edit its content.
     *
     * @param EditPage $editPage edit page
     */
    public static function onEditFormInitialText( $editPage ) {
        if ( $editPage->contentModel === MoocContent::CONTENT_MODEL_MOOC_ITEM ) {
            $pageContent = $editPage->getArticle()->getPage()->getContent()->serialize();
            $moocContent = new MoocContent( $pageContent );
            if ( $moocContent->isValid() && ( $moocContent->entity instanceof MoocResource ) ) {
                $entity = $moocContent->entity;
                if ( $entity !== null ) {
                    $editPage->textbox1 = $entity->content;
                }
            }
        }
    }

    /**
     * Transforms the resource file content into a MOOC resource when previewing an edit.
     * Behind the scenes, the original MOOC resource is loaded and its content field is overwritten.
     *
     * @param EditPage $editPage edit page
     * @param Content $content previewed content
     */
    public static function onEditPageGetPreviewContent( $editPage, &$content ) {
        if ( $editPage->contentModel === MoocContent::CONTENT_MODEL_MOOC_ITEM ) {
            if ( $editPage->getArticle()->getPage()->exists() ) {
                // existing page: inject edit-content into MOOC resource
                $pageContent = $editPage->getArticle()->getPage()->getContent()->serialize();
                $moocContent = new MoocContent( $pageContent );
                $newMoocContent = self::mergeResourceFileIntoMoocContent( $moocContent, $content );
                if ( $newMoocContent === null ) {
                    // invalid MOOC entity or not a MoocResource
                } else {
                    $content = $newMoocContent;
                }
            } else {
                // new page: TODO unclear whether MoocItem or MoocResource
                // maybe decide depending on JSON validity but invalid MoocItem == MoocResource
                // so this is a modelling choice
            }
        }
    }

    /**
     * Transforms the resource file content into a MOOC resource when the user finishes editing.
     * Behind the scenes, the original MOOC resource is loaded and its content field is overwritten.
     *
     * @param WikiPage $wikiPage wiki page being saved
     * @param User $user user saving the article
     * @param Content $content new article content
     * @param string $summary edit summary
     * @param bool $isMinor minor flag
     * @param bool $isWatch <i>null</i>
     * @param bool $section <i>null</i>
     * @param $flags
     * @param Status $status edit status
     */
    public static function onPageContentSave( &$wikiPage, &$user, &$content, &$summary,
                                              $isMinor, $isWatch, $section, &$flags, &$status ) {
        // limit hook to saves (not creates) of MOOC entities
        if ( $wikiPage->getContentModel() === MoocContent::CONTENT_MODEL_MOOC_ITEM && $wikiPage->exists() ) {
            $pageContent = $wikiPage->getContent()->serialize();
            $moocContent = new MoocContent( $pageContent );
            $newMoocContent = self::mergeResourceFileIntoMoocContent( $moocContent, $content );
            if ( $newMoocContent === null ) {
                // invalid MOOC entity or not a MoocResource
            } else {
                $content = $newMoocContent;
            }
        }
    }

    /**
     * Creates a new MOOC content by injecting the given resource file content into an existing MOOC content.
     *
     * @param MoocContent $moocResourceContent current MOOC content (from existing page)
     * @param Content $resourceFileContent new resource file content (edit text)
     * @return MoocContent|null MOOC content with given resource file content or <i>null</i> on error
     */
    private static function mergeResourceFileIntoMoocContent($moocResourceContent, $resourceFileContent ) {
        if ( $moocResourceContent->isValid() && $moocResourceContent->entity instanceof MoocResource ) {
            // inject resource file content into valid MOOC resource
            $moocResourceContent->entity->content = $resourceFileContent->getNativeData();
            // create new MOOC content from resource entity
            return new MoocContent( json_encode( $moocResourceContent->entity->toJson() ) );
        }
        return null;
    }
}
