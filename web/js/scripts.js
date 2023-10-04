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

  // $('.register-btn').attr('disabled', 'disabled');
  // const modalConditions = new bootstrap.Modal(document.querySelector('#conditions-modal'));
  // $('#registerform-accept_conditions').on('click', function () {
  //   if ($(this)[0].checked) {
  //     $('.register-btn').removeAttr('disabled');
  //     modalConditions.show();
  //   } else {
  //     $('.register-btn').attr('disabled', 'disabled');
  //   }
  // });
  // $('#conditions-modal .close, #conditions-modal .ok-btn').on('click', function () {
  //   modalConditions.hide();
  // });
})();

//# sourceMappingURL=scripts.js.map