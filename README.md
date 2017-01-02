# MOOC

The Massive Open Online Course (MOOC) extension allows to create, maintain and attend online courses in a user-friendly way.
It enhances (or rather replaces) the user interface of the MediaWiki, in order to offer the relevant functions to the user.

## Installation

1. install the dependencies
   * [MwEmbedSupport](https://www.mediawiki.org/wiki/Extension:MwEmbedSupport)
   * [TimedMediaHandler](https://www.mediawiki.org/wiki/Extension:TimedMediaHandler)
1. and additionally append the following line to your *LocalSettings.php*:
   `require_once "$IP/extensions/MOOC/MOOC.php";`

## Technical Information

Technically this extension introduces a namespace (*Course*) where courses can be stored.
Courses that have been created using this extension still are pages using a new content model `mooc-item` which is based on *JSON*.

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
