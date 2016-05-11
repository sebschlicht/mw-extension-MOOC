<?php

/**
 * Try parsing the following code structure and display ith with nice css.ß
 * __MOOC__
== Lesson1 ==
=== unit a ===
=== unit b ===


== Lesson 2 ==
=== unit x ===
=== unit y ===

== Lesson 3 ==
=== unit 1 ===
=== unit 2 ===

 * 
 */


if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'MOOC' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MOOC'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['MOOCAlias'] = __DIR__ . '/MOOC.i18n.alias.php';
    $wgExtensionMessagesFiles['MOOCMagic'] = __DIR__ . '/MOOC.i18n.magic.php';
    
	wfWarn(
		'Deprecated PHP entry point used for MOOC extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the MOOC extension requires MediaWiki 1.25+' );
}
