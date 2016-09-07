<?php

/**
 * Hooks for MOOC extension
 *
 * @file
 * @ingroup Extensions
 *
 * @author Rene Pickhardt (User:renepick), Sebastian Schlicht (User:sebschlicht)
 * @license GPLv3
 */
class MOOCHooks {

    /**
     * registeres parser function for magic keywords.
     *
     * @param Parser $parser            
     */
    public static function onParserFirstCallInit(Parser &$parser) {
        // register funciton for Magic Keyword MOOC
        $parser->setFunctionHook('MOOC', 'MOOCHooks::parseMooc');
    }

    /**
     * If Magic Keyword {{#MOOC: }} is detected on a page register renderMoocPage function
     *
     * @param Parser $parser            
     */
    public static function parseMooc(Parser &$parser, $frame, $args) {
        global $wgHooks;
        // TODO maybe we have to hook in earlier, sth. like `OutputPageBeforeWikiText`
        $wgHooks['OutputPageBeforeHTML'][] = 'MOOCHooks::renderMoocPage';
        $parser->getOutput()->addModuleStyles('ext.mooc');
        $parser->disableCache();
    }

    /**
     * Currently enriches h2 with a css class to demonstrate our planned workflow / dataflow to enrich html for MOOC
     * pages
     *
     * currently the reason to use outputPageBeforeHTML hook is that we needed the html text. is there another more
     * suitable hook?
     *
     * @param OutputPage $out            
     * @param
     *            HTML String $text
     */
    public static function renderMoocPage(&$out, &$text) {
        $tStart = microtime(true);
        
        // inspired by https://www.mediawiki.org/wiki/Extension:BrettCrumbs
        // cancel if action unsupported
        global $action;
        if ($action == 'edit')
            return true;
        if ($action == 'history')
            return true;
            
            // cancel if on main page or namespace unsupported
        $title = $out->getTitle();
        if ($title->isMainPage() || $title->inNamespace(NS_FILE) || $title->inNamespace(NS_USER) ||
             $title->inNamespace(NS_SPECIAL)) {
            return true;
        }
        global $wgMOOCClasses, $wgMOOCSectionNames, $wgMOOCSections;
        
        // TODO add Bootstrap extension as dependency?
        // FIXME looks like the default font-size has decreased with Bootstrap
        // $out->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        
        // TODO does not seem to work
        // disable TOC
        $out->enableTOC(false);
        
        /*
         * TODO WARNING: DOM manipulations theirselves are NOT parsed by the MediaWiki.
         * If manipulations should be parsed, such as links that may be redlinks aso., they have to be parsed manually.
         * This can be done using an OutputPage object.
         */
        $renderer = new MoocItemRenderer($out, $text);
        $text = $renderer->render();
        echo 'parsing took: ' . (microtime(true) - $tStart) . " ms<br>\n";
        return true;
    }
}
