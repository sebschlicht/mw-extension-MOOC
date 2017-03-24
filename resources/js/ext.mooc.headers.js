( function ( mw, $ ) {

  // globals to make section headers sticky+
  var $window = $( window );
  var $sections = $( '#mooc-sections' ).find( '.section' );
  var $activeSection = null;

  // prepare sections for stickyness
  //TODO make this a separate LESS file and pack JS and LESS to an own module
  $sections.each( function () {
    var $section = $( this );
    var $sectionHeader = $section.children( '.header' );
    $sectionHeader.addClass( 'enabled' );
    var $sectionContent = $section.children( '.content' );
    $sectionContent.css( 'margin-top', $sectionHeader.outerHeight() );
  });

  // update section headers on scroll
  var $itemNavigation = $( '#itemnav' );
  $window.scroll( updateSectionHeaders );
  $window.scroll();
  $window.resize( resizeSectionHeader );

  /**
   * Updates the headers of all sections:
   * Each section header is fixed ("fixed") to the top of the screen as long as the user is scrolling its section.
   * When the section is scrolling out of view and there is not enough space for the header, it is placed at the very bottom of the section ("trailing").
   * Headers of all the other sections are placed on top of their sections ("reset") just like in the default layout.
   */
  function updateSectionHeaders() {
    var y = $window.scrollTop();
    var marginTop = 0;
    if ( $itemNavigation.hasClass( 'fixed' ) ) {// correct scroll position
      marginTop = $itemNavigation.outerHeight();
      y += marginTop;
    }

    var $crrActiveSection = null;
    $sections.each( function ( i, ele ) {
      var $section = $( ele );
      var $sectionHeader = $section.children( '.header' );
      var sectionTop = $section.offset().top;
      var sectionHeight = $section.outerHeight();
      var isActive = $section.hasClass( 'active' );
      var isFixed = $sectionHeader.hasClass( 'fixed' );

      if ( y >= sectionTop && y <= sectionTop + sectionHeight ) {// active section
        if ( !isActive ) {
          setActiveSection( $section );
        }
        $crrActiveSection = $section;
        if ( y <= sectionTop + sectionHeight - $sectionHeader.outerHeight() ) {// header can be fixed
          if ( !isFixed ) {
            fixSectionHeader( $sectionHeader, marginTop, $section.width() );
          }
        } else {// header reached section bottom
          if ( !$sectionHeader.hasClass( 'trailing' ) ) {
            trailSectionHeader( $sectionHeader, sectionHeight );
          }
        }
      } else { // inactive section
        if ( isActive ) {
          resetSectionHeader( $sectionHeader );
        }
      }
    } );

    // unset section fragment when leaving
    if ( $crrActiveSection === null ) {
      setActiveSection( null );
    }
  }

  /**
   * Sets a section as active section and marks it as active. The previously active section is marked as inactive, if any.
   *
   * @param $section section jQuery-element
   */
  function setActiveSection( $section ) {
    if ( $activeSection !== null ) {
      setSectionActive( $activeSection, false );
      resetSectionHeader( $activeSection.children( '.header' ) );
    }
    if ( $section !== null ) {
      setSectionActive( $section, true );
    } else {
      //TODO unset fragment
    }
    $activeSection = $section;
  }

  /**
   * Sets the activation state of a section.
   *
   * @param $section section jQuery-element
   * @param isActive new activation state
   */
  function setSectionActive( $section, isActive ) {
    if ( isActive ) {
      $section.addClass( 'active' );
    } else {
      $section.removeClass( 'active' );
    }
    // toggle item navigation link
    if ( $itemNavigation.length !== 0 ) {
      var sectionId = $section.attr( 'id' );
      var $sectionLink = $itemNavigation.find( '.' + sectionId );
      if ( isActive ) {
        $sectionLink.addClass( 'active' );
      } else {
        $sectionLink.removeClass( 'active' );
      }
    }
  }

  /**
   * Fixes the section header on top of the screen.
   *
   * @param $header section header jQuery-element
   * @param top top margin
   * @param width section width to adopt
   */
  function fixSectionHeader( $header, top, width ) {
    $header.css( 'top', top );
    $header.css( 'width', width );
    $header.removeClass( 'trailing' );
    $header.addClass( 'fixed' );
  }

  /**
   * Trails a section header by placing it at the bottom of its section.
   *
   * @param $header section header jQuery-element
   * @param sectionHeight section height
   */
  function trailSectionHeader( $header, sectionHeight ) {
    resetSectionHeader( $header );
    $header.css( 'top', sectionHeight - $header.outerHeight() );
    $header.addClass( 'trailing' );
  }

  /**
   * Resets a section header to its default position on top of its section.
   *
   * @param $header section header jQuery-element
   */
  function resetSectionHeader( $header ) {
    if ( $header.hasClass( 'fixed' ) ) {
      $header.css( 'top', '' );
      $header.css( 'width', '' );
      $header.removeClass( 'fixed' );
    }
    $header.removeClass( 'trailing' );
  }

  /**
   * Resizes the active section header to fit its container.
   */
  function resizeSectionHeader() {
    if ( $activeSection !== null ) {
      var $sectionHeader = $activeSection.children( '.header' );
      var isSectionHeaderFixed = $sectionHeader.hasClass( 'fixed' );
      if ( isSectionHeaderFixed ) {
        resetSectionHeader( $sectionHeader );
      }
    }
    updateSectionHeaders();
  }

}( mediaWiki, jQuery ) );
