jQuery(document).ready(function ($) {
  // Handle form submission
  $("#order-group-form").on("submit", function (e) {
    e.preventDefault();

    let formData = {
      action: "create_order_group",
      post_title: $("#post_title").val(),
      post_id: $("#order-group-form").data("post-id"),
      order_group_products_main: $("#order_group_products_main").val(),
      order_group_products_sites: $("#order_group_products_sites").val(),
      order_group_products_flash: $("#order_group_products_flash").val(),
      nonce: alarnd_order_group_vars.nonce,
    };

    $.ajax({
      type: "POST",
      url: alarnd_order_group_vars.ajax_url,
      data: formData,
      success: function (response) {
        if (response.success) {
        //   window.location.href = response.data.redirect_url;
            location.reload();
        } else {
          alert("Failed to create Order Group: " + response.data.message);
        }
      },
      error: function (xhr, status, error) {
        alert("An error occurred: " + error);
      },
    });
  });

  // Function to format the options with thumbnails
  function formatProduct(product) {
    if (!product.id) {
      return product.text;
    }
    var $product = $(
      '<span><img src="' +
        $(product.element).data("thumbnail") +
        '" class="grouping-product-thumbnail" /> ' +
        product.text +
        "</span>"
    );
    return $product;
  }

  // Initialize Select2 with custom templates
  $(
    "#order_group_products_main, #order_group_products_sites, #order_group_products_flash"
  ).select2({
    placeholder: "Select products",
    allowClear: true,
    templateResult: formatProduct,
    templateSelection: formatProduct,
    dropdownCssClass: "order-group-select2-dropdown",
  });


    $(".save_order_group_text").on("click", function (e) {
        e.preventDefault();

        let $button = $(this);
        let orderId = $button.data("order-id");
        let $textarea = $button.siblings(".orderGroup_textInput");
        let textValue = $textarea.val();

        // Add loading state to the button
        $button.prop("disabled", true).text("Saving...");

        $.ajax({
        type: "POST",
        url: alarnd_order_group_vars.ajax_url,
        data: {
            action: "save_order_group_text",
            order_id: orderId,
            text_value: textValue,
            nonce: alarnd_order_group_vars.nonce,
        },
        success: function (response) {
            if (response.success) {
            $button.text("Saved!").removeClass("error");
            Toastify({
              text: `#${orderId} note saved successfully!`,
              duration: 3000,
              close: true,
              gravity: "bottom", // `top` or `bottom`
              position: "right", // `left`, `center` or `right`
              stopOnFocus: true, // Prevents dismissing of toast on hover
              style: {
                background: "linear-gradient(to right, #00b09b, #96c93d)",
              },
            }).showToast();

            } else {
            alert("Failed to save text: " + response.data.message);
            $button.addClass("error");
            }
        },
        error: function (xhr, status, error) {
            alert("An error occurred: " + error);
        },
        complete: function () {
            // Reset the button state after 2 seconds
            setTimeout(function () {
            $button.prop("disabled", false).text("Save");
            }, 2000);
        },
        });
    });
});
