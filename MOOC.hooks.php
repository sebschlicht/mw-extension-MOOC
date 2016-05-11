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
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		//register funciton for Magic Keyword MOOC
		$parser->setFunctionHook( 'MOOC', 'MOOCHooks::parseMooc' );
	}
	
	/**
	 * If Magic Keyword {{#MOOC: }} is detected on a page register renderMoocPage function
	 * @param Parser $parser
	 */
	public static function parseMooc( Parser &$parser, $frame, $args )
	{		
		global $wgHooks;
		$wgHooks['OutputPageBeforeHTML'][] = 'MOOCHooks::renderMoocPage';
	}
	
	/**
	 * Currently enriches h2 with a css class to demonstrate our planned workflow / dataflow to enrich html for MOOC pages
	 * 
	 * currently the reason to use outputPageBeforeHTML hook is that we needed the html text. is there another more suitable hook?
	 * 
	 * @param OutputPage $out
	 * @param HTML String $text
	 */
	public function renderMoocPage (&$out, &$text) {	
		// inspired by https://www.mediawiki.org/wiki/Extension:BrettCrumbs
		global $wgTitle, $action ;
		if ( $action == 'edit' ) return true;
		if ( $action == 'history' ) return true;
		if ( $wgTitle->getPrefixedText() == 'Main Page' ) return true;
		if ( strpos ( $wgTitle->getPrefixedText(), 'User:' ) === 0 ) return true;
		if ( strpos ( $wgTitle->getPrefixedText(), 'Special:' ) === 0 ) return true;
		if ( strpos ( $wgTitle->getPrefixedText(), 'File:' ) === 0 ) return true;
	
		$dom = new DOMDocument;
		$dom->loadHTML($text);
		$sections = $dom->getElementsByTagName('h2');
		foreach ($sections as $section) {
			$class = 'mooc-section';
			if ($section->hasAttribute('class'))
				$class = $class . ' ' .$section->getAttribute('class');
			$section->setAttribute('class', $class);
		}
		$text = $dom->saveHTML();		
		return true;
	}
}
