(function ($) {
  ("use strict");

  // ********** Order Management Scripts Start **********//
  // ********** Mockup Image Upload Post Request **********//

  // ********** Send Mockup to ArtWork Post Request **********//

  $("#send-proof-button").on("click", function () {
    var orderId = allaround_vars.order_id;
    var orderNumber = allaround_vars.order_number;
    var customerName = allaround_vars.customer_name;
    var customerEmail = allaround_vars.customer_email;
    var commentText = "Your comment here"; // Customize as needed

    // Select the first <tr> element
    let firstTr = document.querySelector("tbody > tr");

    // Get all <td> elements with the 'item_mockup_column' class within the first <tr>
    let itemMockupColumns = firstTr.querySelectorAll(".item_mockup_column");

    // Select the last element in the NodeList
    let lastItemMockupColumn = itemMockupColumns[itemMockupColumns.length - 1];

    // Get the value of the 'data-version_number' attribute from the last <td>
    let version = lastItemMockupColumn.getAttribute("data-version_number");

    // set version value 1 if not set
    if (version == null) {
      version = 1;
    }

    var proofStatus = "Mockup V" + version + " Sent"; // Customize as needed
    console.log("proofStatus", proofStatus);

    var imageUrls = [];

    document.querySelectorAll("tbody > tr").forEach(function (row) {
      let currentVersion = version;
      let imageUrl = "";

      while (currentVersion > 0) {
        let column = row.querySelector(
          '.item_mockup_column[data-version_number="' + currentVersion + '"]'
        );
        if (column) {
          let input = column.querySelector('input[type="hidden"]');
          if (input && input.value.trim() !== "") {
            imageUrl = input.value.trim();
            break;
          }
        }
        currentVersion--;
      }

      if (imageUrl !== "") {
        imageUrls.push(imageUrl);
      }
    });

    var imageUrlString = imageUrls.join(",");
    console.log(imageUrlString); // Outputs: url1,url2,url3...

    var data = {
      comment_text: commentText,
      order_id: orderId,
      order_number: orderNumber,
      image_urls: imageUrlString,
      proof_status: proofStatus,
      customer_name: customerName,
      customer_email: customerEmail,
    };

    var requestData = {
      action: "send_proof_version",
      post_id: allaround_vars.post_id,
      version: version,
    };

    function handleResponse(data) {
      if (data.error) {
        alert("There was an error sending the proof: " + data.message);
      } else {
        alert("Proof sent successfully!");
      }
    }

    fetch("https://artwork.lukpaluk.xyz/wp-json/artwork-review/v1/add-proof", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => {
        ml_send_ajax(requestData, handleResponse);
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("There was an error sending the proof.");
      });
  });

  // ********** Add New Item to the Order **********//
  $("#new_product_artwork").on("change", function (event) {
    var files = event.target.files;
    var uploadedFiles = [];

    if (files.length > 0) {
      $("#addNewItemButton").addClass("ml_loading").prop("disabled", true);
      var formData = new FormData();
      for (var i = 0; i < files.length; i++) {
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
            $("#uploaded_file_path").val(JSON.stringify(uploadedFiles));
            console.log("Files uploaded successfully:", data.file_paths);
          } else {
            alert("Failed to upload files: " + data.message);
          }
          $("#addNewItemButton")
            .removeClass("ml_loading")
            .prop("disabled", false);
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
          $("#addNewItemButton")
            .removeClass("ml_loading")
            .prop("disabled", false);
        });
    }
  });

  $("#addProductModal").magnificPopup({
    items: {
      src: "#add-item-modal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  $("#addNewItemButton").on("click", function (event) {
    event.preventDefault();
    const newItem = {
      product_id: $("#new_product_id").val(),
      quantity: $("#new_product_quantity").val(),
      alarnd_color: $("#new_product_color").val(),
      alarnd_size: $("#new_product_size").val(),
      allaround_art_pos: $("#new_product_art_pos").val(),
      allaround_instruction_note: $("#new_product_instruction_note").val(),
      alarnd_artwork: $("#uploaded_file_path").val(),
      order_id: allaround_vars.order_id,
      nonce: allaround_vars.nonce,
    };

    let order_domain = allaround_vars.order_domain;

    var requestData = {
      action: "update_order_transient",
      order_id: allaround_vars.order_id,
    };

    function handleResponse(response) {
      alert("Item added successfully");
      location.reload(); // Refresh the page to see the new item
    }

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          ml_send_ajax(requestData, handleResponse);
        } else {
          return response.json();
        }
      })
      .then((data) => {
        if (data) {
          alert("Failed to add item: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error: " + error.message);
      });
  });

  function ml_send_ajax(data, callback) {
    $.ajax({
      type: "POST",
      url: allaround_vars.ajax_url,
      data: data,
      dataType: "json",
      success: function (response) {
        if (typeof callback === "function") {
          callback(response);
        } else {
          console.log("No callback function provided. Response:", response);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX request failed:", status, error);
        if (typeof callback === "function") {
          callback({ success: false, data: error });
        }
      },
    });
  }

  // keep #addNewItemButton button disabled and opacity 0.5 if #new_product_id is empty
  $("#new_product_id").on("change", function () {
    if ($(this).val() === "") {
      $("#addNewItemButton").prop("disabled", true).css("opacity", 0.5);
    } else {
      $("#addNewItemButton").prop("disabled", false).css("opacity", 1);
    }
  });

  // ********** Duplicate Order Item **********//
  $(document).on("click", ".om_duplicate_item", function () {
    var order_id = allaround_vars.order_id;
    var item_id = $(this).siblings('input[name="item_id"]').val();
    var order_domain = allaround_vars.order_domain;

    // Debugging
    console.log("Order ID:", order_id);
    console.log("Item ID:", item_id);
    console.log("Order Domain:", order_domain);

    var newItem = {
      order_id: order_id,
      item_id: item_id,
      method: "duplicateItem",
      nonce: allaround_vars.nonce,
    };

    var requestData = {
      action: "update_order_transient",
      order_id: order_id,
    };

    function handleResponse(response) {
      alert("Item duplicated successfully");
      location.reload(); // Refresh the page to see the new item
    }

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          ml_send_ajax(requestData, handleResponse);
        } else {
          return response.json();
        }
      })
      .then((data) => {
        if (data) {
          alert("Failed to duplicate item: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred: " + error.message);
      });
  });

  // ********** Delete Order Item **********//
  $(document).on("click", ".om_delete_item", function () {
    var order_id = allaround_vars.order_id;
    var item_id = $(this).siblings('input[name="item_id"]').val();
    var order_domain = allaround_vars.order_domain;

    // Debugging
    console.log("Order ID:", order_id);
    console.log("Item ID:", item_id);
    console.log("Order Domain:", order_domain);

    var newItem = {
      order_id: order_id,
      item_id: item_id,
      method: "deleteItem",
      nonce: allaround_vars.nonce,
    };

    var requestData = {
      action: "update_order_transient",
      order_id: order_id,
    };

    function handleResponse(response) {
      alert("Item deleted successfully");
      location.reload(); // Refresh the page to see the new item
    }

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          ml_send_ajax(requestData, handleResponse);
        } else {
          return response.json();
        }
      })
      .then((data) => {
        if (data) {
          alert("Failed to delete item: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred: " + error.message);
      });
  });

  // ********** Fetch Products from Main Site **********//

  function fetchProducts() {
    let order_domain = allaround_vars.order_domain;
    console.log(order_domain);

    fetch(`${order_domain}/wp-json/alarnd-main/v1/products`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }
        return response.json();
      })
      .then((data) => {
        displayProductList(data);
      })
      .catch((error) => {
        console.error("Error fetching products:", error);
      });
  }

  function displayProductList(products) {
    let productDropdown = $("#productDropdown");
    if (productDropdown.length === 0) {
      productDropdown = $(
        '<ul id="productDropdown" class="product-dropdown"></ul>'
      );
      // $("#fetchProductList").after(productDropdown);
    }
    productDropdown.empty();
    products.forEach((product) => {
      const productThumbnail = product.thumbnail ? product.thumbnail : "";
      productDropdown.append(
        `<li data-id="${product.id}" data-custom-quantity="${
          product.is_custom_quantity
        }" data-group-quantity="${
          product.is_group_quantity
        }" data-colors='${JSON.stringify(
          product.colors
        )}' data-sizes='${JSON.stringify(
          product.sizes
        )}' data-art-positions='${JSON.stringify(
          product.art_positions
        )}' class="product-item">
        <img src="${productThumbnail}" alt="${
          product.name
        }" class="product-thumb">
        ${product.name}
        </li>`
      );
    });

    $(".product-item").on("click", function () {
      const selectedProduct = $(this).data("id");
      const isCustomQuantity = $(this).data("custom-quantity");
      const isGroupQuantity = $(this).data("group-quantity");
      const colors = $(this).data("colors");
      const sizes = $(this).data("sizes");
      const artPositions = $(this).data("art-positions");

      const productThumbnail = $(this).find(".product-thumb").attr("src");
      const productName = $(this).text().trim();

      if ($("#selectedProductDisplay").length) {
        $("#selectedProductDisplay").html(
          `<img src="${productThumbnail}" alt="${productName}" class="product-thumb">${productName}`
        );
        $("#selectedProductDisplay").data("product-id", selectedProduct);

        $("#addNewItemButton")
          .addClass("om_add_item_selected")
          .prop("disabled", false);
      }
      $("#new_product_id").val(selectedProduct);

      // Insert the product name and thumbnail into a div or span

      if (isCustomQuantity) {
        populateCustomQuantityColors("#new_product_color", colors);
        hideSizeDropdown("#new_product_size");
        hideArtPositionsDropdown("#new_product_art_pos");
      } else if (isGroupQuantity) {
        populateGroupQuantityColors("#new_product_color", colors);
        populateSizeDropdown("#new_product_size", sizes);
        populateArtPositionsDropdown("#new_product_art_pos", artPositions);
      } else {
        hideSizeDropdown("#new_product_size");
        hideArtPositionsDropdown("#new_product_art_pos");
      }

      $("#productDropdown").slideUp();
    });
  }

  function populateCustomQuantityColors(selector, colors) {
    const dropdown = $(selector);
    dropdown.empty();
    dropdown.append('<option value="">Select Color</option>');
    if (Array.isArray(colors)) {
      colors.forEach((item) => {
        dropdown.append(`<option value="${item.color}">${item.color}</option>`);
      });
    }
  }

  function populateGroupQuantityColors(selector, colors) {
    const dropdown = $(selector);
    dropdown.empty();
    dropdown.append('<option value="">Select Color</option>');
    if (Array.isArray(colors)) {
      colors.forEach((item) => {
        dropdown.append(
          `<option value="${item.title}" style="background-color: ${item.color_hex_code};">${item.title}</option>`
        );
      });
    }
  }

  function populateSizeDropdown(selector, sizes) {
    const dropdown = $(selector);
    dropdown.empty();
    if (
      !sizes ||
      (typeof sizes === "object" && Object.keys(sizes).length === 0)
    ) {
      dropdown.hide();
      return;
    }

    dropdown.closest(".form-group").show();
    dropdown.show();
    dropdown.append('<option value="">Select Size</option>');
    if (sizes && typeof sizes === "object") {
      Object.values(sizes).forEach((item) => {
        dropdown.append(`<option value="${item}">${item}</option>`);
      });
    }
  }

  function populateArtPositionsDropdown(selector, artPositions) {
    const dropdown = $(selector);
    dropdown.empty();
    if (!artPositions || !Array.isArray(artPositions)) {
      dropdown.hide();
      return;
    }

    dropdown.closest(".form-group").show();
    dropdown.show();
    dropdown.append('<option value="">Select Art Position</option>');
    artPositions.forEach((item) => {
      dropdown.append(`<option value="${item.title}">${item.title}</option>`);
    });
  }

  function hideSizeDropdown(selector) {
    const dropdown = $(selector);
    dropdown.empty();
    dropdown.hide();
    dropdown.closest(".form-group").hide();
  }

  function hideArtPositionsDropdown(selector) {
    const dropdown = $(selector);
    dropdown.empty();
    dropdown.hide();
    dropdown.closest(".form-group").hide();
  }

  // ********** Add Item to create Order **********//

  // ********** Add New Order **********//
  var products = [];

  // $("#fetchAddProductList")
  //   .on("focus", function () {
  //     if (!$(this).data("loaded")) {
  //       fetchAddProducts();
  //       $(this).data("loaded", true);
  //     }
  //     $("#productDropdown").slideDown();
  //   })
  //   .on("focusout", function () {
  //     $("#productDropdown").slideUp();
  //   });

  $("#selectedProductDisplay").on("click", function () {
    if (!$(this).data("loaded")) {
      fetchAddProducts();
      $(this).data("loaded", true);
    }
    $("#productDropdown").slideDown();
  });

  $(document).on("mouseup", function (e) {
    var container = $("#selectedProductDisplay");

    // if the target of the click isn't the container nor a descendant of the container
    if (!container.is(e.target) && container.has(e.target).length === 0) {
      $("#productDropdown").slideUp();
    }
  });

  function fetchAddProducts() {
    let order_domain = "https://allaround.test";

    fetch(`${order_domain}/wp-json/alarnd-main/v1/products`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }
        return response.json();
      })
      .then((data) => {
        displayProductList(data);
      })
      .catch((error) => {
        console.error("Error fetching products:", error);
      });
  }

  // Handle adding product to line items
  $("#addProductButton").on("click", function () {
    const productId = $("#new_product_id").val();
    const productHTML = $("#selectedProductDisplay").html();
    const quantity = $("#new_product_quantity").val();
    const color = $("#new_product_color").val();
    const size = $("#new_product_size").val();
    const artPos = $("#new_product_art_pos").val();
    const instructionNote = $("#new_product_instruction_note").val();
    const alarnd_artwork = $("#uploaded_file_path").val();

    if (!productId || !quantity) {
      alert("Please select a product and specify quantity.");
      return;
    }

    const lineItem = {
      product_id: productId,
      product_html: productHTML,
      quantity: quantity,
      meta_data: [
        {
          key: "Color",
          value: color,
        },
        {
          key: "Size",
          value: size,
        },
        {
          key: "Size",
          value: size,
        },
        {
          key: "Art Position",
          value: artPos,
        },
        {
          key: "Instruction Note",
          value: instructionNote,
        },
        {
          key: "Attachment",
          value: alarnd_artwork,
        },
      ],
    };

    products.push(lineItem);
    alert("Product added to order.");
    $("#line_items").val(JSON.stringify(products));
    displayAddedProducts();
  });

  // Function to display the added products
  function displayAddedProducts() {
    let lineItemsAddedOm = $("#line_items_added_om");
    lineItemsAddedOm.empty();
    products.forEach((product, index) => {
      const filteredMetaData = product.meta_data.filter(
        (meta) => meta.value !== null && meta.value !== ""
      );
      const productHtml = `
      <div class="om__line-item">
        <p><strong>Product ID:</strong>${product.product_html}</p>
        <p><strong>Quantity:</strong> ${product.quantity}</p>
        <ul>
          ${filteredMetaData
            .map(
              (meta) => `<li><strong>${meta.key}:</strong> ${meta.value}</li>`
            )
            .join("")}
        </ul>
        <button class="remove-product" data-index="${index}">Remove</button>
      </div>
      <hr>
    `;
      lineItemsAddedOm.append(productHtml);
    });

    // Add event listener to remove buttons
    $(".remove-product").on("click", function () {
      const index = $(this).data("index");
      products.splice(index, 1);
      displayAddedProducts();
      $("#line_items").val(JSON.stringify(products));
    });
  }

  // Handle form submission via AJAX
  $("#orderForm").on("submit", function (event) {
    event.preventDefault();

    const shippingMethodId = $("#shipping-method-list").val();
    const shippingMethodTitle = $(
      "#shipping-method-list option:selected"
    ).text();

    const formData = {
      action: "create_order",
      security: allaround_vars.nonce,
      first_name: $("#firstName").val(),
      last_name: $("#lastName").val(),
      company: $("#company").val(),
      address_1: $("#address").val(),
      city: $("#city").val(),
      country: $("#country").val(),
      email: $("#email").val(),
      phone: $("#phone").val(),
      line_items: $("#line_items").val(),
      shipping_method: shippingMethodId,
      shipping_method_title: shippingMethodTitle,
    };

    $.post(allaround_vars.ajax_url, formData, function (response) {
      if (response.success) {
        alert("Order created successfully.");
        // Create the order post
        createOrderPost(response.data);
        console.log(response.data);
      } else {
        alert(response.data);
      }
    });
  });

  function createOrderPost(orderData) {
    $.ajax({
      url: `${allaround_vars.redirecturl}/wp-json/manage-order/v1/create`,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(orderData),
      success: function (response) {
        alert("Order post created successfully. Post ID: " + response);
        // location.reload();
      },
      error: function (xhr, status, error) {
        alert("Error creating order post: " + error);
      },
    });
  }

  // ********** Shipping Method Update Ajax **********//
  // on submit of #shipping-method-form form
  $("#shipping-method-form").on("submit", function (event) {
    event.preventDefault();

    var order_id = allaround_vars.order_id;
    var order_domain = allaround_vars.order_domain;
    var shipping_method_id = document.querySelector(
      'select[name="shipping_method"]'
    ).value;
    var shipping_method_title = document.querySelector(
      'select[name="shipping_method"] option:checked'
    ).text;
    var nonce = allaround_vars.nonce;

    fetch(allaround_vars.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "update_shipping_method",
        order_id: order_id,
        shipping_method: shipping_method_id,
        shipping_method_title: shipping_method_title,
        order_domain: order_domain,
        nonce: nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Shipping method updated successfully.");
          location.reload();
        } else {
          alert("Failed to update shipping method: " + data.data);
        }
      })
      .catch((error) => {
        alert("An error occurred: " + error);
      });
  });

  // ********** Edit Shipping form open **********//
  // on #shipping-method-list select option change show .om_shipping_submit button
  $("#shipping-method-list").on("change", function () {
    $(".om_shipping_submit").show();
  });

  // ********** Add Mockup Column **********//
  $(document).on("click", "#addMockupButton", function (e) {
    e.preventDefault();
    const current = $(this);
    var table = $("#tableMain");
    var sendProofButton = $("#send-proof-button");
    var post_id = $('input[name="post_id"]').val();

    var headerRow = table.find("thead tr");
    var newMockupIndex = headerRow.find("th").length - 3 + 1; // Calculate the new mockup index
    var newMockupTh = $("<th>", { class: "head" }).html(
      "<strong>Mockups V" + newMockupIndex + "</strong>"
    );
    headerRow.append(newMockupTh);

    // add loading snipper
    current.addClass("ml_loading");
    current.prop("disabled", true);
    sendProofButton.prop("disabled", true);

    table.find("tbody tr").each(function () {
      const current = $(this);
      const product_id = current.data("product_id");

      var newMockupTd = $("<td>", {
        class: "item_mockup_column",
        "data-version_number": newMockupIndex,
      }).html(
        '<div class="lds-spinner-wrap"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>' +
          '<input type="hidden" class="hidden_mockup_url" name="mockup-image-v' +
          newMockupIndex +
          '" value="">' +
          '<div class="mockup-image">Select Mockup Image</div>' +
          '<input class="file-input__input" name="file-input[' +
          product_id +
          ']" id="file-input-' +
          product_id +
          "-v" +
          newMockupIndex +
          '" data-version="V' +
          newMockupIndex +
          '" type="file" placeholder="Upload Mockup">' +
          '<label class="file-input__label" for="file-input-' +
          product_id +
          "-v" +
          newMockupIndex +
          '">' +
          '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">' +
          '<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>' +
          "</svg>" +
          "<span>Upload file</span></label>"
      );
      $(this).append(newMockupTd);
    });

    // After adding the new <td> elements, scroll to the rightmost position
    var $container = $(".order_manage_tab_wrapper"); // Adjust the selector to your actual container
    var $lastTd = $("td.item_mockup_column:last");

    if ($lastTd.length) {
      // Calculate the offset of the last <td> element from the left edge of the container
      var targetOffset = $lastTd.position().left + $container.scrollLeft();

      // Scroll the container to the last <td> element's position
      $container.animate({ scrollLeft: targetOffset }, 500);
    } else {
      console.error("Target element not found");
    }

    // add loading snipper
    current.removeClass("ml_loading");
  });

  $(window).on("load", function () {});
})(jQuery); /*End document ready*/

