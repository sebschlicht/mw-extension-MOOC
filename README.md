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

Technically, this extension mainly consists of the following files:
* PHP:
  * `includes/content/MoocContent.php` - content type handler for MOOC items
  * `includes/structure/MoocContentStructureProvider.php` - MOOC structure loader
  * `includes/model/MoocItem.php` - basic MOOC item model
  * `includes/rendering/MoocContentRenderer.php` - basic MOOC item renderer
* JavaScript:
  * `resources/js/ext.mooc.js` - general UI
  * `resources/js/ext.mooc.edit.js` - MOOC item editing (e.g. MW.API wrappers)
  * `resources/js/ext.mooc.navigation.js` - sticky navigation
  * `resources/js/ext.mooc.headers.js` - sticky section headers
* Stylesheet:
  * `resources/less/ext.mooc.less` - stylesheet for MOOC pages

## Links

* [Extension Page](https://www.mediawiki.org/wiki/Extension:MOOC)
* [Project on Phabricator](https://phabricator.wikimedia.org/diffusion/1892/repository/master/)
* [Project on Gerrit](https://gerrit.wikimedia.org/r/#/admin/projects/mediawiki/extensions/MOOC)
* [Extension Prototype (Wikiversity)](https://en.wikiversity.org/wiki/Wikiversity:MOOC_Interface)
* [Extension Prototype Demo (Wikiversity)](https://en.wikiversity.org/wiki/Web_Science/Part1:_Foundations_of_the_web/Ethernet)


## Credits
The extension was partly founded by the [Medienanstalt Berlin-Brandenburg](http://www.mabb.de/) as the winner of the [fOERder award 2016](http://open-educational-resources.de/tag/foerder-award-2016/) and by the [University of Koblenz Landau](https://www.uni-koblenz-landau.de/)