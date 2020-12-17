(function ($, Drupal) {
  Drupal.behaviors.checkboxes_all = {
    attach: function (context, settings) {
      $('input[data-action="checkboxes_all"]', context).each(function () {
        $(this).on('change', function() {
          if($(this).val() && !this.checked) {
            $('input[data-action="checkboxes_all"]', context).eq(0).prop('checked', false);
          }
          if(!$(this).val()) {
            if(this.checked) {
              $('input[data-action="checkboxes_all"]:not(:checked)', context).prop('checked', true);
            } else {
              $('input[data-action="checkboxes_all"]:checked', context).prop('checked', false);
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal);
