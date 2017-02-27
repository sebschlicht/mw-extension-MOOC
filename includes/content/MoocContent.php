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
     * @var MoocItem|MoocResource MOOC entity being loaded
     */
    public $entity;

    /**
     * @param string $text MOOC entity JSON
     * @param string $modelId identifier of the page's content model
     */
    public function __construct($text, $modelId = self::CONTENT_MODEL_MOOC_ITEM) {
        parent::__construct($text, $modelId);
    }

    /**
     * Decodes the page content JSON into a MOOC entity.
     *
     * @return MoocEntity MOOC entity loaded from the content
     */
    public function loadItem() {
        return MoocEntity::loadFromJson( null, (array) parent::getData()->getValue() );
    }

    /**
     * Checks whether the page content is valid JSON and a valid MOOC entity.
     * If the page content is valid, the MOOC entity will be loaded into the <i>entity</i> field.
     *
     * @return bool whether the content is valid or not
     */
    public function isValid() {
        // check for valid JSON
        if (parent::isValid()) {
            // load MOOC entity if not loaded yet
            if (!isset($this->entity)) {
                $this->entity = $this->loadItem();
            }
            return ( $this->entity != null );
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
        if ( $generateHtml && $this->isValid() ) {
            $output->setTOCEnabled( false );

            if ( $this->entity instanceof MoocResource ) {
                // MOOC resource: render content as Wikitext
                $tmpOut = new OutputPage();
                $tmpOut->addWikiText( $this->entity->content );
                $output->setText( $tmpOut->getHTML() );
            } else {
                // MOOC item: let content renderer decide
                $this->entity->setTitle( $title );
                $output->setEnableOOUI( true );
                $output->addModuleScripts( 'ext.mooc' );
                $output->addModuleStyles( 'ext.mooc' );

                $output->setText( MoocContentRenderer::renderItem( $output, $this->entity ) );

                // pass data to JS
                $output->addJsConfigVars( [
                    'moocAgentData' => [
                        'userAgentName' => 'MoocBot',
                        'userAgentUrl' => 'https://en.wikiversity.org/wiki/User:Sebschlicht',
                        'userAgentMailAddress' => 'sebschlicht@uni-koblenz.de',
                        'version' => '0.1'
                    ],
                    'moocItem' => $this->entity->toJson()
                ] );
            }
        } else {
            $output->setText( '' );
        }
    }
}
