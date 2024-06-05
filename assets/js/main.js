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

  document
    .getElementById("send-proof-button")
    .addEventListener("click", function () {
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

      fetch(
        "https://artwork.lukpaluk.xyz/wp-json/artwork-review/v1/add-proof",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
        }
      )
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
  $("#addProductModal").magnificPopup({
    items: {
      src: "#add-item-modal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  $("#addNewItemButton").on("click", function () {
    const newItem = {
      product_id: $("#new_product_id").val(),
      quantity: $("#new_product_quantity").val(),
      alarnd_color: $("#new_product_color").val(),
      alarnd_size: $("#new_product_size").val(),
      allaround_art_pos: $("#new_product_art_pos").val(),
      allaround_instruction_note: $("#new_product_instruction_note").val(),
      order_id: allaround_vars.order_id,
    };

    $.ajax({
      url: "https://main.lukpaluk.xyz/wp-json/update-order/v1/add-item-to-order",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(newItem),
      success: function (response) {
        alert("Item added successfully");
        location.reload(); // Refresh the page to see the new item
      },
      error: function (xhr, status, error) {
        alert("Error: " + xhr.responseJSON.message);
      },
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
    console.log("Fetching products...");
    $.ajax({
      url: "https://main.lukpaluk.xyz/wp-json/alarnd-main/v1/products",
      method: "GET",
      success: function (response) {
        displayProductList(response);
      },
      error: function (xhr, status, error) {
        console.error("Error fetching products:", error);
      },
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
        )}' class="product-item">${product.name}</li>`
      );
    });

    $(".product-item").on("click", function () {
      const selectedProduct = $(this).data("id");
      const isCustomQuantity = $(this).data("custom-quantity");
      const isGroupQuantity = $(this).data("group-quantity");
      const colors = $(this).data("colors");
      const sizes = $(this).data("sizes");
      const artPositions = $(this).data("art-positions");

      $("#fetchProductList").val($(this).text());
      $("#fetchProductList").data("product-id", selectedProduct);
      $("#new_product_id").val(selectedProduct);

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
})(jQuery); /*End document ready*/
