<?php
/**
 * Translations of the namespaces introduced by this MOOC extension.
 *
 * @file
 */
$namespaceNames = array();

// For wikis without MOOC installed.
if (! defined('NS_MOOC')) {
    define('NS_MOOC', 350);
    define('NS_MOOC_TALK', 351);
}

/**
 * English
 */
$namespaceNames['en'] = array(
    NS_MOOC => 'Course',
    NS_MOOC_TALK => 'Course_talk'
);

/**
 * German
 */
$namespaceNames['de'] = array(
    NS_MOOC => 'Kurs',
    NS_MOOC_TALK => 'Kurs_talk'
);
