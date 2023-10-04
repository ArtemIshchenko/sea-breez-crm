'use strict';

(function() {
  //yii.validation bootstrap 4 fix
  $('.active-form').on('afterValidateAttribute', function (event, attribute, messages) {
    console.log('validated')
    if ($(attribute.container).hasClass('is-invalid')) {
      $('#'+attribute.id).addClass('is-invalid');
    }
    else {
      $('#'+attribute.id).removeClass('is-invalid');
    }
  });
  if ($('.active-form').length) {
    $('.active-form').find('.form-group.is-invalid').each(function() {
      $(this).find('.form-control').addClass('is-invalid');
    });
  }

})();
