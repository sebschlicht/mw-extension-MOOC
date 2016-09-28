( function ( mw, $ ) {
  
  var extConfig = mw.config.get( 'wgMOOC' );
  
  // setup user agent for API requests
  $.ajaxSetup({
    beforeSend: function(request) {
      request.setRequestHeader( 'User-Agent', extConfig.userAgentName + '/' + extConfig.version + ' (' + extConfig.userAgentUrl + '; ' + extConfig.userAgentMailAddress + ')' );
    }
  });
  
  mw.loader.using( 'mediawiki.api', loadItem, function() {
    console.error( 'failed to load mw.Api' );
  } );
  
  var item = {};
  
  function loadItem() {
    var $title = mw.config.get( 'wgPageName' );
    loadPageContent($title).then( function( content ) {
      if (item !== null) {
        item = JSON.parse( content );
        mw.log( 'item loaded:' );
        mw.log( item );
        fillModalBoxes( item );
      }  
    } );
  }
  
  // TODO: if possible, we should load the VisualEditor instead
  function fillModalBoxes( item ) {
    var htmlListSeparator = '# ';
    function arrayToHtmlList( a ) {
      if (a.length === 0) {
        return '';
      }
      return htmlListSeparator + a.join( '\n' + htmlListSeparator ) + '\n';
    }
    
    fillModalBox( 'learning-goals', arrayToHtmlList( item['learning-goals'] ) );
    fillModalBox( 'video', item.video );
    fillModalBox( 'further-reading', arrayToHtmlList( item['further-reading'] ) );
  }
  
  function fillModalBox( id, content ) {
    var modalBox = $('#mooc #' + id + '.section .header .edit-form .value');
    modalBox.val(content);
  }
  
  function loadPageContent($title) {
    mw.log( 'loading content of page "' + $title + '" ...' );
    var api = new mw.Api();
    return api.get( {
      action: 'query',
      format: 'json',
      titles: $title,
      prop: 'revisions',
      rvprop: 'content'
    } ).then( function( json ) {
      mw.log( json );
      var page = getFirstPage( json.query.pages );
      if (page !== null) {
        var rev = page.revisions[0];
        var content = rev['*'];
        mw.log( 'page content loaded: "' + content + '"' );
        return content;
      } else {
        return null;
      }
    } ).fail( function( errorCode ) {
      console.error( 'failed with error code: ' + errorCode );
    } );
  }
  
  function getFirstPage( pages ) {
    var firstId;
    var numIds = 0;
    for (var id in pages) {
      if (numIds === 0) {
        firstId = id;
      } else {
        return null;
      }
      numIds += 1;
    }
    return pages[firstId];
  }
  
}( mediaWiki, jQuery ) );
