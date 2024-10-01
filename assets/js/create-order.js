jQuery(document).ready(function ($) {
  function getGroupedItemRate(quantity, steps, regularPrice) {
    let rate = regularPrice; // Start with regular price
    for (const step of steps) {
      if (quantity <= parseInt(step.quantity)) {
        rate = step.amount;
        break;
      }
    }
    if (rate == 0) {
      rate = regularPrice;
    }
    return rate;
  }

  function updateGroupProductPrice(modal) {
    const wrapper = modal.find(".product-grouped-product-wraper");
    let steps;
    try {
      steps = JSON.parse(wrapper.attr("data-steps"));
    } catch (e) {
      console.error("Invalid JSON in data-steps:", wrapper.attr("data-steps"));
      return;
    }
    const regularPrice = parseFloat(wrapper.attr("data-regular_price"));

    let totalUnits = 0;
    modal.find(".group-product-input").each(function () {
      const qty = parseInt($(this).val()) || 0;
      totalUnits += qty;
    });

    if (totalUnits === 0) {
      modal.find(".grouped_product_add_to_cart").attr("disabled", true);
    } else {
      modal.find(".grouped_product_add_to_cart").attr("disabled", false);
    }

    let customRate = $(".grouped_custom_rate_input").val();

    let unitRate = getGroupedItemRate(totalUnits, steps, regularPrice);

    if (customRate && !isNaN(customRate)) {
      unitRate = parseFloat(customRate);
    }
    const subTotalPrice = unitRate * totalUnits;

    modal.find(".group_unite_price").text(unitRate);
    modal.find(".item_unit_rate").val(unitRate);
    modal.find(".total_units").text(totalUnits);
    modal.find(".item_total_units").val(totalUnits);
    modal.find(".item-total-number").text(subTotalPrice);
    modal.find(".item_total_price").val(subTotalPrice);
  }

  $(".group-product-input").on("input", function () {
    let value = $(this).val().replace(/\D/g, ""); // Only numbers
    if (value.length > 3) {
      value = value.slice(0, 3); // Limit to 3 characters
    }
    $(this).val(value);

    const modal = $(this).closest(".product-details-modal");
    updateGroupProductPrice(modal);
  });

  // Rate change event listener
  $(".grouped_custom_rate_input").on("input", function () {
    let value = $(this).val().replace(/\D/g, ""); // Only numbers
    $(this).val(value);

    const modal = $(this).closest(".product-details-modal");
    updateGroupProductPrice(modal);
  });

  // Initialize calculations on page load
  $(".grouped-product").each(function () {
    updateGroupProductPrice($(this));
  });

  // Function to get the item rate based on quantity
  function getItemRate(quantity, steps) {
    let rate = steps[0].amount;
    for (let i = 0; i < steps.length; i++) {
      if (
        i === steps.length - 1 ||
        quantity < parseInt(steps[i + 1].quantity)
      ) {
        rate = steps[i].amount;
        break;
      }
    }
    return rate;
  }

  // Event listener for quantity input change
  function updatePriceAndTotal() {
    let quantityStr = $(".custom-quantity").val().replace(/\D/g, ""); // Only numbers
    if (quantityStr.length > 6) {
      quantityStr = quantityStr.slice(0, 6); // Limit to 6 characters
    }
    if (quantityStr === "" || quantityStr === "0") {
      quantityStr = "1";
    }
    let quantity = parseInt(quantityStr);

    let steps = $(".custom-quantity").data("steps"); // Get steps from data attribute
    if (typeof steps === "string") {
      steps = JSON.parse(steps);
    }

    let customRate = $(".item-rate-number-input").val();
    let rate = getItemRate(quantity, steps);

    if (customRate && !isNaN(customRate)) {
      rate = parseFloat(customRate);
    }

    let total = rate * quantity;

    // Update the values in the DOM
    $(".custom-quantity").val(quantityStr); // Update the input value
    $(".product-details-modal")
      .find(".price-item span.item-rate-number")
      .text(rate);
    $(".product-details-modal")
      .find(".price-total span.item-total-number")
      .text(total);
    $(".product-details-modal").find(".item_total_price").val(total);
  }

  // Quantity change event listener
  $(".custom-quantity").on("input", function () {
    updatePriceAndTotal();

    let quantity = parseInt($(this).val());
    if (quantity > 0) {
      $(this)
        .closest(".product-details-modal")
        .find(".single_add_to_cart_button")
        .attr("disabled", false);
    }
  });

  // Rate change event listener
  $(".item-rate-number-input").on("input", function () {
    updatePriceAndTotal(); // Call the same function to update the rate and total
  });

  // Prevent invalid input characters
  $(".custom-quantity, .group-product-input").on("keypress", function (e) {
    let charCode = e.which ? e.which : e.keyCode;
    if (charCode < 48 || charCode > 57) {
      return false;
    }
  });

  // Initial calculation for each custom-quantity input
  $(".custom-quantity").each(function () {
    $(this).trigger("input");
  });

  // Initialize Magnific Popup on item click
  $(".item-wrap").on("click", function () {
    let modalId = $(this).data("modal-id");
    $.magnificPopup.open({
      items: {
        src: "#" + modalId,
        type: "inline",
      },
      closeBtnInside: true,
      fixedContentPos: true,
      mainClass: "mfp-no-margins mfp-with-zoom", // class to remove default margin from left and right side
      zoom: {
        enabled: true,
        duration: 300, // don't forget to change the duration also in CSS
      },
    });
  });

  // Handle Add to Cart button click
  $(".single_add_to_cart_button").on("click", function (e) {
    e.preventDefault();
    const modal = $(this).closest(".product-details-modal");
    const productId = modal.data("product_id");
    const productName = modal.find(".modal-title").text();
    const productThumbnail = modal.find(".product-thumb").val();
    // const quantity = modal.find(".custom-quantity").val();
    const quantity =
      modal.find(".custom-quantity").length > 0
        ? modal.find(".custom-quantity").val()
        : modal.find(".freestyle-custom-quantity").val();
    const selectedColor = modal
      .find("input[name='custom_color']:checked")
      .val();
    const subTotalPrice = modal.find(".item_total_price").val();
    const artworkUrl = modal.find(".uploaded_file_path").val();
    const instructionNote = modal.find(".new_product_instruction_note").val();
    var artworkHTML = "";
    modal.find(".uploaded_artwork").each(function () {
      artworkHTML += $(this).prop("outerHTML");
    });

    if (isNaN(parseFloat(subTotalPrice))) {
      console.error("Total Price is NaN");
      return;
    }

    // Remove existing cart items with the same product ID
    $(".content-cart ul li")
      .filter(function () {
        return $(this).data("product-id") === productId;
      })
      .remove();

    // Add product details to cart
    var cartItem = `
    <li class="single-cart-item" data-product-id="${productId}">
        <button class="remove-cart-item">×</button>
        <img class="cart-item-thumb" src="${productThumbnail}" alt="${productName}" />
        <span class="cart-item-contents">
        <span class="product-name">${productName}</span>
        <span class="product-quantity">Quantity: <span class="product-quantity-number">${quantity}</span></span>
        <span class="product-total-price-container">Items Subtotal: <span class="product-total-price">${subTotalPrice}</span>₪</span>
        <input type="hidden" name="product-total-price-incart" class="product-total-price-incart" value="${subTotalPrice}">
  `;
    if (selectedColor) {
      cartItem += `<span class="product-color">Color: ${selectedColor}</span>`;
    }
    if (instructionNote) {
      cartItem += `<span class="product-instruction-note">Instruction Note: ${instructionNote}</span>`;
    }
    if (artworkUrl) {
      cartItem += `<span class="product-artwork">Artworks: ${artworkHTML}</span>`;
      cartItem += `<input type="hidden" name="product-artworks-incart" class="product-artworks-incart" value='${artworkUrl}'>`;
    }
    cartItem += `</span>`;
    cartItem += `</li>`;

    $(".content-cart ul").append(cartItem);

    // Update cart total
    updateCartTotal();
    validateCheckout();

    // Reset input fields
    // resetModalFields(modal);

    // Close the modal
    $.magnificPopup.close();
  });

  // Handle Add to Cart button click for grouped products
  $(".grouped_product_add_to_cart").on("click", function (e) {
    e.preventDefault();
    const modal = $(this).closest(".product-details-modal");
    const productId = modal.data("product_id");
    const productName = modal.find(".modal-title").text();
    const productThumbnail = modal.find(".product-thumb").val();
    const artworkUrl = modal.find(".uploaded_file_path").val();
    const instructionNote = modal.find(".new_product_instruction_note").val();
    var artworkHTML = "";
    modal.find(".uploaded_artwork").each(function () {
      artworkHTML += $(this).prop("outerHTML");
    });

    // Remove existing cart items with the same product ID
    $(".content-cart ul li")
      .filter(function () {
        return $(this).data("product-id") === productId;
      })
      .remove();

    modal.find(".group-product-input").each(function () {
      const quantity = $(this).val();
      const color = $(this).data("color");
      const size = $(this).data("size");

      if (quantity && quantity !== "0") {
        const itemUniteRate = modal.find(".item_unit_rate").val();
        const subTotalPrice = itemUniteRate * quantity;

        // Add product details to cart
        var cartItem = `
          <li class="single-cart-item" data-product-id="${productId}">
            <button class="remove-cart-item">×</button>
            <img class="cart-item-thumb" src="${productThumbnail}" alt="${productName}" />
            <span class="cart-item-contents">
            <span class="product-name">${productName}</span>
            <span class="product-quantity">Quantity: <span class="product-quantity-number">${quantity}</span></span>
            <span class="product-total-price-container">Items Subtotal: <span class="product-total-price">${subTotalPrice}</span>₪</span>
            <input type="hidden" name="product-total-price-incart" class="product-total-price-incart" value="${subTotalPrice}">
        `;
        if (color) {
          cartItem += `<span class="product-color">Color: ${color}</span>`;
        }
        if (size) {
          cartItem += `<span class="product-size">Size: ${size}</span>`;
        }
        if (instructionNote) {
          cartItem += `<span class="product-instruction-note">Instruction Note: ${instructionNote}</span>`;
        }
        if (artworkUrl) {
          cartItem += `<span class="product-artwork">Artworks: ${artworkHTML}</span>`;
          cartItem += `<input type="hidden" name="product-artworks-incart" class="product-artworks-incart" value='${artworkUrl}'>`;
        }
        cartItem += `</span>`;
        cartItem += `</li>`;

        $(".content-cart ul").append(cartItem);
      }
    });

    // Update cart total
    updateCartTotal();
    validateCheckout();

    // Reset input fields
    // resetModalFields(modal);

    // Close the modal
    $.magnificPopup.close();
  });

  $("#shipping_method").on("change", function () {
    if ($("#shipping_method").val() === "flat_rate") {
      $(".content-cart .shipping-total-number").text("29.00");
    } else {
      $(".content-cart .shipping-total-number").text("0.00");
    }
    validateCheckout();
    updateCartTotal();
  });

  function validateCheckout() {
    if (
      "" === $("#shipping_method").val() ||
      "" === $("#client-select").val() ||
      $(".content-cart ul li").length === 0
    ) {
      $(".content-cart #checkout").attr("disabled", true);
    } else {
      $(".content-cart #checkout").attr("disabled", false);
    }
  }

  function updateCartTotal() {
    let total = 0;
    $(".content-cart ul li").each(function () {
      total += parseFloat($(this).find(".product-total-price-incart").val());
    });
    // add .content-cart .shipping-total-number text to total
    total += parseFloat($(".content-cart .shipping-total-number").text());

    $(".content-cart .cart-total-number").text(total.toFixed(2));
    if (total !== 0) {
      $(".content-cart #checkout").attr("disabled", false);
    }
    validateCheckout();

    // Show/hide empty cart message
    toggleEmptyCartMessage();
  }

  // Function to remove a cart item
  $(document).on("click", ".remove-cart-item", function () {
    $(this).closest("li.single-cart-item").remove();
    updateCartTotal();
  });

  // Category and search filter function
  function filterProducts() {
    const selectedCategory = $("#category-select").val();
    const searchTerm = $("#product-search").val().toLowerCase();
    const items = $(".items-wrapper .item");

    items.each(function () {
      const item = $(this);
      const itemCategory = item.data("category");
      const itemName = item.find(".title").text().toLowerCase();

      const categoryMatch =
        selectedCategory === "all" || itemCategory.includes(selectedCategory);
      const searchMatch =
        searchTerm.length < 3 || itemName.includes(searchTerm);

      if (categoryMatch && searchMatch) {
        item.show();
      } else {
        item.hide();
      }
    });
  }

  $("#category-select").on("change", function () {
    $("#product-search").val(""); // Reset search field
    filterProducts();
  });

  $("#product-search").on("input", function () {
    $("#select2-category-select-container").text("All Categories");
    $("#category-select").val("all"); // Reset category select to default
    filterProducts();
  });

  // ********** Add New Item to the Existing Order **********//
  $(".new_product_artwork").on("change", function (event) {
    let $this = $(this);
    let files = event.target.files;
    let uploadedFiles = [];

    if (files.length > 0) {
      $this
        .closest(".product-details-modal")
        .find('button[name="add-to-cart"]')
        .addClass("ml_loading")
        .prop("disabled", true);
      let formData = new FormData();
      for (let i = 0; i < files.length; i++) {
        formData.append("files[]", files[i]);
      }

      fetch("/wp-content/themes/manage-order/includes/php/artwork-upload.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Store the file paths in an array
            uploadedFiles = data.file_paths;
            $this
              .siblings(".uploaded_file_path")
              .val(JSON.stringify(uploadedFiles));
            // add a span with a ling to the uploaded file and hide .new_product_artwork input
            let uploadedFilesHtml = "";
            uploadedFiles.forEach((file, index) => {
              uploadedFilesHtml += `<span class="uploaded_artwork"><a href="${file}" target="_blank">Attachment ${
                index + 1
              }</a></span>`;
            });
            $this.closest(".form-group").append(uploadedFilesHtml);
            $this.hide();

            console.log("Files uploaded successfully:", data.file_paths);
          } else {
            alert("Failed to upload files: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
        })
        .finally(() => {
          $this.val("");
          $this
            .closest(".product-details-modal")
            .find('button[name="add-to-cart"]')
            .removeClass("ml_loading")
            .prop("disabled", false);
        });
    }
  });

  // Handle checkout button click
  $("#checkout").on("click", function (e) {
    e.preventDefault();

    $(".sitewide_spinner").addClass("loading");

    let agentID = $("#agent-select").val();
    let orderType = $("#order_type").val();
    let clientID = $("#client-select").val();
    let clientType = $("#client_type").val();
    let totalPaid = parseFloat(
      $(".cart-total-number").text().replace("₪", "").trim()
    );

    // Get invoice/receipt selections
    let invoice = $("#payment_invoice").is(":checked") ? "yes" : "no";
    let receipt = $("#payment_receipt").is(":checked") ? "yes" : "no";

    // Get payment method selections
    let wireTransfer = $("#wire_transfer").is(":checked") ? "yes" : "no";
    let creditCard = $("#credit_card").is(":checked") ? "yes" : "no";
    let cash = $("#cash").is(":checked") ? "yes" : "no";

    // Get No Invoice selection
    let noInvoice = $("#no_invoice").is(":checked") ? "yes" : "no";

    // Get the selected date
    let orderDate = $("#order_date").val();

    const paymentData = {
      invoice: invoice,
      receipt: receipt,
      order_date: orderDate,
      wire_transfer: wireTransfer,
      credit_card: creditCard,
      cash: cash,
      no_invoice: noInvoice,
    };

    // Collect billing and shipping information
    const billing = {
      first_name: $("#billing_first_name").val(),
      last_name: $("#billing_last_name").val(),
      address_1: $("#billing_address_1").val(),
      address_2: $("#billing_address_2").val(),
      company: $("#billing_company").val(),
      city: $("#billing_city").val(),
      country: $("#billing_country").val() || "Israel",
      email: $("#billing_email").val(),
      phone: $("#billing_phone").val(),
    };

    // Collect cart items
    const lineItems = [];
    $(".content-cart ul li.single-cart-item").each(function () {
      const item = $(this);
      const productId = item.data("product-id");
      const productName = item.find(".product-name").text();
      const quantity = item
        .find(".product-quantity-number")
        .text()
        .replace(" ", "");
      const subtotal = item
        .find(".product-total-price")
        .text()
        .replace("₪", "");
      const color = item.find(".product-color").text().replace("Color: ", "");
      const size = item.find(".product-size").text().replace("Size: ", "");
      const artworks = item.find(".product-artworks-incart").val();
      const instructionNote = item
        .find(".product-instruction-note")
        .text()
        .replace("Instruction Note: ", "");
      const variableQuantity = item
        .find(".product-quantity-meta")
        .text()
        .replace("Quantity: ", "");

      const metaData = [];
      if (color) metaData.push({ key: "Color", value: color });
      if (size) metaData.push({ key: "Size", value: size });
      if (variableQuantity)
        metaData.push({ key: "Quantity", value: variableQuantity });
      if (artworks) metaData.push({ key: "Attachment", value: artworks });
      if (instructionNote)
        metaData.push({ key: "Instruction Note", value: instructionNote });

      lineItems.push({
        product_id: productId,
        name: productName,
        quantity: quantity,
        total: subtotal,
        meta_data: metaData,
      });
    });

    // Collect shipping method information
    const shippingMethod = $("#shipping_method").val();
    const shippingMethodTitle = $("#shipping_method option:selected").text();
    const shippingTotal = $(".shipping-total-number").text();

    const orderData = {
      action: "create_order_from_form",
      security: alarnd_create_order_vars.nonce,
      first_name: billing.first_name,
      last_name: billing.last_name,
      address_1: billing.address_1,
      address_2: billing.address_2,
      company: billing.company,
      city: billing.city,
      country: billing.country,
      email: billing.email,
      phone: billing.phone,
      shipping_method: shippingMethod,
      shipping_method_title: shippingMethodTitle,
      shipping_total: shippingTotal,
      line_items: JSON.stringify(lineItems),
    };

    $.post({
      url: alarnd_create_order_vars.ajax_url,
      data: orderData,
      success: function (response) {
        if (response.success) {
          // Clear the cart
          $(".content-cart ul").empty();
          $(".cart-total-number").text("0");
          $("#checkout").attr("disabled", true);
          $("#billing-form").trigger("reset");
          $("#shipping_method option:selected")
            .prop("selected", false)
            .trigger("change");
          $("#client-select").val(null).trigger("change");
          // Create the order post
          createOrderPost(response.data, orderType, agentID, paymentData);

          $(".sitewide_spinner").removeClass("loading");
        } else {
          $(".sitewide_spinner").removeClass("loading");
          alert("Error creating order: " + response.data);
        }
      },
      error: function (xhr, status, error) {
        $(".sitewide_spinner").removeClass("loading");
        console.error("AJAX Error:", error);
        console.log("XHR:", xhr);
        console.log("Status:", status);
        console.log("Error:", error);
        console.log("Response Headers:", xhr.getAllResponseHeaders());
        console.log("Response Body:", xhr.responseText);
        alert("An error occurred while creating the order. Please try again.");
      },
    });
  });

  // Initialize the jQuery UI Datepicker on #order_date
  $("#order_date").datepicker({
    dateFormat: "dd/mm/yy", // Set the desired date format
    // changeMonth: true,
    // changeYear: true,
    showButtonPanel: true,
    // minDate: 0,
  });

  // Set the current date as the default selected date
  $("#order_date").datepicker("setDate", new Date());

  // Handle the payment method checkboxes
  $('input[name="payment_method"]').on("change", function () {
    // Uncheck other checkboxes when one is selected
    $('input[name="payment_method"]').not(this).prop("checked", false);

    // Ensure at least one checkbox is checked
    if (!$('input[name="payment_method"]:checked').length) {
      $(this).prop("checked", true);
    }
  });

  // Handle the No Invoice checkbox
  $("#no_invoice").on("change", function () {
    if ($(this).is(":checked")) {
      // Uncheck and disable all other checkboxes
      $(
        'input[name="payment_method"], input[name="order_date"]'
        // 'input[name="payment_method"], input[name="invoice-receipt"], input[name="order_date"]'
      )
        .prop("checked", false)
        .prop("disabled", true);
      // add css opacity: 0.5 to .invoice-receipt-options and .payment-method-options
      // $(
      //   ".invoice-receipt-options, .payment-method-options, .order-date-field"
      // ).css("opacity", "0.5");
    } else {
      // Enable the other checkboxes when No Invoice is unchecked
      $('input[name="payment_method"], input[name="order_date"]').prop(
        "disabled",
        false
      );
      // Remove CSS opacity from .invoice-receipt-options and .payment-method-options
      // $(
      //   ".invoice-receipt-options, .payment-method-options, .order-date-field"
      // ).css("opacity", "1");
    }
  });

  function createOrderPost(orderData, orderType, agentID, paymentData) {
    let root_domain = alarnd_create_order_vars.redirecturl;

    // Username and password for Basic Authentication
    const username = "OmAdmin";

    // Determine the password based on the root_domain
    let password = "";

    if (root_domain == "https://om.allaround.co.il") {
      password = "Vlh4 F7Sw Zu26 ShUG 6AYu DuRI";
    } else if (root_domain == "https://om.lukpaluk.xyz") {
      password = "vZmm GYw4 LKDg 4ry5 BMYC 4TMw";
    } else {
      password = "Qj0p rsPu eU2i Fzco pwpX eCPD";
    }

    // Check if the request is to the same origin
    let headers = {};
    if (window.location.origin !== root_domain) {
      const basicAuth = btoa(`${username}:${password}`);
      headers.Authorization = "Basic " + basicAuth;
    }

    orderData.line_items.forEach((item) => {
      item.product_name = item.name;
      delete item.name;
      // and add a new key printing_note with empty value
      item.printing_note = "";
    });

    orderData.agent_id = agentID;
    orderData.order_type = orderType;
    orderData.order_source = "manual_order";
    orderData.payment_data = paymentData;

    $.ajax({
      url: `${root_domain}/wp-json/manage-order/v1/create`,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(orderData),
      headers: headers,
      success: function (response) {
        console.log("Order post created successfully:", response);
        alert("New Order created successfully!");
        // Redirect to the newly created order post
        if (response && response.post_url) {
          window.location.href = response.post_url;
        } else {
          console.error("Post URL not returned in response.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error creating order post:", error);
        console.log("XHR:", xhr);
        console.log("Status:", status);
        console.log("Error:", error);
        console.log("Response Headers:", xhr.getAllResponseHeaders());
        console.log("Response Body:", xhr.responseText);
        alert("Error creating order post: " + error);
      },
    });
  }

  // Fetch and populate billing form based on selected client
  $("#client-select").on("change", function () {
    toggleArrow();
    validateCheckout();
    const clientId = $(this).val();

    console.log("Selected client IDs:", clientId);

    if (clientId) {
      // Fetch client details via AJAX
      $.ajax({
        url: alarnd_create_order_vars.ajax_url,
        method: "POST",
        data: {
          action: "get_client_details",
          client_id: clientId,
          security: alarnd_create_order_vars.nonce,
        },
        success: function (response) {
          if (response.success) {
            const client = response.data;
            console.log("Client details:", client);
            // if client.client_type is not empty, set the client_type value to the select box
            if (client.client_type) {
              $("#billing-form #client_type").val(client.client_type);
            }
            // Populate billing form with client details
            $("#billing-form #billing_first_name").val(client.first_name);
            $("#billing-form #billing_last_name").val(client.last_name);
            $("#billing-form #billing_first_name").data(
              "old_value",
              client.first_name
            );
            $("#billing-form #billing_last_name").data(
              "old_value",
              client.last_name
            );
            $("#billing-form #billing_company").val(client.invoice);
            $("#billing-form #billing_address_1").val(client.address_1);
            $("#billing-form #billing_address_2").val(client.address_2);
            $("#billing-form #billing_city").val(client.city);
            $("#billing-form #billing_email").val(client.email);
            $("#billing-form #billing_phone").val(client.phone);
            $("#update-order-client").data("client_id", clientId);

            if ($(".om__billing-form-modal").length) {
              if (client.client_type === "company") {
                $(".om__client_company_info").show();
              } else {
                $(".om__client_company_info").hide();
              }
              $("#billing-form #logo_type").val(client.logo_type);
              $("#billing-form #mini_url").val(client.mini_url);
              $("#billing-form #mini_header").val(client.mini_header);
            }

            // $(".client_profile_URL a").attr("href", client.url);
          } else {
            alert("Failed to fetch client details");
          }
        },
        error: function () {
          alert("Error fetching client details");
        },
      });
    } else {
      // Clear the form if no client is selected
      $("#billing-form").trigger("reset");
    }
  });

  // Custom matcher function for Select2
  function customMatcher(params, data) {
    // If there are no search terms, return all of the data
    if ($.trim(params.term) === "") {
      return data;
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === "undefined") {
      return null;
    }

    // Create a string to combine name, email, and phone
    var combinedText =
      data.text +
      " " +
      $(data.element).data("email") +
      " " +
      $(data.element).data("phone");

    // Make the search case-insensitive
    var term = params.term.toLowerCase();

    // Check if the term is in the combined string
    if (combinedText.toLowerCase().indexOf(term) > -1) {
      return data;
    }

    // Return `null` if the term should not be displayed
    return null;
  }

  // Template for displaying options in the dropdown
  function formatClient(client) {
    if (!client.id) {
      return client.text;
    }

    var $client = $(
      "<div>" +
        client.text +
        " | " +
        $(client.element).data("email") +
        " | " +
        $(client.element).data("phone") +
        "</div>"
    );

    return $client;
  }

  // Template for displaying the selected option
  function formatClientSelection(client) {
    return client.text;
  }

  $(document).ready(function () {
    // Initialize Select2 with the custom matcher and templates
    $("#client-select").select2({
      placeholder: "Select a Client",
      allowClear: true,
      matcher: customMatcher,
      templateResult: formatClient,
      templateSelection: formatClientSelection,
    });
  });

  // Function to toggle arrow visibility based on selection
  function toggleArrow() {
    const hasSelection = $("#client-select").val();
    const select2Container = $("#client-select").siblings(".select2-container");

    if (hasSelection) {
      select2Container.addClass("hide-arrow");
      $(".client_profile_URL").show();
      $(".om__client_update_btn").show();
    } else {
      select2Container.removeClass("hide-arrow");
      $(".client_profile_URL").hide();
      $(".om__client_update_btn").hide();
    }
  }

  // Initial check to hide/show the arrow
  toggleArrow();

  // Open modal on client profile URL click
  $(".client_profile_URL").on("click", function (e) {
    e.preventDefault();
    $.magnificPopup.open({
      items: {
        src: "#billing-form-modal",
        type: "inline",
      },
      closeBtnInside: true,
      fixedContentPos: true,
      mainClass: "mfp-no-margins mfp-with-zoom", // class to remove default margin from left and right side
      zoom: {
        enabled: true,
        duration: 300, // don't forget to change the duration also in CSS
      },
    });
  });

  // Function to show/hide empty cart message
  function toggleEmptyCartMessage() {
    if ($(".content-cart ul li.single-cart-item").length === 0) {
      $(".empty-cart-message").show();
      $(".content-cart h4").hide();
    } else {
      $(".empty-cart-message").hide();
      $(".content-cart h4").show();
    }
  }

  // Initial check to show/hide empty cart message
  toggleEmptyCartMessage(); // Initial call to updateCartTotal to set the correct state on page load
  updateCartTotal();
  validateCheckout();

  // Initialize Select2 on the agent dropdown
  $("#agent-select").select2();

  // Initialize Select2 on the category dropdown
  $("#category-select").select2();

  // Initialize Select2 on the shipping dropdown
  $("#shipping_method").select2();

  // Function to ensure only numbers and one decimal point are allowed
  function sanitizeInput(value) {
    // Remove any non-numeric characters except for a decimal point
    let sanitizedValue = value.replace(/[^0-9.]/g, "");
    // Ensure only one decimal point is allowed
    sanitizedValue = sanitizedValue.replace(/(\..*)\./g, "$1");
    return sanitizedValue;
  }

  $(".freestyle-rate-number-input").on("input", function () {
    // Sanitize input value
    let freestyleRateStr = sanitizeInput($(this).val());
    $(this).val(freestyleRateStr);

    // Run the calculation function
    freestylePriceCalc();
  });

  function freestylePriceCalc() {
    $(".freestyle-custom-quantity").each(function () {
      console.log("Calculating price for freestyle product");
      let quantityStr = $(this).val().replace(/\D/g, ""); // Only numbers
      if (quantityStr.length > 6) {
        quantityStr = quantityStr.slice(0, 6); // Limit to 6 characters
      }
      if (quantityStr === "" || quantityStr === "0") {
        quantityStr = "1";
      }
      let quantity = parseInt(quantityStr);

      let rate = parseFloat(
        $(this)
          .closest(".product-custom-quantity-wraper")
          .find(".freestyle-rate-number-input")
          .val()
      );

      // Check if rate is a valid number
      if (isNaN(rate)) {
        rate = 0;
      }

      let total = rate * quantity;

      $(this).val(quantityStr); // Update the input value
      $(this)
        .closest(".product-custom-quantity-wraper")
        .find(".item-total-number")
        .text(total.toFixed(2));
      $(this)
        .closest(".product-custom-quantity-wraper")
        .find(".item_total_price")
        .val(total);
    });
  }

  // Bind the input event for .freestyle-custom-quantity elements to update the total dynamically
  $(".freestyle-custom-quantity").on("input", function () {
    freestylePriceCalc();
  });

  // Initial calculation on page load
  freestylePriceCalc();

  window.deleteProductTransient = function () {
    if (confirm("Are you sure you want to delete the product transient?")) {
      $.ajax({
        url: alarnd_create_order_vars.ajax_url,
        method: "POST",
        data: {
          action: "delete_product_transient",
          nonce: alarnd_create_order_vars.nonce,
        },
        success: function (response) {
          if (response.success) {
            alert("Product transient deleted.");
            location.reload();
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

  // New variable product code
  function initializeVariableProduct() {
    $(".product-variable-quantity-wrapper").each(function () {
      const wrapper = $(this);
      const quantitySelect = wrapper.find(".variable-quantity");
      const hasSize = quantitySelect.data("has-size") === true;
      const sizeInputs = wrapper.find('input[name="product_size"]');
      const customPriceInput = wrapper.find(".variableItem-total-number-input");

      function updatePrice() {
        let totalPrice;

        // Check if the custom price input has a value
        const customPrice = customPriceInput.val();
        if (customPrice && !isNaN(customPrice)) {
          totalPrice = parseFloat(customPrice);
        } else {
          // Fallback to the selected option's price
          const selectedOption = quantitySelect.find("option:selected");
          totalPrice = parseFloat(selectedOption.data("amount"));
        }

        // Update the display
        wrapper.find(".item-total-number").text(totalPrice.toFixed(2));
        wrapper.find(".item_total_price").val(totalPrice);
      }

      function updateQuantityOptions(steps) {
        quantitySelect.empty();
        steps.forEach((step) => {
          const option = $("<option>", {
            value: step.quantity || step.name,
            "data-amount": step.quantity ? step.amount : step.steps[0].amount,
            "data-variation-id": step.variation_id
              ? step.variation_id
              : step.steps[0].variation_id,
            text: `${step.quantity || step.name}`,
          });
          quantitySelect.append(option);
        });
      }

      if (hasSize) {
        sizeInputs.on("change", function () {
          const steps = $(this).data("steps");
          updateQuantityOptions(steps);
          updatePrice();
        });

        // Initialize with the first selected size
        sizeInputs.filter(":checked").trigger("change");
      } else {
        // For products without size, initialize quantity options directly
        const steps = quantitySelect.data("steps");
        if (steps) {
          updateQuantityOptions(steps);
        } else {
          console.error("No steps data found for product without size");
        }
      }

      quantitySelect.on("change", updatePrice);

      // Listen to custom price input changes
      customPriceInput.on("input", updatePrice);

      // Initial price update
      updatePrice();
    });
  }

  // Initialize on page load
  initializeVariableProduct();

  // Re-initialize when a modal opens (if you're using a modal)
  $(document).on("mfpOpen", initializeVariableProduct);

  // Handle Add to Cart button click for variable products
  $(".variable_add_to_cart_button").on("click", function (e) {
    e.preventDefault();
    const wrapper = $(this).closest(".product-variable-quantity-wrapper");
    const productId = $(this).val();
    const productName = wrapper
      .closest(".product-details-modal")
      .find(".modal-title")
      .text();
    const productThumbnail = wrapper
      .closest(".product-details-modal")
      .find(".product-thumb")
      .val();
    const sizeInput = wrapper.find('input[name="product_size"]:checked');
    const selectedSize = sizeInput.length ? sizeInput.next("label").text() : "";
    const selectedQuantity = wrapper.find(".variable-quantity").val();
    const subTotalPrice = wrapper.find(".item_total_price").val();
    const artworkUrl = wrapper.find(".uploaded_file_path").val();
    const instructionNote = wrapper.find(".new_product_instruction_note").val();

    let artworkHTML = "";
    wrapper.find(".uploaded_artwork").each(function () {
      artworkHTML += $(this).prop("outerHTML");
    });

    if (isNaN(parseFloat(subTotalPrice))) {
      console.error("Total Price is NaN");
      return;
    }

    // Remove existing cart items with the same product ID
    $(".content-cart ul li")
      .filter(function () {
        return $(this).data("product-id") === productId;
      })
      .remove();

    // Add product details to cart
    var cartItem = `
      <li class="single-cart-item" data-product-id="${productId}">
          <button class="remove-cart-item">×</button>
          <img class="cart-item-thumb" src="${productThumbnail}" alt="${productName}" />
          <span class="cart-item-contents">
          <span class="product-name">${productName}</span>
          <span class="product-quantity">Unite: <span class="product-quantity-number">1</span></span>
          <span class="product-total-price-container">Items Subtotal: <span class="product-total-price">${subTotalPrice}</span>₪</span>
          <input type="hidden" name="product-total-price-incart" class="product-total-price-incart" value="${subTotalPrice}">
      `;
    if (selectedQuantity) {
      cartItem += `<span class="product-quantity-meta">Quantity: ${selectedQuantity}</span>`;
    }
    if (selectedSize) {
      cartItem += `<span class="product-size">Size: ${selectedSize}</span>`;
    }
    if (instructionNote) {
      cartItem += `<span class="product-instruction-note">Instruction Note: ${instructionNote}</span>`;
    }
    if (artworkUrl) {
      cartItem += `<span class="product-artwork">Artworks: ${artworkHTML}</span>`;
      cartItem += `<input type="hidden" name="product-artworks-incart" class="product-artworks-incart" value='${artworkUrl}'>`;
    }
    cartItem += `</span>`;
    cartItem += `</li>`;

    $(".content-cart ul").append(cartItem);

    // Update cart total
    if (typeof updateCartTotal === "function") updateCartTotal();
    if (typeof validateCheckout === "function") validateCheckout();

    // Close the modal (if you're using one)
    $.magnificPopup.close();
  });
});
