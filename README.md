# MOOC

The Massive Open Online Course (MOOC) extension allows to create, maintain and attend online courses in a user-friendly way.  
It introduces the *Course* namespace where the MediaWiki user-interface is enhanced, in order to offer the relevant functions to the user.

## Installation

1. install the dependencies
   * [MwEmbedSupport](https://www.mediawiki.org/wiki/Extension:MwEmbedSupport)
   * [TimedMediaHandler](https://www.mediawiki.org/wiki/Extension:TimedMediaHandler)
1. and additionally append the following line to your *LocalSettings.php*:
   `require_once "$IP/extensions/MOOC/MOOC.php";`

## Technical Information

Technically a course in the *Course* namespace is a page with the content model `mooc-item` (format: *JSON*) that is defined by this extension.

Though possible in principle, this extension does not fully work without JavaScript on user-side.

## Files

This extension mainly consists of the following files:
* `MOOC.php` - MediaWiki entry point
* `MOOC.hooks.php` - extension initialization
* `resources/ext.mooc.css` - stylesheet for MOOC pages

## Links

* [Extension Page](https://www.mediawiki.org/wiki/Extension:MOOC)
* [Project on Phabricator](https://phabricator.wikimedia.org/diffusion/1892/repository/master/)
* [Project on Gerrit](https://gerrit.wikimedia.org/r/#/admin/projects/mediawiki/extensions/MOOC)
* [Extension Prototype (Wikiversity)](https://en.wikiversity.org/wiki/Wikiversity:MOOC_Interface)
* [Extension Prototype Demo (Wikiversity)](https://en.wikiversity.org/wiki/Web_Science/Part1:_Foundations_of_the_web/Ethernet)
