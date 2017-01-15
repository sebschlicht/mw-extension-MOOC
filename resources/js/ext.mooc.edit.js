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
      $( '#units' ).find( '.header form.add .btn-submit' ).on( 'click', addUnitToCurrentLesson );
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
    // learning goals
    fillModalBox( 'learning-goals', item );
    // video
    fillModalBox( 'video', item );
    //TODO script
    //TODO quiz
    // further reading
    fillModalBox( 'further-reading', item );
  }

  /**
   * Fills the modal edit box of a section with content.
   *
   * @param section section id
   * @param item item holding the content
   */
  function fillModalBox( section, item ) {
    var $form = $( '#mooc' ).find( '#' + section ).find( '.header form.edit' );
    switch ( section ) {
      case 'learning-goals':
      case 'further-reading':
        buildHtmlList( $form.find( 'ol.value' ), item[section] );
        break;

      default:
        var $input = $form.find( '.value' );
        $input.val( item[section] );

        // register resizing for textareas
        if ( $input.is( 'textarea.auto-grow ') ) {
          resizeTextarea( $input );
          $input.on( 'keyup', textareaValueChanged );
        }
        break;
    }
  }

  /**
   * Transforms a list of values into HTML list items with input fields containing the values.
   * These list items are injected into the specified HTML list, after its previous items have been removed.
   * One list items will be added to the list at least - even if the specified value list is empty.
   *
   * @param $list list jQuery-element
   * @param items items to be filled in the list item input fields
   */
  function buildHtmlList( $list, items ) {
    $list.empty();
    if ( items.length > 0 ) {
      for ( var i = 0; i < items.length; i++ ) {
        addListItem( $list, items[i] );
      }
    } else {
      addListItem( $list, '' );
    }
  }

  /**
   * Adds an item to a list.
   * If there is a previous item specified, it will be inserted hereafter.
   * Otherwise it will be added to the end of the list.
   *
   * @param $list list to add the item to
   * @param value input field value
   * @param $prev (optional) previous list item
   * @returns {*} input field jQuery-element
   */
  function addListItem( $list, value, $prev ) {
    var $input = $( '<input>', {
      'class': 'form-control value',
      'type': 'text',
      'value': value
    } );
    $input.on( 'keydown', onListItemInputKeyDown);

    var $listItem = $( '<li>' ).append($input);
    if ( $prev === undefined ) {
      $list.append($listItem);
    } else {
      $prev.after($listItem);
    }
    $input.focus();

    return $input;
  }

  /**
   * Removes a list item if there still are other items to work with.
   *
   * @param $listItem list item jQuery-element
   * @param focusNextItem whether to focus the next item instead of the previous one
   * @returns {boolean} whether the list item has been removed or not
   */
  function removeListItem( $listItem, focusNextItem ) {
    // remove only if other list items available
    if ( $listItem.siblings().length > 0 ) {
      // select list item to be focused next
      // TODO set cursor position to start/end
      var $nextListItem = $listItem.prev();
      focusNextItem = focusNextItem || ( $nextListItem.length === 0 );
      if ( focusNextItem ) {
        $nextListItem = $listItem.next();
      }

      // set focus to input field of next list item
      var $nextInput = $nextListItem.find( 'input.value' );
      $nextInput.focus();
      if ( focusNextItem ) {
        // move selection to start
        $nextInput[0].setSelectionRange(0, 0);
      }

      $listItem.remove();
      return true;
    }
    return false;
  }

  /**
   * jQuery-callback to insert/remove list items when a key is pressed down in a list item input.
   *
   * @param e keydown event
   * @returns {boolean} whether to further delegate the event or not
   */
  function onListItemInputKeyDown ( e ) {
    var $input = null;
    switch ( e.which ) {
      // Backspace
      case 8:
        $input = $( this );
        if ( $input.val().length === 0 ) {
          removeListItem( getListItem( $input ), false );
          e.preventDefault();
          return false;
        }
        break;

      // Enter
      case 13:
        var $listItem = getListItem( $( this ) );
        addListItem( $listItem.parent(), '', $listItem);
        e.preventDefault();
        return false;

      // Delete
      case 46:
        $input = $( this );
        if ( $input.val().length === 0 ) {
          removeListItem( getListItem( $input ), true );
          e.preventDefault();
          return false;
        }
        break;
    }
  }

  /**
   * Retrieves the parent list item containing the input field specified.
   *
   * @param $input input field
   * @returns {*} list item jQuery-element
   */
  function getListItem( $input ) {
    return $input.parent( 'li' );
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
