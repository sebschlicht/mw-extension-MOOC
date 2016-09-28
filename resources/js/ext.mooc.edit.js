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
      item = JSON.parse( content );
      mw.log( 'item loaded:' );
      mw.log( item );
    } );
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
      var pageId = Object.keys(json.query.pages)[0];
      var rev = json.query.pages[pageId].revisions[0];
      var content = rev['*'];
      mw.log( 'page content loaded: "' + content + '"' );
      return content;
    } ).fail( function( errorCode ) {
      console.error( 'failed with error code: ' + errorCode );
    } );
  }
  
}( mediaWiki, jQuery ) );
