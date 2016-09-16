<?php

/*
 * Try parsing the following code structure and display it with nice CSS.
 *
 * <code>
 * {{#MOOC: base=path/to/mooc/base }}
 * == Learning goals ==
 * # ...
 * # ...
 *
 * == Video ==
 * [[File:...|800px]]
 *
 * == Units ==
 * === unit 1 ===
 * === unit 2 ===
 * </code>
 */
if (function_exists('wfLoadExtension')) {
    wfLoadExtension('MOOC');
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['MOOC'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['MOOCAlias'] = __DIR__ . '/MOOC.i18n.alias.php';
    $wgExtensionMessagesFiles['MOOCMagic'] = __DIR__ . '/MOOC.i18n.magic.php';
    
    // TODO get out how to include jquery.ui.effects (includes easing) other than shipping
    
    $wgMOOCSectionNames = [
        'learning-goals',
        'video',
        'script',
        'quiz',
        'further-reading',
        'lessons',
        'units'
    ];
    $wgMOOCSections = [
        'learning-goals' => [
            'title' => 'Learning Goals',
            'icon' => 'Wikiversity-Mooc-Icon-Learning-goals.svg',
            'collapsed' => false
        ],
        'video' => [
            'title' => 'Video',
            'icon' => 'Wikiversity-Mooc-Icon-Video.svg',
            'collapsed' => false
        ],
        'script' => [
            'title' => 'Script',
            'icon' => 'Wikiversity-Mooc-Icon-Script.svg',
            'collapsed' => true
        ],
        'quiz' => [
            'title' => 'Quiz',
            'icon' => 'Wikiversity-Mooc-Icon-Quiz.svg',
            'collapsed' => true
        ],
        'further-reading' => [
            'title' => 'Further Reading',
            'icon' => 'Wikiversity-Mooc-Icon-Further-readings.svg',
            'collapsed' => false
        ],
        'lessons' => [
            'title' => 'Lessons',
            'icon' => 'Wikiversity-Mooc-Icon-Associated-units.svg',
            'collapsed' => false
        ],
        'units' => [
            'title' => 'Units',
            'icon' => 'Wikiversity-Mooc-Icon-Associated-units.svg',
            'collapsed' => false
        ]
    ];
    $wgMOOCClasses = [
        'section' => 'mooc-section',
        'section-markers' => 'mooc-section-markers'
    ];
    return true;
} else {
    die('This version of the MOOC extension requires MediaWiki 1.25+');
}
