# MOOC
Allows to create and maintain Massive Open Online Courses.

A wiki page that includes the magic word `MOOC` will be rendered as a MOOC page:

    ...
    {{#MOOC: }}
    ...

## Technical Information
The extension is activated via a conditioned hook:

1. Wiki pages are scanned for the magic word `MOOC`.
2. *If* the magic word has been found, we trigger the resource loader and register an `OutputPageBeforeHTML` hook.
3. This hook finally triggers the HTML structure manipulation of the wiki page content.

The conditioned hook activation method is supposed to reduce the overhead of the extension.

## Files
This extension mainly consists of the following files:
* `MOOC.php` - MediaWiki entry point
* `MOOC.hooks.php` - Extension entry point and code (**TODO**: separate extension code from entry point to `MOOC.class.php`)
* `resources/ext.mooc.css` - stylesheet for MOOC pages

## Links
* [Extension Page](https://www.mediawiki.org/wiki/Extension:MOOC)
* [Phabricator Page](https://phabricator.wikimedia.org/diffusion/1892/repository/master/)
* [Gerrit Page](https://gerrit.wikimedia.org/r/#/admin/projects/mediawiki/extensions/MOOC)
* [Extension Prototype Page (Wikiversity)](https://en.wikiversity.org/wiki/Wikiversity:MOOC_Interface)
* [Extension Prototype Demo (Wikiversity)](https://en.wikiversity.org/wiki/Web_Science/Part1:_Foundations_of_the_web/Ethernet)
