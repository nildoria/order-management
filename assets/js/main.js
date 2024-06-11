(function ($) {
  ("use strict");

  // ********** Order Management Scripts Start **********//
  // ********** Mockup Image Upload Post Request **********//
  var fileInputs = document.querySelectorAll(".file-input__input");

  fileInputs.forEach(function (input) {
    input.addEventListener("change", function (event) {
      var file = event.target.files[0];
      var productId = input
        .getAttribute("name")
        .replace("file-input[", "")
        .replace("]", "");
      var version = input.getAttribute("data-version");
      var orderId = document.querySelector('input[name="order_id"]').value;

      var formData = new FormData();
      formData.append("file", file);
      formData.append("order_id", orderId);
      formData.append("product_id", productId);
      formData.append("version", version);

      // Display a preview of the selected image
      var reader = new FileReader();
      reader.onload = function (e) {
        var mockupImage =
          "<img src='" + e.target.result + "' alt='Mockup Image' />";
        var mockupColumn = input.closest(".item_mockup_column");
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

      uploadFile(formData, input); // Pass the input element here
    });
  });

  function uploadFile(formData, input) {
    // Accept the input element as a parameter
    fetch("/wp-content/themes/manage-order/upload.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.text();
      })
      .then((data) => {
        console.log(data);
      })
      .catch((error) => {
        console.error("There was a problem with your fetch operation:", error);
        alert("There was a problem uploading file");
        location.reload();
      });
  }

  // ********** Send Mockup to ArtWork Post Request **********//

  $("#send-proof-button").on("click", function () {
    var orderId = allaround_vars.order_id;
    var orderNumber = allaround_vars.order_number;
    var customerName = allaround_vars.customer_name;
    var customerEmail = allaround_vars.customer_email;
    var commentText = "Your comment here"; // Customize as needed
    var proofStatus = "Mockup V1 Sent"; // Customize as needed

    var imageUrls = [];
    document
      .querySelectorAll('.item_mockup_column input[type="hidden"]')
      .forEach(function (input) {
        imageUrls.push(input.value);
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

    fetch("https://artwork.lukpaluk.xyz/wp-json/artwork-review/v1/add-proof", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
          alert("There was an error sending the proof: " + data.message);
        } else {
          alert("Proof sent successfully!");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("There was an error sending the proof.");
      });
  });

  // ********** Add New Item to the Order **********//
  $("#new_product_artwork").on("change", function (event) {
    var file = event.target.files[0];
    if (file) {
      var formData = new FormData();
      formData.append("file", file);

      fetch("/wp-content/themes/manage-order/includes/php/artwork-upload.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Store the file path in a hidden input field
            $("#uploaded_file_path").val(data.file_path);
            console.log("File uploaded successfully:", data.file_path);
          } else {
            alert("Failed to upload file: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
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

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          alert("Item added successfully");
          location.reload(); // Refresh the page to see the new item
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

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          alert("Item duplicated successfully.");
          location.reload(); // Refresh the page to see the new item
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

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          alert("Item deleted successfully.");
          location.reload(); // Refresh the page to see the new item
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
  $("#fetchProductList").on("focus", function () {
    if (!$(this).data("loaded")) {
      fetchProducts();
      $(this).data("loaded", true);
    }
    $("#productDropdown").slideDown();
  });

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
      $("#fetchProductList").after(productDropdown);
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
      } else {
        $("#fetchProductList").val(productName);
        $("#fetchProductList").data("product-id", selectedProduct);
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
      product_name: productHTML,
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
        <p><strong>Product ID:</strong>${product.product_name}</p>
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
})(jQuery); /*End document ready*/
