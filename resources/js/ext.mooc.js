( function ( mw, $ ) {
  
  // register UI event hooks
  $('#mooc-sections .section .header .actions .btn-edit').on('click', openModalBox);
  $('#mooc-sections .section .header .modal-box-bg').on('click', closeModalBoxes);
  
  $(document).keydown(function(e){
    e = e || window.event;
    var isEscape = false;
    if ("key" in e) {
        isEscape = (e.key === "Escape");
    } else {
        isEscape = (e.keyCode === 27);
    }
    if (isEscape) {
      closeModalBoxes();
    }
  });
  
  $('#mooc-sections .section')
    .on('mouseenter', showActions)
    .on('mouseleave', hideActions);
  $('#mooc-sections .section .header .actions').hide();
  
  // TODO load modal box content
  
  function openModalBox() {
    var $modal = $(this).parent().siblings('.modal-box-wrapper');
    $modal.fadeIn();
    $modal.find('textarea').focus();
    return false;
  }
  function closeModalBoxes() {
    var $visibleModalBoxes = $('#mooc-sections .modal-box-wrapper:visible');
    closeModalBox($visibleModalBoxes);
  }
  function closeModalBox($modal) {
    $modal.find('textarea').blur();
    $modal.fadeOut();
  }
  
  function showActions() {
    $(this).children('.header').children('.actions').fadeIn();
  }
  function hideActions() {
    $(this).children('.header').children('.actions').fadeOut();
  }
  
}( mediaWiki, jQuery ) );
