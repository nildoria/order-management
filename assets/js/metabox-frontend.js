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

        // checkLogos();
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

    // checkLogos();

    return false;
  });

  const initial_order_type = $("#order_type").val();

  $("#order_type-form").on("submit", function (event) {
    event.preventDefault();

    $(".om_order_type_submit").addClass("pulse");

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
          $(".om_order_type_submit").removeClass("pulse");
          setTimeout(() => {
            $(".om_order_type_submit").fadeOut();
          }, 500);

          // Store order_type and client_type in localStorage
          localStorage.setItem("order_type", response.data.order_type);
          localStorage.setItem("client_type", response.data.client_type);

          // if initial_order_type is empty then do the bellow code
          if (!initial_order_type) {
            // Extract email, phone, full name, and total paid from the frontend
            const email = $("#billing_email").val();
            const phone = $("#billing_phone").val();
            const fullName =
              $("#billing_first_name").val() +
              " " +
              $("#billing_last_name").val();
            let totalPaid = parseFloat(
              $(".om__orderTotal").text().replace("₪", "").trim()
            ); // Extract total without symbol and convert to a number

            // Function to push the event to the dataLayer
            function pushEvent(event) {
              window.dataLayer = window.dataLayer || [];
              window.dataLayer.push({
                event: event,
                email: email,
                phone: phone,
                full_name: fullName,
                total_paid: totalPaid,
              });
            }

            // Function to push events with thresholds based on totalPaid
            function pushEventsWithThresholds(baseEvent) {
              pushEvent(baseEvent); // Push the base event
              if (totalPaid >= 100) pushEvent(`${baseEvent}_value100`);
              if (totalPaid >= 200) pushEvent(`${baseEvent}_value200`);
              if (totalPaid >= 300) pushEvent(`${baseEvent}_value300`);
              if (totalPaid >= 400) pushEvent(`${baseEvent}_value400`);
              if (totalPaid >= 500) pushEvent(`${baseEvent}_value500`);
              if (totalPaid >= 600) pushEvent(`${baseEvent}_value600`);
              if (totalPaid >= 700) pushEvent(`${baseEvent}_value700`);
              if (totalPaid >= 800) pushEvent(`${baseEvent}_value800`);
              if (totalPaid >= 900) pushEvent(`${baseEvent}_value900`);
              if (totalPaid >= 1000) pushEvent(`${baseEvent}_value1000`);
            }

            // Check the client type and trigger the appropriate Data Layer events
            if (
              (response.data.old_client_type !== "company" &&
                response.data.order_type === "company") ||
              (!response.data.old_client_type &&
                response.data.order_type === "company")
            ) {
              // New company purchase
              pushEventsWithThresholds("ga4_new_company_purchase");
            } else if (
              response.data.old_client_type === "company" &&
              response.data.order_type === "company"
            ) {
              // Repeated company purchase
              pushEventsWithThresholds("ga4_repeated_company_purchase");
            } else if (
              !response.data.old_client_type &&
              response.data.order_type === "personal"
            ) {
              // New personal purchase
              pushEventsWithThresholds("ga4_new_personal_purchase");
            } else if (
              (response.data.old_client_type === "personal" &&
                response.data.order_type === "personal") ||
              (response.data.old_client_type === "company" &&
                response.data.order_type === "personal")
            ) {
              // Repeated personal purchase
              pushEventsWithThresholds("ga4_repeated_personal_purchase");
            }
          }

          Toastify({
            text: `Order Type Updated Successfully!!`,
            duration: 3000,
            close: true,
            gravity: "bottom", // `top` or `bottom`
            position: "right", // `left`, `center` or `right`
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
              background: "linear-gradient(to right, #00b09b, #96c93d)",
            },
          }).showToast();

          // Reload the page after storing the values
          // set timeout to reload the page after 1 seconds
          setTimeout(() => {
            location.reload();
          } , 1000);
        } else {
          $(".om_order_type_submit").removeClass("pulse");
          alert(response.data.message);
        }
      },
    });

    return false;
  });

  $(document).ready(function () {
    // Retrieve the stored values from localStorage
    const order_type = localStorage.getItem("order_type");
    const client_type = localStorage.getItem("client_type");

    // If the stored values match the conditions, show the popup
    if (order_type === "company" && client_type === "company") {
      $(".om__client_company_info").show();
      if ($.magnificPopup && $("#billing-form-modal").length) {
        $.magnificPopup.open({
          items: {
            src: "#billing-form-modal",
            type: "inline",
            closeBtnInside: true,
          },
        });
      }
    } else {
      $(".om__client_company_info").hide();
    }

    // Clear the stored values in localStorage after use
    localStorage.removeItem("order_type");
    localStorage.removeItem("client_type");
    // Check if the order_type is company when the page loads
    const order_type_val = $("#order_type").val();

    // If the order_type value is 'company', show the .om__client_company_info div
    if (order_type_val === "company") {
      $(".om__client_company_info").show();
    } else {
      $(".om__client_company_info").hide();
    }
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

  // check if .om_company_logoInput_group img src is empty or not, if not empty then implement show() function
  $(".om_company_logoInput_group img").each(function () {
    if ($(this).attr("src") !== "") {
      $(this).show();
      $(this)
        .closest(".om_company_logoInput_group")
        .find(".remove-image-button")
        .show();
    }
  });

  function checkLogos() {
    var dark_logo = $("#dark_logo").val();
    var lighter_logo = $("#lighter_logo").val();

    if (dark_logo && lighter_logo) {
      $("#submitOmCompanyLogo").prop("disabled", false);
    } else {
      $("#submitOmCompanyLogo").prop("disabled", true);
    }
  }

  // Initial check
  // checkLogos();

  // OM Company Logo Uploads
  $("#submitOmCompanyLogo").on("click", function () {
    var client_id = $(this).data("client_id");
    var dark_logo = $("#dark_logo").val();
    var lighter_logo = $("#lighter_logo").val();
    var back_light = $("#back_light").val();
    var back_dark = $("#back_dark").val();
    var post_id = $("#post_id").val();

    // add .ml_loading class to the button
    $(this).addClass("ml_loading");

    $.ajax({
      url: all_around_clients_vars.ajax_url,
      type: "POST",
      data: {
        action: "om_update_client_company_logos",
        client_id: client_id,
        post_id: post_id,
        dark_logo: dark_logo,
        lighter_logo: lighter_logo,
        back_light: back_light,
        back_dark: back_dark,
        nonce: all_around_clients_vars.nonce,
      },
      success: function (response) {
        if (response.success) {
          // remove loading class
          $("#submitOmCompanyLogo").removeClass("ml_loading");
          alert("Logo fields updated successfully!");
          location.reload();
        } else {
          if (response.data && response.data.message) {
            // remove loading class
            $("#submitOmCompanyLogo").removeClass("ml_loading");
            alert(response.data.message);
          } else {
            alert("Failed to update logos.");
          }
        }
      },
      error: function () {
        // remove loading class
        $("#submitOmCompanyLogo").removeClass("ml_loading");
        alert("An unexpected error occurred.");
      },
    });
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
    let client_data = {
      client_type: $("#client_type").val(),
      first_name: $("#billing_first_name").val(),
      last_name: $("#billing_last_name").val(),
      address_1: $("#billing_address_1").val(),
      address_2: $("#billing_address_2").val(),
      invoice: $("#billing_company").val(),
      city: $("#billing_city").val(),
      phone: $("#billing_phone").val(),
      email: $("#billing_email").val(),
      logo_type: $("#logo_type").val(),
      mini_url: $("#mini_url").val(),
      mini_header: $("#mini_header").val(),
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

  // Check if #addClientForm is available
  if ($("#addClientForm").length) {
    // Listen for changes on the #status select element
    $("#status").on("change", function () {
      var statusValue = $(this).val();

      if (statusValue === "company_prospect") {
        // Change the select value of #client_type to company
        // $("#client_type").val("company");
        $("#client_type").val("company").trigger("change");

        // Disable the #client_type select element to prevent changes
        $("#client_type").prop("disabled", true);
      } else {
        // Enable the #client_type select element if status is not company_prospect
        $("#client_type").prop("disabled", false);
      }
    });
  }
})(jQuery); /*End document ready*/
