<?php

/**
 * MOOC extension
 */
if (function_exists('wfLoadExtension')) {
    wfLoadExtension('MOOC');
    
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['MOOC'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['MOOCAlias'] = __DIR__ . '/MOOC.i18n.alias.php';
    $wgExtensionMessagesFiles['MOOCNamespaces'] = __DIR__ . '/MOOC.namespaces.php';
    
    // TODO how to include boostrap other than shipping => composer?!
    // TODO get out how to include jquery.ui.effects (includes easing) other than shipping
    
    $wgMOOCSectionConfig = [
        'learning-goals' => [
            'collapsed' => false
        ],
        'video' => [
            'collapsed' => false
        ],
        'script' => [
            'collapsed' => true
        ],
        'quiz' => [
            'collapsed' => true
        ],
        'further-reading' => [
            'collapsed' => false
        ],
        'lessons' => [
            'collapsed' => false
        ],
        'units' => [
            'collapsed' => false
        ]
    ];
    
    global $wgScriptPath;
    $wgExtensionAssetsPath = $wgScriptPath . '/extensions';
    $wgMOOCAssetsPath = $wgExtensionAssetsPath . '/MOOC';
    $wgMOOCImagePath = $wgMOOCAssetsPath . "/images/";
    
    return true;
} else {
    die('This version of the MOOC extension requires MediaWiki 1.25+');
}
