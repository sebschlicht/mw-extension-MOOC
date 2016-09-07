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
            'icon' => 'Wikiversity-Mooc-Icon-Learning-goals.svg'
        ],
        'video' => [
            'title' => 'Video',
            'icon' => 'Wikiversity-Mooc-Icon-Video.svg'
        ],
        'script' => [
            'title' => 'Script',
            'icon' => 'Wikiversity-Mooc-Icon-Script.svg'
        ],
        'quiz' => [
            'title' => 'Quiz',
            'icon' => 'Wikiversity-Mooc-Icon-Quiz.svg'
        ],
        'further-reading' => [
            'title' => 'Further Reading',
            'icon' => 'Wikiversity-Mooc-Icon-Further-readings.svg'
        ],
        'lessons' => [
            'title' => 'Lessons',
            'icon' => 'Wikiversity-Mooc-Icon-Associated-units.svg'
        ],
        'units' => [
            'title' => 'Units',
            'icon' => 'Wikiversity-Mooc-Icon-Associated-units.svg'
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
