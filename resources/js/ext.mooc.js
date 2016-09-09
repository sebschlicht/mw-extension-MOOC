( function ( mw, $ ) {
  
  // register UI event hooks
  $('#mooc-sections .section .header .actions .btn-edit').on('click', openModalBox);
  
  $('#mooc-sections .section .header .actions').hide();
  $('#mooc-sections .section')
    .on('mouseenter', toggleActions)
    .on('mouseleave', toggleActions);
  
  function openModalBox() {
    return false;
  }
  
  function toggleActions() {
    $(this).children('.header').children('.actions').fadeToggle();
  }
  
}( mediaWiki, jQuery ) );
