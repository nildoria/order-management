(function ($) {
  ("use strict");

  // ********** Order Management Scripts Start **********//


  

  

  // ********** Send Mockup to ArtWork Post Request **********//

  document
    .getElementById("send-proof-button")
    .addEventListener("click", function () {
      var orderId = allaround_vars.order_id;
      var orderNumber = allaround_vars.order_number;
      var customerName = allaround_vars.customer_name;
      var customerEmail = allaround_vars.customer_email;
      var commentText = "Your comment here"; // Customize as needed

      // Select the first <tr> element
      let firstTr = document.querySelector('tbody > tr');

      // Get all <td> elements with the 'item_mockup_column' class within the first <tr>
      let itemMockupColumns = firstTr.querySelectorAll('.item_mockup_column');

      // Select the last element in the NodeList
      let lastItemMockupColumn = itemMockupColumns[itemMockupColumns.length - 1];

      // Get the value of the 'data-version_number' attribute from the last <td>
      let version = lastItemMockupColumn.getAttribute('data-version_number');

      // set version value 1 if not set
      if(version == null){
        version = 1;
      }


      var proofStatus = "Mockup V"+version+" Sent"; // Customize as needed
      console.log('proofStatus', proofStatus);

      var imageUrls = [];
      document
        .querySelectorAll('.item_mockup_column input[type="hidden"]')
        .forEach(function (input) {
          // Check if the parent element (td) of the input has the desired attribute
          if (input.closest('.item_mockup_column').getAttribute('data-version_number') === version.toString()) {
            imageUrls.push(input.value);
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

      console.log(data);
      return;

      fetch(
        "http://mlimon.io/artwork/wp-json/artwork-review/v1/add-proof",
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





  $(document).on('click', '#addMockupButton', function(e) {
    e.preventDefault();
    const current = $(this);
    var table = $('#tableMain');
    var sendProofButton = $('#send-proof-button');
    var post_id = $('input[name="post_id"]').val();

    var headerRow = table.find('thead tr');
    var newMockupIndex = headerRow.find('th').length - 3 + 1; // Calculate the new mockup index
    var newMockupTh = $('<th>', { class: 'head' }).html('<strong>Mockups V' + newMockupIndex + '</strong>');
    headerRow.append(newMockupTh);

    // add loading snipper
    current.addClass('ml_loading');
    current.prop('disabled', true);
    sendProofButton.prop('disabled', true);

    table.find('tbody tr').each(function() {
        const current = $(this);
        const product_id = current.data('product_id');

        var newMockupTd = $('<td>', { class: 'item_mockup_column', 'data-version_number': newMockupIndex }).html(
            '<div class="lds-spinner-wrap"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>' +
            '<input type="hidden" class="hidden_mockup_url" name="mockup-image-v' + newMockupIndex + '" value="">' +
            '<div class="mockup-image">Select Mockup Image</div>' +
            '<input class="file-input__input" name="file-input['+product_id+']" id="file-input-'+ product_id +'-v'+newMockupIndex+'" data-version="V' + newMockupIndex + '" type="file" placeholder="Upload Mockup">' +
            '<label class="file-input__label" for="file-input-'+ product_id +'-v'+newMockupIndex+'">' +
            '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">' +
            '<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>' +
            '</svg>' +
            '<span>Upload file</span></label>'
        );
        $(this).append(newMockupTd);
    });

    // After adding the new <td> elements, scroll to the rightmost position
    var $container = $('.order_manage_tab_wrapper'); // Adjust the selector to your actual container
    var $lastTd = $('td.item_mockup_column:last');

    if ($lastTd.length) {
        // Calculate the offset of the last <td> element from the left edge of the container
        var targetOffset = $lastTd.position().left + $container.scrollLeft();

        // Scroll the container to the last <td> element's position
        $container.animate({ scrollLeft: targetOffset }, 500);
    } else {
        console.error('Target element not found');
    }

    // add loading snipper
    current.removeClass('ml_loading');
});











})(jQuery); /*End document ready*/

  // ********** Mockup Image Upload Post Request **********//
document.addEventListener("DOMContentLoaded", function() {
  // Use a parent element that exists when the DOM is loaded
  document.body.addEventListener("change", function(event) {
    if (event.target.classList.contains("file-input__input")) {
      var input = event.target;
      var file = input.files[0];
      var version = input.getAttribute("data-version");
      var orderId = document.querySelector('input[name="order_id"]').value;
      var postId = document.querySelector('input[name="post_id"]').value;

      var mockupColumn = input.closest(".item_mockup_column");
      var spinner = mockupColumn.querySelector(".lds-spinner-wrap");
      spinner.style.display = "flex";

      const productId = mockupColumn.closest("tr").getAttribute("data-product_id");

      console.log("Product ID:", productId, "Order ID:", orderId, "Version:", version);

      var formData = new FormData();
      formData.append("file", file);
      formData.append("order_id", orderId);
      formData.append("post_id", postId);
      formData.append("product_id", productId);
      formData.append("version", version);

      // Display a preview of the selected image
      var reader = new FileReader();
      reader.onload = function(e) {
        var mockupImage = "<img src='" + e.target.result + "' alt='Mockup Image' />";
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
    var mockupInput = mockupColumn.querySelector('input[type="hidden"].hidden_mockup_url');
    var spinner = mockupColumn.querySelector(".lds-spinner-wrap");

    var addMockupButton = document.querySelector('#addMockupButton');
    var sendProofButton = document.querySelector('#send-proof-button');

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
        if( data.success && data.file_path ) {
          mockupInput.value = data.file_path;

          if (addMockupButton) {
            addMockupButton.removeAttribute('disabled');
          }
          if (sendProofButton) {
            sendProofButton.removeAttribute('disabled');
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