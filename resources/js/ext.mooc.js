( function ( mw, $ ) {
  
  var hCollapsedSection = 200;
  var $openedModalBox = null;
  
  // register UI event hooks
  var $sections = $( '#mooc-sections' ).find( '.section' );
  var $headers = $sections.find( '.header' );

  // hide action buttons
  $headers.find( '.actions' ).addClass( 'hidden-actions' );
  // open modal boxes via action buttons
  $headers.find( '.actions .btn-edit' ).on( 'click', openModalEditBox );
  $headers.find( '.actions .btn-add' ).on( 'click', openModalAddBox );
  // close modal boxes via background, close and cancel
  $headers.find( '.modal-bg' ).on( 'click', closeModalBoxes );
  $headers.find( '.modal-box .btn-close' ).on( 'click', closeModalBoxes );
  $headers.find( '.modal-box .btn-cancel' ).on( 'click', closeModalBoxes );

  // register hooks using jQuery easing when module loaded
  mw.loader.using( [ 'oojs-ui' ], function () {
    $sections.each( function ( index, element ) {
      var $section = $( element );

      // collapse sections and hide action buttons
      initSection( $section );
      hideActions( $section );

      // register links in empty sections
      $section.find( '.content .edit-link' ).on( 'click', openModalEditBox );
      $section.find( '.content .add-link' ).on( 'click', openModalAddBox );
    });
  }, function () {
    mw.log.error( 'Failed to load MediaWiki modules to initialize MOOC extension!' );
  } );

  // close modal box on key ESC down
  $( document ).keydown( function ( e ) {
    e = e || window.event;
    var isEscape = false;
    if ( 'key' in e ) {
        isEscape = ( e.key === 'Escape' );
    } else {
        isEscape = ( e.keyCode === 27 );
    }
    if ( isEscape ) {
      closeModalBoxes();
    }
  });

  // show/hide actions if mouse is inside/outside the respective section
  $sections
    .on( 'mouseenter', showActions )
    .on( 'mouseleave', hideActions );

  /**
   * Shows the action buttons of a section.
   */
  function showActions() {
    $( this ).children( '.header' ).children( '.actions' ).removeClass( 'hidden-actions' );
  }

  /**
   * Hides the action buttons of a section.
   */
  function hideActions() {
    $( this ).children( '.header' ).children( '.actions' ).addClass( 'hidden-actions' );
  }

  /*
   * ##########################
   * Open and close modal boxes
   * ##########################
   */
  //TODO do avoid intereference with MediaWiki interface we could move the modal to root in DOM when opening

  /**
   * Opens the modal edit box of a section when an element in the section has been clicked.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalEditBox() {
    return openModalBox( $( this ), 'edit' );
  }

  /**
   * Opens the modal add box of a section when an element in the section has been clicked.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalAddBox() {
    return openModalBox( $( this ), 'add' );
  }

  /**
   * Opens a modal box of a section.
   *
   * @param $element element in the section that has been clicked
   * @param action action the modal box should be opened for
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function openModalBox( $element, action ) {
    var $section = $element.parents( '.section' );
    // increase section z-index to bring modal in foreground
    $section.css( 'z-index', 2 );
    var $modal = $section.find( '.header .modal-wrapper' );
    if ( $modal.length > 1 ) {
      // filter by action if multiple modal boxes available
      $modal = $modal.filter( function( index, $el ) {
        return ( $el.find( 'form.' + action ).length > 1 );
      });
    }

    $modal.fadeIn( 200 );
    $openedModalBox = $modal;
    $modal.find( '.value' ).focus();
    return false;
  }

  /**
   * Closes the currently open modal box - if any.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function closeModalBoxes() {
    if ( $openedModalBox !== null ) {
        closeModalBox( $openedModalBox );
    }
    return false;
  }

  /**
   * Closes a modal box.
   *
   * @param $modal modal box
   */
  function closeModalBox( $modal ) {
    // reset section z-index
    var $section = $modal.parents( '.section' );
    $section.css( 'z-index', '' );
    $modal.find( '.value' ).blur();
    $modal.fadeOut( 200 );
  }

  /*
   * ############################
   * Collapse and expand sections
   * ############################
   */

  /**
   * Initializes a section:
   * If the section is collapsable the UI to do so is enabled.
   * If the section is collapsed by default it is being tried to collapse it immediately.
   *
   * @param $section section jQuery-element
   */
  function initSection( $section ) {
    // make section collapsable
    if ( isSectionCollapsable( $section ) ) {
      var $content = $section.children( '.content' );
      $content.css( 'max-height', $content.outerHeight() + 'px' );
      enableSectionCollapseUI( $section );
    }
    
    // collapse sections with .default-collapsed
    if ( $section.hasClass( 'default-collapsed' ) ) {
      // if collapsible
      if ( isSectionCollapsable( $section ) ) {
        collapseSection( $section );
      }
      $section.removeClass( 'default-collapsed' );
    }
  }

  /**
   * Checks if a section is collapsable.
   *
   * @param $section section jQuery-element
   * @returns {boolean} whether the section is collapsable or not
   */
  function isSectionCollapsable( $section ) {
    if ( $section.hasClass( 'collapsed' ) ) {
      return true;
    }
    var $content = $section.children( '.content' );
    
    // calculate effective content height
    // WARNING: this resets the ELEMENT:CSS max-height
    $content.css( 'max-height', 'none' );
    var height = $content.outerHeight();
    $content.css( 'max-height', '' );
    
    return ( height > hCollapsedSection );
  }

  /**
   * Collapses the section that contains the element that is being clicked on.
   *
   * jQuery-callback for click.
   */
  function collapseClickedSection() {
    var $section = $( this ).parents( '.section' );
    collapseSection( $section );
  }

  /**
   * Collapses a section to a fixed height - if this process would actually decrease the section height.
   * TODO pass height as variable from extension core via resource loader
   *
   * @param $section section jQuery-element
   */
  function collapseSection( $section ) {
    $section.addClass( 'collapsed' );
    
    var $content = $section.children( '.content' );
    $content.stop().css( 'max-height', hCollapsedSection + 'px' );

    var $expander = $content.children( '.expander' );
    $expander.addClass( 'active' );

    // enable UI to expand section
    enableSectionExpandUI( $section );
  }

  /**
   * Enables the UI to collapse a section.
   *
   * @param $section section jQuery-element
   */
  function enableSectionCollapseUI( $section ) {
    $section.removeClass( 'expandable' );
    $section.addClass( 'collapsable' );
    var $header = $section.children( '.header' );
    $header.off( 'click' ).on( 'click', collapseClickedSection );
    var $content = $section.children( '.content' );
    $content.off( 'click' );
  }

  /**
   * Expands the section that contains the element that is being clicked on.
   *
   * @param e click event
   */
  function expandClickedSection( e ) {
    var $hookTarget = $( this );
    var $target = $( e.target );
    // except if clicking at child element of header (e.g. modal box)
    if ( $hookTarget.is( '.header' ) && !$target.is ( '.header' ) ) {
      return;
    }
    var $section = $target.parents( '.section' );
    expandSection( $section );
  }

  /**
   * Expands a section to its full height.
   *
   * @param $section section jQuery-element
   */
  function expandSection( $section ) {
    $section.removeClass( 'collapsed' );
    
    // TODO make this fucking work with CSS3 transitions
    var $content = $section.children( '.content' );
    var maxHeight = $content.css( 'max-height' );
    $content.css( 'max-height', 'none' );
    var contentHeight = $content.outerHeight();
    $content.css( 'max-height', maxHeight );
    $content.stop().animate( {
      'max-height': contentHeight + 'px'
    }, 300, 'easeInQuint' );
    
    var $expander = $content.children( '.expander' );
    $expander.removeClass( 'active' );
    
    enableSectionCollapseUI( $section );
  }

  /**
   * Enables the UI to expand a section.
   *
   * @param $section section jQuery-element
   */
  function enableSectionExpandUI( $section ) {
    $section.removeClass( 'collapsable' );
    $section.addClass( 'expandable' );
    var $header = $section.children( '.header' );
    $header.off( 'click' ).on( 'click', expandClickedSection );
    var $content = $section.children( '.content' );
    $content.on( 'click', expandClickedSection );
  }
  
  // hide MW UI
  var $mwContent = $( '#content.mw-body' );
  var contentMarginLeft = $mwContent.css( 'margin-left' );
  var $mwNavigation = $( '#mw-navigation' );
  var $mwPageBase = $( '#mw-page-base' );
  
  var $mwNavButton = $( '<img>' ).addClass( 'btn-toggle-mw-nav' );
  $mwNavButton.attr( 'src', '/mediawiki-vagrant.png' );
  $mwNavButton.on( 'click', mwNavigationButtonClicked );
  //$mwNavButton.insertBefore( $mwNavigation );
  // hideMwNavigation( $mwNavigation );
  
  function mwNavigationButtonClicked() {
    if ( $mwNavigation.hasClass( 'hidden' ) ) {
      showMwNavigation( $mwNavigation );
    } else {
      hideMwNavigation( $mwNavigation );
    }
  }
  function hideMwNavigation( $mwNavigation ) {
    $mwContent.stop().animate( {
      'margin-left': 0
    } );
    $mwPageBase.slideUp();
    $mwNavigation.addClass( 'hidden' );
  }
  function showMwNavigation( $mwNavigation ) {
    $mwContent.stop().animate( {
      'margin-left': contentMarginLeft
    } );
    $mwPageBase.slideDown();
    $mwNavigation.removeClass( 'hidden' );
  }
  
}( mediaWiki, jQuery ) );
