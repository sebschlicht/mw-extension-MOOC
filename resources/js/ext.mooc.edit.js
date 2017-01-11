( function ( mw, $ ) {
  
  var extConfig = mw.config.get( 'moocAgentData' );
  var item = mw.config.get( 'moocItem' );
  
  // setup user agent for API requests
  $.ajaxSetup({
    beforeSend: function(request) {
      request.setRequestHeader( 'User-Agent', extConfig.userAgentName + '/' + extConfig.version + ' (' + extConfig.userAgentUrl + '; ' + extConfig.userAgentMailAddress + ')' );
    }
  });

  // fill modal boxes with item content
  fillModalBoxes( item );
  
  mw.loader.using( 'mediawiki.api.edit', registerApiCalls, function() {
    console.error( 'failed to load mw.Api' );
  } );

  function registerApiCalls() {
    $( '#units.section .header form.add .btn-submit' ).on('click', addUnitToCurrentLesson);
  }

  function addUnitToCurrentLesson() {
    var $form = $(this).parent('form');
    var unitName = $form.find('.value').val();
    // TODO validate unit name

    return apiAddUnitToLesson(mw.config.get('wgPageName'), unitName);
  }

  function apiAddUnitToLesson(lessonTitle, unitName) {
    var unitTitle = lessonTitle + '/' + unitName;
    mw.log('adding unit ' + unitName + ' (' + unitTitle + ') to lesson ' + lessonTitle);

    //TODO summary localization
    var $api = new mw.Api();
    $api.create(unitTitle, {
      summary: 'Adding the unit "' + unitName + '".',
      text: '{"type":"unit"}',
      // TODO badformat why?
      //contentformat: 'application/json',
      // TODO currently not possible when logged out (or even non-admin?)
      contentmodel: 'mooc-item'
    }).then(function (json) {
      mw.log('The unit has been added successfully. Response:');
      mw.log(json);
    }).fail(function (code, result) {
      mw.log.warn('Failed to add the unit! Cause:');
      if (code === "http") {
        mw.log.warn("HTTP error: " + result.textStatus); // result.xhr contains the jqXHR object
      } else {
        mw.log.warn("API error: " + code);
      }
      mw.log.warn(result);
    });

    return false;
  }
  
  // TODO: if possible, we should load the VisualEditor instead
  function fillModalBoxes( item ) {
    var htmlListSeparator = '';
    function arrayToHtmlList( a ) {
      if (a === undefined || a.length === 0) {
        return '';
      }
      return htmlListSeparator + a.join( '\n' + htmlListSeparator ) + '\n';
    }
    
    fillModalBox( 'learning-goals', arrayToHtmlList( item['learning-goals'] ) );
    fillModalBox( 'video', item.video );
    fillModalBox( 'further-reading', arrayToHtmlList( item['further-reading'] ) );
  }
  
  function fillModalBox( id, content ) {
    var $input = $('#mooc #' + id + '.section .header form.edit .value');
    $input.val(content);

    // register resizing for textareas
    if ($input.is('textarea.auto-grow')) {
        resizeTextarea($input);
        $input.on('keyup', textareaValueChanged);
    }
  }

  function textareaValueChanged(e) {
    resizeTextarea($(e.delegateTarget));
  }

  function resizeTextarea($textarea) {
    var numRows = $textarea.val().split(/\r*\n/).length;
    if ($textarea.attr('rows') !== numRows) {
      $textarea.attr('rows', numRows);
    }
  }
  
}( mediaWiki, jQuery ) );
