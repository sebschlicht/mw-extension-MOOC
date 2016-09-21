( function ( mw, $ ) {
  
  var hCollapsedSection = 200;
  
  // register UI event hooks
  $('#mooc-sections .section .header .actions .btn-edit').on('click', openModalBox);
  $('#mooc-sections .section .header .modal-box-bg').on('click', closeModalBoxes);
  $('#mooc-sections .section .header .modal-box button.close').on('click', closeModalBoxes);
  $('#mooc-sections .section .header .modal-box button.btn-cancel').on('click', closeModalBoxes);
  $('#mooc-sections .section').each(function(index, element) {
    initSection($(element));
  });
  
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
  
  $('#mooc-sections .section')
    .on('mouseenter', showActions)
    .on('mouseleave', hideActions);
  $('#mooc-sections .section .header .actions').hide();
  
  // globals to make navigation bar sticky+
  var $navigationBar = $('#mooc-navigation-bar');
  var $navigation = $navigationBar.find('#mooc-navigation');
  var $navigationHeader = $navigation.find('.header');
  var marginBottom = 30;
  
  // TODO load modal box content
  
  function openModalBox() {
    var $modal = $(this).parent().siblings('.modal-box-wrapper');
    $modal.fadeIn(200);
    $modal.find('textarea').focus();
    return false;
  }
  function closeModalBoxes() {
    var $visibleModalBoxes = $('#mooc-sections .modal-box-wrapper:visible');
    closeModalBox($visibleModalBoxes);
  }
  function closeModalBox($modal) {
    $modal.find('textarea').blur();
    $modal.fadeOut(200);
  }
  
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
  
  function showActions() {
    $(this).children('.header').children('.actions').fadeIn();
  }
  function hideActions() {
    $(this).children('.header').children('.actions').fadeOut();
  }
  
  // make navigation bar sticky+
  $(window).scroll(function() {
    var y = $(window).scrollTop();
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    var rootTop = $navigationBar.offset().top;
    var maxY = rootTop + $navigation.outerHeight();
    
    var isNavigationFixed = $navigation.hasClass('fixed');
    var isNavigationHeaderFixed = $navigationHeader.hasClass('fixed');
    var isNavigationTrailing = $navigation.hasClass('trailing');
    
    if (y >= rootTop) {// navigation bar is (about to scroll) out of view
      if (h - marginBottom >= $navigation.outerHeight()) {// navigation fits view
        if (!isNavigationFixed) {
          fixNavBar($navigation);
        }
      } else {// navigation too large
        if (!isNavigationHeaderFixed) {// fix navigation header
          fixNavBarHeader($navigationHeader);
        }
        if (y + h >= maxY + marginBottom) {// bottom reached, disable scrolling
          if (!isNavigationTrailing) {
            preventNavBarScrolling($navigation, marginBottom);
          }
        } else {// content available, activate scrolling (again)
          if (isNavigationTrailing) {
            resetNavBar($navigation);
          }
        }
      }
    } else {
      if (isNavigationHeaderFixed) {
        resetNavBarHeader($navigationHeader);
      }
      if (isNavigationFixed) {
        resetNavBar($navigation);
      }
    }
  });
  function fixNavBarHeader($header) {
    $header.css('width', $header.outerWidth());
    $header.parent().css('padding-top', $header.outerHeight());
    $header.addClass('fixed');
  }
  function resetNavBarHeader($header) {
    $header.removeClass('fixed');
    $header.css('width', '');
    $header.parent().css('padding-top', '0');
  }
  function fixNavBar($navigation) {
    $navigation.removeClass('trailing');
    $navigation.css('width', $navigation.outerWidth());
    $navigation.css('top', 0);
    $navigation.addClass('fixed');
  }
  function preventNavBarScrolling($navigation, marginBottom) {
    $navigation.css('width', $navigation.outerWidth());
    $navigation.css('top', '');
    $navigation.css('bottom', marginBottom);
    $navigation.addClass('trailing');
  }
  function resetNavBar($navigation) {
    $navigation.removeClass('fixed');
    $navigation.removeClass('trailing');
    $navigation.css('width', '');
    $navigation.css('bottom', '');
  }
  // repair navigation bar when window is resized
  $(window).resize(function() {
    resetNavBarHeader($navigationHeader);
    resetNavBar($navigation);
    $(window).scroll();
  });
  
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
