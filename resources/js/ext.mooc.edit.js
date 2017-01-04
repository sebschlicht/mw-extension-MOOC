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
    $( '#units.section .header form.add .btn-submit' ).on('click', function() {
      var $btn = $(this);
      var $form = $btn.parent('form');
      var $value = $form.find('.value');
      // TODO validate
      var $name = $value.val();
      var title = mw.config.get('wgPageName') + '/' + $name;
      console.log('creating ' + title);

      var $api = new mw.Api();
      $api.edit(title, {
        action: 'edit',
        createonly: true,
        summary: 'Adding the lesson "' + $name + '".',
        text: '{"type":"unit"}',
        // TODO badformat why?
        //contentformat: 'application/json',
        // TODO currently not possible when logged out (or even non-admin?)
        contentmodel: 'mooc-item'
      }).then(function (json) {
        // TODO setup mw loggingg
        console.log(json);
      }).fail(function (code, result) {
        if (code === "http") {
          console.log("HTTP error: " + result.textStatus); // result.xhr contains the jqXHR object
          console.log(result);
        } else if (code === "ok-but-empty") {
          console.log("Got an empty response from the server");
        } else {
          console.log("API error: " + code);
          console.log(result);
        }
      });

      return false;
    });
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
