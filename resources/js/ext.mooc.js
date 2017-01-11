( function ( mw, $ ) {
  
  var hCollapsedSection = 200;
  var $openedModalBox = null;
  
  // register UI event hooks
  var $sections = $('#mooc-sections').find('.section');
  var $headers = $sections.find('.header');

  // open modal boxes via action buttons
  $headers.find('.actions .btn-edit').on('click', openModalEditBox);
  $headers.find('.actions .btn-add').on('click', openModalAddBox);
  // close modal boxes via background, close and cancel
  $headers.find('.modal-bg').on('click', closeModalBoxes);
  $headers.find('.modal-box .btn-close').on('click', closeModalBoxes);
  $headers.find('.modal-box .btn-cancel').on('click', closeModalBoxes);
  $sections.each(function(index, element) {
    var $section = $(element);

    // collapse sections and hide action buttons
    initSection($section);
    hideActions($section);

    // register links in empty sections
    $section.find('.content .edit-link').on('click', openModalEditBox);
    $section.find('.content .add-link').on('click', openModalAddBox);
  });

  // close modal box on key ESC down
  $(document).keydown(function(e){
    e = e || window.event;
    var isEscape = false;
    if ("key" in e) {
        isEscape = (e.key === "Escape");
    } else {
        isEscape = (e.keyCode === 27);
    }
    if (isEscape) {
      closeModalBoxes();
    }
  });

  // show/hide actions if mouse is inside/outside the respective section
  $sections
    .on('mouseenter', showActions)
    .on('mouseleave', hideActions);

  /**
   * Shows the action buttons of a section.
   */
  function showActions() {
    $(this).children('.header').children('.actions').addClass('visible');
  }

  /**
   * Hides the action buttons of a section.
   */
  function hideActions() {
    $(this).children('.header').children('.actions').removeClass('visible');
  }

  /*
   * ##########################
   * Open and close modal boxes
   * ##########################
   */

  /**
   * Opens the modal edit box of a section when an element in the section has been clicked.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalEditBox() {
    return openModalBox($(this), 'edit');
  }

  /**
   * Opens the modal add box of a section when an element in the section has been clicked.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalAddBox() {
    return openModalBox($(this), 'add');
  }

  /**
   * Opens a modal box of a section.
   *
   * @param $element element in the section that has been clicked
   * @param action action the modal box should be opened for
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalBox($element, action) {
    var $section = $element.parents('.section');
    var $modal = $section.find('.header .modal-wrapper');
    if ($modal.length > 1) {
      // filter by action if multiple modal boxes available
      $modal = $modal.filter(function(index, $el) {
        return ($el.find('form.' + action).length > 1);
      });
    }

    $modal.fadeIn(200);
    $openedModalBox = $modal;
    $modal.find('.value').focus();
    return false;
  }

  /**
   * Closes the currently open modal box - if any.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function closeModalBoxes() {
    if ($openedModalBox !== null) {
        closeModalBox($openedModalBox);
    }
    return false;
  }

  /**
   * Closes a modal box.
   *
   * @param $modal modal box
   */
  function closeModalBox($modal) {
    $modal.find('.value').blur();
    $modal.fadeOut(200);
  }

  /*
   * ############################
   * Collapse and expand sections
   * ############################
   */

  function initSection($section) {
    // make section collapsable
    if (isSectionCollapsable($section)) {
      enableSectionCollapseUI($section);
    }
    
    // collapse sections with .default-collapsed
    if ($section.hasClass('default-collapsed')) {
      // if collapsible
      if (isSectionCollapsable($section)) {
        collapseSection($section);
      }
      $section.removeClass('default-collapsed');
    }
  }
  function isSectionCollapsable($section) {
    if ($section.hasClass('collapsed')) {
      return true;
    }
    var $content = $section.children('.content');
    
    // calculate effective content height
    // WARNING: this resets the ELEMENT:CSS max-height
    $content.css('max-height', 'none');
    var height = $content.outerHeight();
    $content.css('max-height', '');
    
    return (height > hCollapsedSection);
  }
  
  function collapseClickedSection() {
    var $section = $(this).parent();
    collapseSection($section);
  }
  function collapseSection($section) {
    $section.addClass('collapsed');
    
    var $content = $section.children('.content');
    $content.stop().css('max-height', hCollapsedSection + 'px');
    var $expander = $section.children('.expander');
    $expander.stop().hide().fadeIn();
    
    // enable UI to expand section
    enableSectionExpandUI($section);
  }
  function enableSectionCollapseUI($section) {
    $section.removeClass('expandable');
    $section.addClass('collapsable');
    var $header = $section.children('.header');
    $header.off('click').on('click', collapseClickedSection);
  }
  
  function expandClickedSection() {
    var $section = $(this).parent();
    expandSection($section);
  }
  function expandSection($section) {
    $section.removeClass('collapsed');
    
    // TODO make this fucking work with CSS3 transitions
    var $content = $section.children('.content');
    var maxHeight = $content.css('max-height');
    $content.css('max-height', 'none');
    var contentHeight = $content.outerHeight();
    $content.css('max-height', maxHeight);
    $content.stop().animate({
      'max-height': contentHeight + 'px'
    }, 300, 'easeInQuint');
    
    // TODO add this via classes and let CSS3 do its work!
    var $expander = $section.children('.expander');
    $expander.stop().show().fadeOut();
    
    enableSectionCollapseUI($section);
  }
  function enableSectionExpandUI($section) {
    $section.removeClass('collapsable');
    $section.addClass('expandable');
    var $header = $section.children('.header');
    $header.off('click').on('click', expandClickedSection);
    var $expander = $section.children('.expander');
    $expander.on('click', expandClickedSection);
  }
  
  // hide MW UI
  var $mwContent = $('#content.mw-body');
  var contentMarginLeft = $mwContent.css('margin-left');
  var $mwNavigation = $('#mw-navigation');
  var $mwPageBase = $('#mw-page-base');
  
  var $mwNavButton = $('<img>').addClass('btn-toggle-mw-nav');
  $mwNavButton.attr('src', '/mediawiki-vagrant.png');
  $mwNavButton.on('click', mwNavigationButtonClicked);
  //$mwNavButton.insertBefore($mwNavigation);
  //hideMwNavigation($mwNavigation);
  
  function mwNavigationButtonClicked() {
    if ($mwNavigation.hasClass('hidden')) {
      showMwNavigation($mwNavigation);
    } else {
      hideMwNavigation($mwNavigation);
    }
  }
  function hideMwNavigation($mwNavigation) {
    $mwContent.stop().animate({
      'margin-left': 0
    });
    $mwPageBase.slideUp();
    $mwNavigation.addClass('hidden');
  }
  function showMwNavigation($mwNavigation) {
    $mwContent.stop().animate({
      'margin-left': contentMarginLeft
    });
    $mwPageBase.slideDown();
    $mwNavigation.removeClass('hidden');
  }
  
}( mediaWiki, jQuery ) );
