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
     * Registers parser function for magic keywords.
     *
     * @param Parser $parser            
     */
    public static function onParserFirstCallInit(Parser &$parser) {
        // register funciton for Magic Keyword MOOC
        $parser->setFunctionHook('MOOC', 'MOOCHooks::parseMooc');
    }

    /**
     * If the *Magic Keyword* <code>{{#MOOC: }}</code> is detected on a page register the <code>renderMoocPage</code>
     * hook.
     *
     * @param Parser $parser
     *            parser
     */
    public static function parseMooc(Parser &$parser, $frame, $args) {
        global $wgHooks;
        // TODO maybe we have to hook in earlier, sth. like `OutputPageBeforeWikiText`
        $wgHooks['OutputPageBeforeHTML'][] = 'MOOCHooks::renderMoocPage';
        // $parser->getOutput()->addModules('ext.mooc');
        $parser->getOutput()->addModuleScripts('ext.mooc');
        $parser->getOutput()->addModuleStyles('ext.mooc');
        $parser->disableCache();
    }

    /**
     * Renders a wiki page as a MOOC item.
     *
     * This includes the manipulation of the DOM tree, namely:
     * <ul>
     * <li>rebuild the body structure to limit the content width</li>
     * <li>inject a MOOC navigation into the free space gained</li>
     * <li>split the page into sections (by <code>h2</code> tags)</li>
     * <li>enrich the sections with headers</li>
     * </ul>
     *
     * @param OutputPage $out
     *            output page
     * @param string $text
     *            page HTML that has been generated so far
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
        // WARNING: font-size decreases unrecoverably when adding Bootstrap via CDN after extension CSS loaded
        // $out->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        
        // TODO does not work, has to be done by an earlier hook
        $out->enableTOC(false);
        
        /*
         * TODO WARNING: DOM manipulations theirselves are NOT parsed by the MediaWiki.
         * If manipulations should be parsed, such as links that may be redlinks aso., they have to be parsed manually.
         * This can be done using OutputPage.
         */
        $renderer = new MoocItemRenderer($out, $text);
        $text = $renderer->render();
        echo 'parsing took: ' . (microtime(true) - $tStart) . " ms<br>\n";
        return true;
    }
}
