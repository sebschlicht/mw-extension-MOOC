( function ( mw, $ ) {
  
  // register UI event hooks
  $('#mooc-sections .section .header .actions .btn-edit').on('click', openModalBox);
  $('#mooc-sections .section .header .modal-box-bg').on('click', closeModalBoxes);
  $('#mooc-sections .section .header .modal-box button.close').on('click', closeModalBoxes);
  $('#mooc-sections .section .header .modal-box button.btn-cancel').on('click', closeModalBoxes);
  $('#mooc-sections .section').each(function(index, element) {
    initSection($(element));
  });
  
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
    $modal.fadeIn(200);
    $modal.find('textarea').focus();
    return false;
  }
  function closeModalBoxes() {
    var $visibleModalBoxes = $('#mooc-sections .modal-box-wrapper:visible');
    closeModalBox($visibleModalBoxes);
  }
  function closeModalBox($modal) {
    $modal.find('textarea').blur();
    $modal.fadeOut(200);
  }
  
  function initSection($section) {
    // TODO make large sections collapsable
    // collapse sections with .default-collapsed
    if ($section.hasClass('default-collapsed')) {
      collapseSection($section);
    }
  }
  function collapseSection($section) {
    var $content = $section.children('.content');
    
    // calculate effective content height
    var maxHeight = $content.css('max-height');
    $content.css('max-height', 'none');
    var height = $content.outerHeight();
    $content.css('max-height', maxHeight);
    
    console.log(height);
    if (height > 180) {
      $section.addClass('collapsed');
    }
  }
  function showActions() {
    $(this).children('.header').children('.actions').fadeIn();
  }
  function hideActions() {
    $(this).children('.header').children('.actions').fadeOut();
  }
  
}( mediaWiki, jQuery ) );
