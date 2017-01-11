( function ( mw, $ ) {

  // globals to make section headers sticky+
  var $window = $(window);
  var $sections = $('#mooc-sections').find('.section');
  var $activeSection = null;

  // make section headers absolute for stickyness
  //TODO make this a separate LESS file and pack JS and LESS to an own module
  $sections.each( function() {
    var $section = $(this);
    var $sectionHeader = $section.children('.header');
    $sectionHeader.css('position', 'absolute').css('top', 0).css('width', '100%');
    $section.css('padding-top', $sectionHeader.outerHeight());
  } );

  function setActiveSection($section) {
    if ($activeSection !== null) {
      setSectionActive($activeSection, false);
      resetSectionHeader($activeSection.children('.header'));
    }
    if ($section !== null) {
      setSectionActive($section, true);
    } else {
      //TODO unset fragment
    }
    $activeSection = $section;
  }

  function setSectionActive($section, isActive) {
    if (isActive) {
      $section.addClass('active');
    } else {
      $section.removeClass('active');
    }
  }

  function fixSectionHeader($header, top, sectionWidth) {
    $header.css('position', 'fixed');
    $header.css('top', top);
    $header.css('width', sectionWidth);
    $header.removeClass('trailing');
    $header.addClass('fixed');
  }

  function trailSectionHeader($header, sectionHeight, isFixed) {
    resetSectionHeader($header, isFixed);
    $header.css('top', sectionHeight - $header.outerHeight());
    $header.addClass('trailing');
  }

  function resetSectionHeader($header, isFixed) {
    if (isFixed || (isFixed === undefined && $header.hasClass('fixed'))) {
      $header.css('position', 'absolute');
      $header.css('width', '100%');
      $header.removeClass('fixed');
    }
    $header.css('top', 0);
    $header.removeClass('trailing');
  }

  $window.scroll( function() {
    var y = $window.scrollTop();
    var marginTop = 0;

    var $crrActiveSection = null;
    $sections.each( function() {
      var $section = $(this);
      var $sectionHeader = $section.children('.header');
      var sectionTop = $section.offset().top;
      var sectionHeight = $section.outerHeight();
      var isActive = $section.hasClass('active');
      var isFixed = $sectionHeader.hasClass('fixed');

      if (y >= sectionTop && y <= sectionTop + sectionHeight) {// active section
        if (!isActive) {
          setActiveSection($section);
        }
        $crrActiveSection = $section;
        if (y <= sectionTop + sectionHeight - $sectionHeader.outerHeight()) {// header can be fixed
          if (!isFixed) {
            fixSectionHeader($sectionHeader, marginTop, $section.width());
          }
        } else {// header reached section bottom
          if (!$sectionHeader.hasClass('trailing')) {
            trailSectionHeader($sectionHeader, sectionHeight, isFixed);
          }
        }
      } else { // inactive section
        if (isActive) {
          resetSectionHeader($sectionHeader, isFixed);
        }
      }
    } );

    // unset section fragment when leaving
    if ($crrActiveSection === null) {
      setActiveSection(null);
    }
  } );

}( mediaWiki, jQuery ) );