// ********** Mockup Image Upload Post Request **********//
document.addEventListener("DOMContentLoaded", function () {
  // Use a parent element that exists when the DOM is loaded
  document.body.addEventListener("change", function (event) {
    if (event.target.classList.contains("file-input__input")) {
      var input = event.target;
      var file = input.files[0];
      var version = input.getAttribute("data-version");
      var orderId = document.querySelector('input[name="order_id"]').value;
      var postId = document.querySelector('input[name="post_id"]').value;

      var mockupColumn = input.closest(".item_mockup_column");
      var spinner = mockupColumn.querySelector(".lds-spinner-wrap");
      spinner.style.display = "flex";

      const productId = mockupColumn
        .closest("tr")
        .getAttribute("data-product_id");

      console.log(
        "Product ID:",
        productId,
        "Order ID:",
        orderId,
        "Version:",
        version
      );

      var formData = new FormData();
      formData.append("file", file);
      formData.append("order_id", orderId);
      formData.append("post_id", postId);
      formData.append("product_id", productId);
      formData.append("version", version);

      // Display a preview of the selected image
      var reader = new FileReader();
      reader.onload = function (e) {
        var mockupImage =
          "<img src='" + e.target.result + "' alt='Mockup Image' />";
        var mockupImageContainer = mockupColumn.querySelector(".mockup-image");

        if (mockupImageContainer) {
          mockupImageContainer.innerHTML = mockupImage;
        } else {
          mockupImageContainer = document.createElement("div");
          mockupImageContainer.className = "mockup-image";
          mockupImageContainer.innerHTML = mockupImage;
          mockupColumn.appendChild(mockupImageContainer);
        }
      };
      reader.readAsDataURL(file); // Read the file as a data URL for preview

      uploadFile(formData, input, version); // Pass the input element here
    }
  });

  function uploadFile(formData, input, version) {
    var mockupColumn = input.closest(".item_mockup_column");
    // find input type="hidden" with name="mockup-image-v"+version
    var mockupInput = mockupColumn.querySelector(
      'input[type="hidden"].hidden_mockup_url'
    );
    var spinner = mockupColumn.querySelector(".lds-spinner-wrap");

    var addMockupButton = document.querySelector("#addMockupButton");
    var sendProofButton = document.querySelector("#send-proof-button");

    // Accept the input element as a parameter
    fetch(allaround_vars.fileupload_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success && data.file_path) {
          mockupInput.value = data.file_path;

          if (addMockupButton) {
            addMockupButton.removeAttribute("disabled");
          }
          if (sendProofButton) {
            sendProofButton.removeAttribute("disabled");
          }
        }
        spinner.style.display = "none";
      })
      .catch((error) => {
        spinner.style.display = "none";
        console.error("There was a problem with your fetch operation:", error);
        alert("There was a problem uploading file");
        // location.reload();
      });
  }
});
