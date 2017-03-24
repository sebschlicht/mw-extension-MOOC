( function ( mw, $ ) {

  // globals to make navigation bar sticky+
  var $window = $( window );
  var $navigationBar = $( '#mooc-navigation-bar' );
  var $navigation = $navigationBar.find( '#mooc-navigation' );
  var $navigationHeader = $navigation.find( '.header' );
  var marginBottom = 30;

  // make navigation bar sticky+
  //TODO make navigation bar unsticky when screen width too low
  $window.scroll( updateNavigationBar );
  // resize navigation bar when the window is resized
  $window.resize( updateNavigationBarSize );
  mw.log( 'Navigation activated.' );

  /**
   * Updates the navigation bar:
   *
   * When the navigation bar fits into the viewport (regarding to its height) it is fixed to the top of the screen ("fixed").
   *
   * If it does not, only its header is fixed so the navigation bar itself can be scrolled along with the content.
   * When the bottom of the navigation bar is reached, it is fixed to the bottom of the screen to avoid to be scrolled out of view ("trailing").
   * If the navigation bar is scrolled up again, this process is reverted ("reset").
   */
  function updateNavigationBar() {
    var y = $window.scrollTop();
    var h = Math.max( document.documentElement.clientHeight, window.innerHeight || 0 );
    var rootTop = $navigationBar.offset().top;
    var maxY = rootTop + $navigation.outerHeight();

    var isNavigationFixed = $navigation.hasClass( 'fixed' );
    var isNavigationHeaderFixed = $navigationHeader.hasClass( 'fixed' );
    var isNavigationTrailing = $navigation.hasClass( 'trailing' );

    if ( y >= rootTop ) {// navigation bar is (about to scroll) out of view
      if ( h - marginBottom >= $navigation.outerHeight() ) {// navigation fits view
        if ( !isNavigationFixed ) {
          fixNavBar( $navigation );
        }
      } else {// navigation too large
        if ( !isNavigationHeaderFixed ) {// fix navigation header
          fixNavBarHeader( $navigationHeader );
        }
        if ( y + h >= maxY + marginBottom ) {// bottom reached, disable scrolling
          if ( !isNavigationTrailing ) {
            preventNavBarScrolling( $navigation, marginBottom );
          }
        } else {// content available, activate scrolling (again)
          if ( isNavigationTrailing ) {
            resetNavBar( $navigation );
          }
        }
      }
    } else {
      if ( isNavigationHeaderFixed ) {
        resetNavBarHeader( $navigationHeader );
      }
      if ( isNavigationFixed ) {
        resetNavBar( $navigation );
      }
    }
  }

  /**
   * Fixes the navigation bar header.
   *
   * @param $header header jQuery-element
   */
  function fixNavBarHeader( $header ) {
    $header.css( 'width', $header.outerWidth() );
    $header.parent().css( 'padding-top', $header.outerHeight() );
    $header.addClass( 'fixed' );
  }

  /**
   * Unfixes the navigation bar header.
   *
   * @param $header header jQuery-element
   */
  function resetNavBarHeader( $header ) {
    $header.removeClass( 'fixed' );
    $header.css( 'width', '' );
    $header.parent().css( 'padding-top', '0' );
  }

  /**
   * Fixes the navigation bar.
   *
   * @param $navigation navigation bar jQuery-element
   */
  function fixNavBar( $navigation ) {
    $navigation.removeClass( 'trailing' );
    $navigation.css( 'width', $navigation.outerWidth() );
    $navigation.css( 'top', 0 );
    $navigation.addClass( 'fixed' );
  }

  /**
   * Prevents the navigation bar from scrolling out of view.
   *
   * @param $navigation navigation bar jQuery-element
   * @param marginBottom margin to keep to the lower window frame border
   */
  function preventNavBarScrolling( $navigation, marginBottom ) {
    $navigation.css( 'width', $navigation.outerWidth() );
    $navigation.css( 'top', '' );
    $navigation.css( 'bottom', marginBottom );
    $navigation.addClass( 'trailing' );
  }

  /**
   * Unfixes the navigation bar.
   *
   * @param $navigation navigation bar jQuery-element
   */
  function resetNavBar( $navigation ) {
    $navigation.removeClass( 'fixed' );
    $navigation.removeClass( 'trailing' );
    $navigation.css( 'width', '' );
    $navigation.css( 'bottom', '' );
  }

  /**
   * Resizes the navigation bar to fit its container.
   */
  function updateNavigationBarSize() {
    var isNavigationWidthSet = $navigation.hasClass( 'fixed' ) || $navigation.hasClass( 'trailing' );
    var isNavigationHeaderWidthSet = $navigationHeader.hasClass( 'fixed' );

    if ( isNavigationWidthSet ) {
      resetNavBar( $navigation );
    }
    if ( isNavigationHeaderWidthSet ) {
      resetNavBarHeader( $navigationHeader );
    }
    updateNavigationBar();
  }

}( mediaWiki, jQuery ) );
