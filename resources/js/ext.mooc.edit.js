( function ( mw, $ ) {
  
  var extConfig = mw.config.get( 'moocAgentData' );
  var item = mw.config.get( 'moocItem' );
  
  // setup user agent for API requests
  $.ajaxSetup({
    beforeSend: function ( request ) {
      request.setRequestHeader( 'User-Agent', extConfig.userAgentName + '/' + extConfig.version + ' (' + extConfig.userAgentUrl + '; ' + extConfig.userAgentMailAddress + ')' );
    }
  });

  // fill modal boxes with item content
  fillModalBoxes( item );

  // register API calls when resources ready
  mw.loader.using( [ 'mediawiki.api.messages', 'mediawiki.jqueryMsg', 'mediawiki.api.edit'], registerApiCalls, function () {
    mw.log.error( 'Failed to load MediaWiki modules to initialize MOOC extension!' );
  } );

  /**
   * Registers the API calls with the corresponding UI elements.
   */
  function registerApiCalls() {
    new mw.Api().loadMessagesIfMissing( ['mooc-lesson-add-unit-summary'] ).then( function () {
      $( '#units' ).find( '.section .header form.add .btn-submit' ).on( 'click', addUnitToCurrentLesson );
    } );
  }

  /**
   * Adds the unit specified by the modal add unit box to the lesson represented by this page.
   *
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function addUnitToCurrentLesson() {
    var $form = $( this ).parent( 'form' );
    var unitName = $form.find( '.value' ).val();
    // TODO validate unit name

    return apiAddUnitToLesson( mw.config.get( 'wgPageName' ), unitName );
  }

  /**
   * Adds an unit to a lesson using the MediaWiki API in the background.
   *
   * @param lessonTitle title of the lesson
   * @param unitName name of the unit to be added
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function apiAddUnitToLesson( lessonTitle, unitName ) {
    var unitTitle = lessonTitle + '/' + unitName;
    mw.log( 'adding unit ' + unitName + ' (' + unitTitle + ') to lesson ' + lessonTitle );

    var $api = new mw.Api();
    $api.create( unitTitle, {
      summary: mw.message( 'mooc-lesson-add-unit-summary', unitName ).text(),
      text: '{"type":"unit"}',
      // TODO currently not possible when logged out (or even if logged-in as non-admin?)
      contentmodel: 'mooc-item'
    } ).then( function ( json ) {
      mw.log( 'The unit has been added successfully. Response:' );
      mw.log( json );
      //TODO force purge of cache once enabled
      reloadPage();
    } ).fail( function ( code, response ) {
      mw.log.warn( 'Failed to add the unit! Cause:' );
      mw.log.warn( response.error );
      mw.log( response );

      if ( code === "http" ) {
        mw.log.warn( "HTTP error: " + response.textStatus ); // result.xhr contains the jqXHR object
      }
      //TODO show the user that the process has failed!
    } );

    return false;
  }

  /**
   * Reloads the current page, triggering a new HTTP GET request.
   */
  function reloadPage() {
    window.location.reload( true );
  }
  
  /**
   * Fills the modal edit boxes with content from the currently displayed MOOC item.
   *
   * TODO: if possible, we should load the VisualEditor instead
   *
   * @param item MOOC item being currently displayed
   */
  function fillModalBoxes( item ) {
    var htmlListSeparator = '';
    function arrayToHtmlList( a ) {
      if ( a === undefined || a.length === 0 ) {
        return '';
      }
      return htmlListSeparator + a.join( '\n' + htmlListSeparator ) + '\n';
    }
    
    fillModalBox( 'learning-goals', arrayToHtmlList( item['learning-goals'] ) );
    fillModalBox( 'video', item.video );
    //TODO script
    //TODO quiz
    fillModalBox( 'further-reading', arrayToHtmlList( item['further-reading'] ) );
  }

  /**
   * Fills the modal edit box of a section with content.
   *
   * @param id section id
   * @param content content to be filled in the form element of the modal that holds the value
   */
  function fillModalBox( id, content ) {
    var $input = $( '#mooc' ).find( '#' + id ).find( '.section .header form.edit .value' );
    $input.val( content );

    // register resizing for textareas
    if ( $input.is( 'textarea.auto-grow ') ) {
        resizeTextarea( $input );
        $input.on( 'keyup', textareaValueChanged );
    }
  }

  /**
   * jQuery-callback to resize the textarea when its value has been changed.
   *
   * @param e keyup event
   */
  function textareaValueChanged( e ) {
    resizeTextarea( $( e.delegateTarget ) );
  }

  /**
   * Resizes a textare to match its content height.
   *
   * @param $textarea textarea jQuery-element
   */
  function resizeTextarea( $textarea ) {
    var numRows = $textarea.val().split( /\r*\n/ ).length;
    if ( $textarea.attr( 'rows' ) !== numRows ) {
      $textarea.attr( 'rows', numRows );
    }
  }
  
}( mediaWiki, jQuery ) );
