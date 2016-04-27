<?php
/**
 * Hooks for MOOC extension
 *
 * @file
 * @ingroup Extensions
 * 
 * @author Rene Pickhardt (User:renepick)
 * @license GPLv3
 */

class MOOCHooks {
	

	public static function onParserFirstCallInit( Parser &$parser ) {
		//register funciton for Magic Keyword MOOC
		$parser->setFunctionHook( 'MOOC', 'MOOCHooks::parseMooc' );
	}
	
	/**
	 * If Magic Keyword {{#MOOC: }} is detected on a page exchagne CSS and potentially java script
	 * @param Parser $parser
	 */
	public static function parseMooc( Parser &$parser )
	{
		global $wgOut;
		
		$out = $parser->getOutput();
		global $MOOCFilesDir;
		$out->addHeadItem('<link rel="stylesheet" href="/w/extensions/MOOC/MOOC.css" type="text/css" media="screen" />');
		
		
		return "";//$out->getText();
	}
}
