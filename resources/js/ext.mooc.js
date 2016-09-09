console.log('JS loading...');
(function (mw, $) {
  console.log('JS enabled!');
  $('.section .header .actions .btn-edit').on('click', openModalBox);
  
  function openModalBox(button) {
    return true;
  }
}(mediaWiki, jQuery));
