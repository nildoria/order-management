(function ($) {
  ("use strict");

  $(document).on("click", ".upload-image-button", function (e) {
    e.preventDefault();

    const button = $(this),
      group_item = button.closest(".form-group"),
      target = group_item.find('input[type="text"]'),
      preview = group_item.find("img");

    var image = wp
      .media({
        title: "Upload Image",
        multiple: false,
      })
      .open()
      .on("select", function (e) {
        var uploaded_image = image.state().get("selection").first();
        var image_url = uploaded_image.toJSON().url;
        target.val(image_url);
        preview.attr("src", image_url).show();
        button.nextAll(".remove-image-button").show();
      });
  });

  $(document).on("click", ".remove-image-button", function (e) {
    e.preventDefault();

    const button = $(this),
      group_item = button.closest(".form-group"),
      target = group_item.find('input[type="text"]'),
      preview = group_item.find("img");

    target.val("");
    preview.hide();
    button.hide();

    return false;
  });

  $("#order_type-form").on("submit", function (event) {
    event.preventDefault();

    var $self = $(this),
      getData = $self.serializeArray(),
      order_id = $("#order_id").val();

    getData.push(
      {
        name: "action",
        value: "update_order_type",
      },
      {
        name: "nonce",
        value: all_around_clients_vars.nonce,
      }
    );

    $.ajax({
      type: "POST",
      dataType: "json",
      url: all_around_clients_vars.ajax_url,
      data: getData,
      success: function (response) {
        if (response.success === true) {
          alert(response.data.message);
          location.reload();
        } else {
          alert(response.data.message);
        }
      },
    });

    return false;
  });

  $("#addClientForm").on("submit", function (event) {
    event.preventDefault();

    if (!$(this).valid()) return false;

    var $self = $(this),
      type = $self.data("type"),
      getData = $self.serializeArray();

    let action = "create_client";

    if (type == "edit") {
      action = "update_client";
    }

    getData.push(
      {
        name: "action",
        value: action,
      },
      {
        name: "nonce",
        value: all_around_clients_vars.nonce,
      }
    );

    $.ajax({
      type: "POST",
      dataType: "json",
      url: all_around_clients_vars.ajax_url,
      data: getData,
      success: function (response) {
        if (response.success === true) {
          alert(response.data.message);
          location.reload();
        } else {
          alert(response.data.message);
        }
      },
    });

    return false;
  });

  $("#order_type").on("change", function () {
    // if option has value then show om_order_type_submit otherwise hide it
    if ($(this).val()) {
      $(".om_order_type_submit").show();
    } else {
      $(".om_order_type_submit").hide();
    }
  });

  // if client_type selected is "company" then show company details fields by class ".om_hidden_details" with slideToggle

  $(document).on("change", "#client_type", function () {
    if ($(this).val() == "company") {
      $(".om_hidden_details").fadeIn();
    } else {
      $(".om_hidden_details").fadeOut();
    }
  });

  // Show Token if Status is Client
  $(document).on("change", "#status", function () {
    clientStatus();
  });

  function clientStatus() {
    if ($("#status").val() == "client") {
      $(".client_token_field").fadeIn();
    } else {
      $(".client_token_field").fadeOut();
    }
  }
  clientStatus();

  if ($("#addClientForm").length > 0) {
    $("#addClientForm").validate();
  }

  $(".allaround--client-orders").magnificPopup({
    type: "ajax",
    ajax: {
      settings: null, // Ajax settings object that will extend default one - http://api.jquery.com/jQuery.ajax/#jQuery-ajax-settings
      // For example:
      // settings: {cache:false, async:false}

      cursor: "mfp-ajax-cur", // CSS class that will be added to body during the loading (adds "progress" cursor)
      tError: "The content could not be loaded.", //  Error message, can contain %curr% and %total% tags if gallery is enabled
    },
    callbacks: {
      parseAjax: function (mfpResponse) {
        // mfpResponse.data is a "data" object from ajax "success" callback
        // for simple HTML file, it will be just String
        // You may modify it to change contents of the popup
        // For example, to show just #some-element:
        // mfpResponse.data = $(mfpResponse.data).find('#some-element');

        // mfpResponse.data must be a String or a DOM (jQuery) element

        console.log("Ajax content loaded:", mfpResponse);
      },
      ajaxContentAdded: function () {
        // Ajax content is loaded and appended to DOM
        console.log(this.content);
      },
    },
  });

  // Update Client Profile data on Order page
  $("#update-order-client").on("click", function () {
    const $this = $(this);
    const client_id = $this.data("client_id");
    const order_post_id = $this.data("post_id");
    const type = $this.data("type");
    let client_data = {
      client_type: $("#client_type").val(),
      first_name: $("#billing_first_name").val(),
      last_name: $("#billing_last_name").val(),
      address_1: $("#billing_address_1").val(),
      company: $("#billing_company").val(),
      city: $("#billing_city").val(),
      phone: $("#billing_phone").val(),
      email: $("#billing_email").val(),
      nonce: all_around_clients_vars.nonce,
    };

    $(this).addClass("ml_loading");

    $.ajax({
      url: all_around_clients_vars.ajax_url,
      type: "POST",
      data: {
        action: "update_client",
        client_id: client_id,
        order_post_id: order_post_id,
        type: type,
        ...client_data,
      },
      success: function (response) {
        if (response.success) {
          // remove loading class
          $this.removeClass("ml_loading");

          // Check if the first name or last name has changed
          const oldFirstName = $("#billing_first_name").data("old_value");
          const oldLastName = $("#billing_last_name").data("old_value");
          const newFirstName = $("#billing_first_name").val();
          const newLastName = $("#billing_last_name").val();

          if (oldFirstName !== newFirstName || oldLastName !== newLastName) {
            const fullName = newFirstName + " " + newLastName;
            $(".om__orderSummeryItem span a").text(fullName);

            // Update the data attributes with the new values
            $("#billing_first_name").data("old_value", newFirstName);
            $("#billing_last_name").data("old_value", newLastName);
          }
          // close the modal
          $.magnificPopup.close();
          Toastify({
            text: `Client info updated successfully!`,
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
          alert(
            "Failed to update client information: " + response.data.message
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert("Failed to update client information.");
      },
    });
  });
})(jQuery); /*End document ready*/
