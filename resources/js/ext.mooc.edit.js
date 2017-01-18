( function ( mw, $ ) {
  
  var extConfig = mw.config.get( 'moocAgentData' );
  var item = mw.config.get( 'moocItem' );
  mw.log( 'MOOC item being rendered:' );
  mw.log( item );
  
  // setup user agent for API requests
  $.ajaxSetup({
    beforeSend: function ( request ) {
      request.setRequestHeader( 'User-Agent', extConfig.userAgentName + '/' + extConfig.version + ' (' + extConfig.userAgentUrl + '; ' + extConfig.userAgentMailAddress + ')' );
    }
  });

  // register API calls when resources ready
  mw.loader.using( [ 'mediawiki.api.messages', 'mediawiki.jqueryMsg', 'mediawiki.api.edit'], registerApiCalls, function () {
    mw.log.error( 'Failed to load MediaWiki modules to initialize MOOC extension!' );
  } );

  /**
   * Registers the API calls with the corresponding UI elements.
   */
  function registerApiCalls() {
    new mw.Api().loadMessagesIfMissing( ['mooc-lesson-add-unit-summary'] ).then( function () {
      // initialize modal edit boxes
      initModalEditBoxes( item );
      // initialize modal add box
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

    // TODO show loading indicator
    // create unit
    apiAddUnitToLesson( mw.config.get( 'wgPageName' ), unitName ).then( function () {
      // reload page on success
      reloadPage();
    } );
    return false;
  }

  /**
   * Gets the raw content of a page.
   *
   * @param title page title
   * @returns {*} jQuery-promise on the AJAX request
   */
  function apiGetRawPage( title ) {
    mw.log( 'loading raw content of page ' + title );
    return new mw.Api().get( {
      'action': 'query',
      'prop': 'revisions',
      'rvprop': 'content',
      'titles': title
    } ).then( function ( json ) {
      mw.log( 'The page has been retrieved successfully. Response:' );
      mw.log( json );
      return getFirstPageRevisionContent( json );
    } ).fail( function ( code, response ) {
      mw.log.warn( 'Failed to save the item! Cause:' );
      mw.log.warn( response.error );
      mw.log( response );

      if ( code === "http" ) {
        mw.log.warn( "HTTP error: " + response.textStatus ); // result.xhr contains the jqXHR object
      }
      //TODO show the user that the process has failed!
    } );
  }

  /**
   * Extracts the content of the first revision of the first page in a revisions query response.
   *
   * @param json revisions query response
   * @returns {string|null} content of the first revision of the first page in the revisions query response
   */
  function getFirstPageRevisionContent( json ) {
    if ( 'query' in json && 'pages' in json.query ) {
      var pages = json.query.pages;
      if ( pages.length > 0 ) {
        return pages[0].revisions[0]['*'];
      }
    }
    return null;
  }

  /**
   * Saves a Wikipage.
   *
   * @param title page title
   * @param content page content
   * @param summary edit summary
   * @returns {*} jQuery-promise on the AJAX request
   */
  function apiSavePage( title, content, summary ) {
    mw.log( 'saving page ' + title );
    return new mw.Api().edit( title, function () {
      return {
        'summary': summary,
        'text': content
      };
    } ).then( function( json ) {
      mw.log( 'The page has been saved successfully. Response:' );
      mw.log( json );
    } ).fail( function ( code, response ) {
      mw.log.warn( 'Failed to save the page! Cause:' );
      mw.log.warn( response.error );
      mw.log( response );

      if ( code === "http" ) {
        mw.log.warn( "HTTP error: " + response.textStatus ); // result.xhr contains the jqXHR object
      }
      //TODO show the user that the process has failed!
    } );
  }

  /**
   * Saves an item.
   *
   * @param title item title
   * @param item item
   * @param summary edit summary
   * @returns {*} jQuery-promise on the AJAX request
   */
  function apiSaveItem( title, item, summary ) {
    return apiSavePage( title, JSON.stringify( item ), summary );
  }

  /**
   * Adds an unit to a lesson using the MediaWiki API in the background.
   *
   * @param lessonTitle title of the lesson
   * @param unitName name of the unit to be added
   * @returns {*} jQuery-promise on the AJAX request
   */
  function apiAddUnitToLesson( lessonTitle, unitName ) {
    var unitTitle = lessonTitle + '/' + unitName;
    mw.log( 'adding unit ' + unitName + ' (' + unitTitle + ') to lesson ' + lessonTitle );

    return new mw.Api().create( unitTitle, {
      'summary': mw.message( 'mooc-lesson-add-unit-summary', unitName ).text(),
      'text': '{"type":"unit"}',
      // TODO currently not possible when logged out (or even if logged-in as non-admin?)
      'contentmodel': 'mooc-item'
    } ).then( function ( json ) {
      mw.log( 'The unit has been added successfully. Response:' );
      mw.log( json );
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
  }

  /**
   * Reloads the current page, triggering a new HTTP GET request.
   * TODO force purge of cache once enabled
   */
  function reloadPage() {
    window.location.reload( true );
  }
  
  /**
   * Initializes the modal edit box that allow users to edit the item being currently displayed.
   * TODO: if possible, we should load the VisualEditor instead
   *
   * @param item MOOC item being currently displayed
   */
  function initModalEditBoxes( item ) {
    // learning goals
    initModalEditBox( 'learning-goals', item );
    // video
    initModalEditBox( 'video', item );
    // script
    initModalEditBox( 'script', item );
    // quiz
    initModalEditBox( 'quiz', item );
    // further reading
    initModalEditBox( 'further-reading', item );
  }

  /**
   * Initializes a modal edit box.
   *
   * @param section section id
   * @param item item holding the content
   */
  function initModalEditBox( section, item ) {
    var $form = $( '#mooc' ).find( '#' + section ).find( '.header form.edit' );
    fillModalBoxForm( $form, section, item );
    var $btnSave = $form.find( '.btn-save' );
    $btnSave.on( 'click', onSaveItem );
    var $btnReset = $form.find( '.btn-reset' );
    $btnReset.on( 'click', onResetModal );
  }

  /**
   * Fills the form fields of a modal edit box with the content from an item's section.
   *
   * @param $form edit form jQuery-element
   * @param section section id
   * @param item item holding the content
   */
  function fillModalBoxForm( $form, section, item ) {
    switch ( section ) {
      case 'learning-goals':
      case 'further-reading':
        // fill ordered list with section list items
        buildHtmlList( $form.find( 'ol.value' ), item[section] );
        break;

      case 'script':
      case 'quiz':
        // enable textarea to grow automatically and inject remote page content
        var $textarea = $form.find( 'textarea.value' );
        if ( item[section] === undefined ) {
          $textarea.on( 'input', onTextareaValueChanged );
          var $btnSave = $form.find( '.btn-save' );
          $btnSave.prop( 'disabled', true );
          // download remote page content
          var title = mw.config.get( 'wgPageName' ) + '/' + section;
          apiGetRawPage( title ).then( function ( content ) {
            item[section] = content;
            $textarea.val( content );
            resizeTextarea( $textarea );
            $btnSave.prop( 'disabled', false );
          } );
        } else {
          $textarea.val( item[section] );
          resizeTextarea( $textarea );
        }
        break;

      default:
        // inject section content into input field
        $form.find( 'input.value' ).val( item[section] );
        break;
    }
  }

  /**
   * jQuery-callback to apply the changes made via the modal edit box to the item and save these changes into the item page.
   *
   * @param e click event
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function onSaveItem( e ) {
    var $btnSave = $( e.delegateTarget );
    var $form = $btnSave.parents( 'form' );
    var section = $form.parents( '.section' ).attr( 'id' );

    // apply changes
    var saveItem = false;
    var editSummary =  mw.message( 'mooc-section-' + section + '-save-summary' ).text();
    switch ( section ) {
      case 'video':
        item[section] = $form.find( '.value' ).val();
        saveItem = true;
        break;

      case 'learning-goals':
      case 'further-reading':
        item[section] = buildList( $form.find( 'ol.value' ) );
        saveItem = true;
        break;

      case 'script':
      case 'quiz':
        item[section] = $form.find( '.value' ).val();
        // TODO show loading indicator
        apiSavePage( item[section + 'Title'], item[section], editSummary ).then( function ( ) {
          // reload page on success
          reloadPage();
        } );
        break;
    }

    if ( saveItem ) {
      // TODO show loading indicator
      // save item
      apiSaveItem( mw.config.get( 'wgPageName' ), item, editSummary ).then( function ( ) {
        // reload page on success
        reloadPage();
      } );
    }
    return false;
  }

  /**
   * jQuery-callback to reset the modal box to show the item content when the reset button is clicked.
   *
   * @param e click event
   * @returns {boolean} whether the mouse event should be delegated or not
   */
  function onResetModal( e ) {
    var $btnReset = $( e.delegateTarget );
    var $form = $btnReset.parents( 'form' );
    var section = $form.parents( '.section' ).attr( 'id' );

    // load value from item
    fillModalBoxForm( $form, section, item );

    return true;
  }

  /**
   * Transforms a HTML list into a list of values.
   * The values are extracted from the input fields in each HTML list item.
   * Empty values will be dropped.
   *
   * @param $list HTML list jQuery-element
   * @returns {Array} list of values in the HTML list item's input fields
   */
  function buildList( $list ) {
    var list = [];
    $list.children().each( function ( i, e ) {
      var $item = $( e );
      var value = $item.find( '.value' ).val().trim();
      if ( value.length > 0 ) {
        list.push( value );
      }
    } );
    return list;
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
    if ( items !== undefined && items.length > 0 ) {
      for ( var i = 0; i < items.length; i++ ) {
        addListItem( $list, items[i] );
      }
    } else {
      addListItem( $list, '' );
    }
    // TODO focus last input field and set cursor to end on showModal
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
        $nextInput[0].setSelectionRange( 0, 0 );
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
   * @param e input event
   */
  function onTextareaValueChanged( e ) {
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
