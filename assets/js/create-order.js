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

    const unitRate = getGroupedItemRate(totalUnits, steps, regularPrice);
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
  $(".custom-quantity").on("input", function () {
    let quantityStr = $(this).val().replace(/\D/g, ""); // Only numbers
    if (quantityStr.length > 6) {
      quantityStr = quantityStr.slice(0, 6); // Limit to 6 characters
    }
    if (quantityStr === "" || quantityStr === "0") {
      quantityStr = "1";
    }
    let quantity = parseInt(quantityStr);

    if (quantity > 0) {
      $(this)
        .closest(".product-details-modal")
        .find(".single_add_to_cart_button")
        .attr("disabled", false);
    }

    let steps = $(this).data("steps"); // Get steps from data attribute

    if (typeof steps === "string") {
      steps = JSON.parse(steps);
    }

    let rate = getItemRate(quantity, steps);
    let total = rate * quantity;

    $(this).val(quantityStr); // Update the input value
    $(this)
      .closest(".product-details-modal")
      .find(".price-item span.item-rate-number")
      .text(rate);
    $(this)
      .closest(".product-details-modal")
      .find(".price-total span.item-total-number")
      .text(total);
    $(this)
      .closest(".product-details-modal")
      .find(".item_total_price")
      .val(total);
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

  // Function to reset input fields in the modal
  // function resetModalFields(modal) {
  //   modal.find("input[type='text']").val("");
  //   modal.find("input[type='file']").val("");
  //   modal.find(".item_unit_rate").val("");
  //   modal.find("input[type='radio']").prop("checked", false);
  //   modal
  //     .find(
  //       ".item-total-number, .item-rate-number, .total_units, .group_unite_price"
  //     )
  //     .text("0");
  //   modal
  //     .find(".item_total_price, .item_total_units, .item_unit_rate")
  //     .val("0");
  //   modal.find(".single_add_to_cart_button").attr("disabled", true);
  //   modal.find(".grouped_product_add_to_cart").attr("disabled", true);
  //   modal.find(".uploaded_artwork").remove();
  //   modal.find(".new_product_artwork").show();
  // }

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

    let orderType = $("#order_type").val();

    // Collect billing and shipping information
    const billing = {
      first_name: $("#billing_first_name").val(),
      last_name: $("#billing_last_name").val(),
      address_1: $("#billing_address_1").val(),
      postcode: $("#billing_postcode").val(),
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

      const metaData = [];
      if (color) metaData.push({ key: "Color", value: color });
      if (size) metaData.push({ key: "Size", value: size });
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
      postcode: billing.postcode,
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
          createOrderPost(response.data, orderType);

          $(".sitewide_spinner").removeClass("loading");
        } else {
          $(".sitewide_spinner").removeClass("loading");
          alert("Error creating order: " + response.data);
        }
      },
      error: function (xhr, status, error) {
        $(".sitewide_spinner").removeClass("loading");
        console.error("AJAX Error:", error);
        alert("An error occurred while creating the order. Please try again.");
      },
    });
  });

  function createOrderPost(orderData, orderType) {
    let root_domain = alarnd_create_order_vars.redirecturl;

    let jwtToken = "";

    if (root_domain.includes(".test")) {
      // Webhook URL for test environment
      jwtToken =
        "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL29yZGVybWFuYWdlLnRlc3QiLCJpYXQiOjE3MjI0MjQzOTUsIm5iZiI6MTcyMjQyNDM5NSwiZXhwIjoxNzIzMDI5MTk1LCJkYXRhIjp7InVzZXIiOnsiaWQiOiIxIn19fQ.GqYWpHlTYURZtx45zsV6TZV6-FXK7IJHvmHFPXJPwIQ";
    } else {
      // Webhook URL for production environment
      jwtToken =
        "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL29tLmx1a3BhbHVrLnh5eiIsImlhdCI6MTcyMTgyMzE4NywibmJmIjoxNzIxODIzMTg3LCJleHAiOjE3MjI0Mjc5ODcsImRhdGEiOnsidXNlciI6eyJpZCI6IjEifX19.r3xwKH_5RAY_JJZ-njLl7Cse_tvS8b7ng4ShUMG-1sg";
    }

    orderData.order_type = orderType;

    $.ajax({
      url: `${root_domain}/wp-json/manage-order/v1/create`,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(orderData),
      headers: {
        Authorization: "Bearer " + jwtToken,
      },
      success: function (response) {
        console.log("Order post created successfully:", response);
        alert("New Order created successfully!");
        location.reload();
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
            $("#billing-form #billing_postcode").val(client.postcode);
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
        .val(total.toFixed(2));
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
});
