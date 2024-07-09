jQuery(document).ready(function ($) {
  window.deleteProductTransient = function () {
    if (confirm("Are you sure you want to delete the product transient?")) {
      $.ajax({
        url: deleteTransientVars.ajax_url,
        method: "POST",
        data: {
          action: "delete_product_transient",
          nonce: deleteTransientVars.nonce,
        },
        success: function (response) {
          if (response.success) {
            alert("Product transient deleted.");
          } else {
            alert("Failed to delete product transient.");
          }
        },
        error: function () {
          alert("An error occurred.");
        },
      });
    }
  };
});
