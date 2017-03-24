( function ( mw, $ ) {

  // vertical spacing for scrolling into sections
  var spacingScrollV = 0;
  // scroll animation duration in ms
  var tScroll = 600;
  // vertical offset of the item navigation bar to the upper screen border
  var marginTop = 0;

  // register UI event hooks
  var $window = $( window );
  var $itemNavigationWrapper = $( '#mooc' ).find( '#itemnav-wrapper' );
  var $itemNavigation = $itemNavigationWrapper.find( '#itemnav' );
  $itemNavigation.find( 'a' ).on( 'click', onItemNavigationLinkClick );
  $window.scroll( updateItemNavigation );
  $window.resize( resizeItemNavigation );
  mw.log( 'Item Navigation activated.' );

  /**
   * Updates the item navigation when the user scrolls.
   */
  function updateItemNavigation() {
    var y = $window.scrollTop();
    var barTop = $itemNavigationWrapper.offset().top;
    var isFixed = $itemNavigation.hasClass( 'fixed' );

    if ( y + marginTop > barTop ) {
      if ( !isFixed ) {
        fixItemNavigation( $itemNavigation, marginTop, $itemNavigationWrapper.outerWidth() );
      }
    } else {
      if ( isFixed ) {
        resetItemNavigation( $itemNavigation );
      }
    }
  }

  /**
   * Fixes the item navigation on the upper screen border.
   *
   * @param $itemNavigation item navigation jQuery-element
   * @param marginTop top margin
   * @param width section width to adopt
   */
  function fixItemNavigation( $itemNavigation, marginTop, width ) {
    $itemNavigation.css( 'top', marginTop );
    $itemNavigation.css( 'width', width );
    $itemNavigation.addClass( 'fixed' );

    // insert dummy element to make sticky item navigation compatible with sticky headers
    $itemNavigation.after( $( '<div>', {
      'id': 'qn-replace',
      'height': $itemNavigation.outerHeight()
    }));
  }

  /**
   * Resets the item navigation to its default position above the MOOC sections.
   *
   * @param $itemNavigation item navigation jQuery-element
   */
  function resetItemNavigation( $itemNavigation ) {
    $itemNavigation.next( '#qn-replace' ).remove();
    $itemNavigation.css( 'top', '' );
    $itemNavigation.css( 'width', '' );
    $itemNavigation.removeClass( 'fixed' );
  }

  /**
   * Resizes the item navigation to fit its container.
   */
  function resizeItemNavigation() {
    var itemNavigationFixed = $itemNavigation.hasClass( 'fixed' );
    if ( itemNavigationFixed ) {
      resetItemNavigation( $itemNavigation );
    }
    updateItemNavigation();
  }

  /**
   * Scrolls a section into the current viewport.
   *
   * @param sectionId section identifier
   */
  function scrollIntoSection( sectionId ) {
    var $section = $( '#' + sectionId );
    if ( $section.length === 1 ) {
      // scroll section into view
      var offset = $section.offset();
      // 1px to be inside the elementg
      offset.top -= spacingScrollV + $itemNavigation.outerHeight() - 1;
      offset.left = 0;
      $( 'html, body' ).animate({
        scrollTop: offset.top,
        scrollLeft: offset.left
      }, tScroll);
    }
  }

  /**
   * Scrolls the section into view that the clicked item navigation link is associated to.
   *
   * @returns {boolean} whether the mouse event should be delegated
   */
  function onItemNavigationLinkClick( ) {
    var $item = $( this );
    scrollIntoSection( $item.attr( 'data-section' ) );
    return false;
  }

}( mediaWiki, jQuery ) );
