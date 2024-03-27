(function ($, Drupal) {
  Drupal.behaviors.enrollButtonClick = {
    attach: function (context, settings) {
      $('.button-link', context).once('enrollButtonClick').on('click', function () {
        var nodeId = $(this).data('node-id');
        // Send an AJAX request to update the field_total_count value.
        $.ajax({
          url: '/enroll-click-counter/' + nodeId,
          type: 'POST',
          success: function (response) {
            // Handle success response if needed.
            console.log('Enroll button clicked for node ID: ' + nodeId);
          },
          error: function (jqXHR, textStatus, errorThrown) {
            // Handle error if needed.
            console.error('Error while updating enroll count: ' + errorThrown);
          }
        });
      });
    }
  };
})(jQuery, Drupal);

