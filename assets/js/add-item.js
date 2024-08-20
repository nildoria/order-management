jQuery(document).ready(function ($) {
  $("#addProductModal").magnificPopup({
    items: {
      src: "#add-item-modal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Category and search filter function
  function filterProducts() {
    const searchTerm = $("#fetchProductList").val().toLowerCase();
    const items = $("#productDropdown li.product-item");

    items.each(function () {
      const item = $(this);
      const itemName = item.text().toLowerCase();

      const searchMatch =
        searchTerm.length < 1 || itemName.includes(searchTerm);

      if (searchMatch) {
        item.show();
      } else {
        item.hide();
      }
    });
  }

  $("#fetchProductList").on("input", function () {
    filterProducts();
  });

  $("#fetchProductList")
    .on("click", function () {
      if (!$(this).data("loaded")) {
        fetchProducts();
        $(this).data("loaded", true);
      }
      $("#productDropdown").slideDown();
    })
    .on("focusout", function () {
      $("#productDropdown").slideUp();
    });

  function fetchProducts() {
    var order_domain = alarnd_add_item_vars.order_domain;
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
        `<li data-id="${product.id}" class="product-item">
            <img src="${productThumbnail}" alt="${product.name}" class="product-thumb">
            ${product.name}
        </li>`
      );
    });
    productDropdown.append(
      `<li data-id="freestyle" class="product-item-freestyle">
            <img src="${alarnd_add_item_vars.assets}images/allaround-logo.png" alt="Freestyle" class="product-thumb">
            Freestyle Item
        </li>`
    );

    $(".product-item").on("click", function () {
      const productId = $(this).data("id");
      fetchProductDetails(productId);

      const productThumbnail = $(this).find(".product-thumb").attr("src");
      const productName = $(this).text().trim();

      // Call the new function with the necessary parameters
      updateSelectedProductDisplay(productId, productThumbnail, productName);
    });

    $(".product-item-freestyle").on("click", function () {
      const productId = $(this).data("id");
      freestyleProductDetails(productId);
      loadCreateOrderJS();
      $("#addNewItemButton")
        .addClass("om_add_item_selected")
        .prop("disabled", false);
      $("#fetchProductList").hide();
      $("label[for='fetchingProductList']").hide();
      $("#selectedProductDisplay").addClass("selected-item-display");
      $("#add-item-modal .select-product-input").prepend(
        '<button id="returnToSelectProduct" class="button">Select Another Product</button>'
      );

      const productThumbnail = $(this).find(".product-thumb").attr("src");
      const productName = $(this).text().trim();

      // Call the new function with the necessary parameters
      updateSelectedProductDisplay(productId, productThumbnail, productName);
    });
  }

  function updateSelectedProductDisplay(
    productId,
    productThumbnail,
    productName
  ) {
    // Add an <h5> with "Selected Product" text before #selectedProductDisplay if it doesn't already exist
    if ($("#selectedProductDisplay").prev("label").length === 0) {
      $("#selectedProductDisplay").before("<label>Selected Product</label>");
    }

    // Update the #selectedProductDisplay with the product details
    if ($("#selectedProductDisplay").length) {
      $("#selectedProductDisplay").html(
        `<img src="${productThumbnail}" alt="${productName}" class="product-thumb">${productName}`
      );
      $("#selectedProductDisplay").data("product-id", productId);
    }

    // Update the hidden input value
    $("#new_product_id").val(productId);
  }

  function freestyleProductDetails(productId) {
    let productDetailsContainer = $("#productDetailsContainer");
    let html = `<div class="product-custom-quantity-wraper">
                  <div class="form-group">
                    <label for="custom-quantity">Quantity</label>
                    <div class="quantity-wrapper">
                      <input type="text" name="freestyle-custom-quantity" class="freestyle-custom-quantity" value="1" data-steps='[]'>
                      <div class="price-total">
                          <span class="item-total-number">0</span>₪
                          <input type="hidden" class="item_total_price" name="item_total_price" value="0">
                      </div>
                      <div class="price-item freestyle-price-rate">
                          <input type="text" value="1" name="item-rate-number" class="freestyle-rate-number-input">
                          <span> per unit</span>
                      </div>
                    </div>
                  </div>
                  ${renderArtworkUploader()}
                  ${renderInstructionNote()}
                  <button name="add-to-cart" value="freestyle" id="addNewItemButton"
                    class="customQuantity_add_to_cart_button ml_add_loading button alt ">Add to order</button>
                </div>`;
    productDetailsContainer.html(html);
  }

  function fetchProductDetails(productId) {
    var order_domain = alarnd_add_item_vars.order_domain;
    fetch(`${order_domain}/wp-json/alarnd-main/v1/products/${productId}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }
        return response.json();
      })
      .then((data) => {
        data = data[0];
        displayProductDetails(data);
        loadCreateOrderJS();
        $("#addNewItemButton")
          .addClass("om_add_item_selected")
          .prop("disabled", false);
        $("#fetchProductList").hide();
        $("label[for='fetchingProductList']").hide();
        $("#selectedProductDisplay").addClass("selected-item-display");
        $("#add-item-modal .select-product-input").prepend(
          '<button id="returnToSelectProduct" class="button">Select Another Product</button>'
        );
      })
      .catch((error) => {
        console.error("Error fetching product details:", error);
      });
  }

  $(document).on("click", "#returnToSelectProduct", function () {
    $("#productDetailsContainer").empty();
    $("#selectedProductDisplay")
      .html("Select Product...")
      .removeClass("selected-item-display");
    $("#add-item-modal").css("max-width", "");
    $("#fetchProductList").show();
    $("label[for='fetchingProductList']").show();
    $("#returnToSelectProduct").remove();
    $("#new_product_id").val("");
    $("#addNewItemButton")
      .removeClass("om_add_item_selected")
      .prop("disabled", true);

    $("#selectedProductDisplay").prev("label").remove();
  });

  function displayProductDetails(product) {
    let productDetailsContainer = $("#productDetailsContainer");
    console.log(product);
    if (product.is_custom_quantity) {
      renderCustomQuantityProduct(productDetailsContainer, product);
    } else if (product.is_group_quantity) {
      renderGroupQuantityProduct(productDetailsContainer, product);
    } else {
      productDetailsContainer.html(
        "<p>No specific structure for this product.</p>"
      );
    }
  }

  function loadCreateOrderJS() {
    // Load create-order.js script
    const createOrderScriptPath =
      "/wp-content/themes/manage-order/assets/js/create-order.js";
    // Dynamically load the create-order.js script
    $.getScript(createOrderScriptPath)
      .done(function (script, textStatus) {
        console.log("create-order.js loaded successfully.");
        // Optionally, you can execute any function defined in create-order.js here
        // if (typeof initCreateOrder === "function") {
        //   initCreateOrder(); // assuming initCreateOrder is a function defined in create-order.js
        // }
      })
      .fail(function (jqxhr, settings, exception) {
        console.error("Error loading create-order.js:", exception);
      });
  }

  function renderCustomQuantityProduct(container, product) {
    const modal = $("#add-item-modal");
    // remove max-width css property from modal
    modal.css("max-width", "");

    modal.removeClass("grouped-product");
    let html = `
            <div class="product-custom-quantity-wraper">
                ${renderColors(product.colors)}
                ${renderQuantitySteps(product.quantity_steps)}
                ${renderArtworkUploader()}
                ${renderInstructionNote()}
                <button name="add-to-cart" value="${
                  product.id
                }" id="addNewItemButton"
                    class="customQuantity_add_to_cart_button ml_add_loading button alt ">Add to order</button>
            </div>
        `;
    container.html(html);
  }

  function renderGroupQuantityProduct(container, product) {
    const modal = $("#add-item-modal");
    modal.addClass("addItem-grouped-product");
    const artistPositions = product.art_positions;
    console.log(artistPositions);
    const sizes = product.sizes;
    const sizeCount = Object.keys(sizes).length;
    const modalWidth = 130 + sizeCount * 70;
    const tableWidth = 80 + sizeCount * 70;
    modal.css("max-width", modalWidth + "px");
    let html = `
            <div class="product-grouped-product-wraper" data-regular_price="${
              product.price
            }" data-steps='${JSON.stringify(product.quantity_steps)}'>
                <div class="alarnd--select-options-cart-wrap">
                    <div class="alarnd--select-options" style="width: ${tableWidth}px">
                        <div class="alarnd--select-opt-wrapper">
                            <div class="alarnd--select-opt-header">
                                ${Object.values(sizes)
                                  .map((size) => `<span>${size}</span>`)
                                  .join("")}
                            </div>
                            <div class="alarnd--select-qty-body">
                                ${product.colors
                                  .map(
                                    (color, color_index) => `
                                    <div class="alarn--opt-single-row">
                                        ${Object.values(sizes)
                                          .map(
                                            (size) => `
                                            <div class="tshirt-qty-input-field${
                                              isSizeOmitted(color, size)
                                                ? " omit-size"
                                                : ""
                                            }">
                                                <input type="text" autocomplete="off"
                                                    name="alarnd__color_qty[${color_index}][${size}]"
                                                    class="group-product-input"
                                                    data-color="${color.title}"
                                                    data-size="${size}"
                                                    ${
                                                      isSizeOmitted(color, size)
                                                        ? 'disabled placeholder="N/A"'
                                                        : ""
                                                    }>
                                            </div>
                                        `
                                          )
                                          .join("")}
                                        <div class="alarnd--opt-color">
                                            <span style="background-color: ${
                                              color.color_hex_code
                                            };">${color.title}</span>
                                        </div>
                                    </div>
                                `
                                  )
                                  .join("")}
                            </div>
                        </div>
                    </div>
                </div>
                  ${renderGroupProductMetaData(artistPositions)}
                  <div class="grouped-modal-actions">
                    <div class="alarnd--price-by-shirt">
                        <p class="alarnd--group-price">
                            <span class="group_unite_price">0</span>₪ / Unit
                            <input type="hidden" class="item_unit_rate" name="item_unit_rate">
                        </p>
                        <p class="total-units">
                            Total Units: <span class="total_units">0</span>
                            <input type="hidden" class="item_total_units" name="item_total_units">
                        </p>
                        <div class="price-total">
                            Total: <span class="item-total-number">0</span>₪
                            <input type="hidden" class="item_total_price" name="item_total_price">
                        </div>
                    </div>
                    <button name="add-to-cart" data-product_id="${
                      product.id
                    }" id="addNewItemButton"
                    class="groupedProduct_add_to_cart_button ml_add_loading button alt ">Add to order</button>
                </div>
            </div>
        `;
    container.html(html);
    dynamicVariationColor();
  }

  function renderColors(colors) {
    if (!Array.isArray(colors) || colors.length === 0) {
      return "";
    }
    return `
            <div class="form-group">
                <label for="new_product_color">Select a Color</label>
                <div class="custom-colors-wrapper">
                    ${colors
                      .map(
                        (color, index) => `
                        <span class="alarnd--single-var-info">
                            <input type="radio" id="custom_color-${
                              color.id
                            }-${index}"
                                name="custom_color" value="${color.color}" ${
                          index === 0 ? "checked" : ""
                        }>
                            <label for="custom_color-${color.id}-${index}">${
                          color.color
                        }</label>
                        </span>
                    `
                      )
                      .join("")}
                </div>
            </div>
        `;
  }

  function renderQuantitySteps(quantitySteps) {
    if (!Array.isArray(quantitySteps) || quantitySteps.length === 0) {
      return "";
    }
    return `
            <div class="form-group">
                <label for="custom-quantity">Quantity</label>
                <div class="quantity-wrapper">
                    <input type="text" name="custom-quantity" class="custom-quantity" value="1" data-steps='${JSON.stringify(
                      quantitySteps
                    )}'>
                    <div class="price-total">
                        <span class="item-total-number">0</span>₪
                        <input type="hidden" class="item_total_price" name="item_total_price">
                    </div>
                    <div class="price-item">
                        <span class="item-rate-number">0</span> per unit
                    </div>
                </div>
            </div>
        `;
  }

  function renderArtworkUploader() {
    return `
            <div class="form-group">
                <label for="new_product_artwork">Upload Artwork</label>
                <input type="file" class="new_product_artwork" name="artwork" multiple />
                <input type="hidden" class="uploaded_file_path" name="uploaded_file_path">
            </div>
        `;
  }

  function renderArtPosition(artistPositions) {
    return `
            <div class="form-group">
                <label for="new_product_art_pos">Art Position</label>
                <select class="new_product_art_pos">
                    <option value="">Select Art Position</option>
                    ${artistPositions
                      .map(
                        (position) =>
                          `<option value="${position.title}">${position.title}</option>`
                      )
                      .join("")}
                </select>
            </div>
        `;
  }

  function renderInstructionNote() {
    return `
            <div class="form-group">
                <label for="new_product_instruction_note">Instruction Note</label>
                <input type="text" class="new_product_instruction_note" value="" placeholder="Enter Instruction Note" />
            </div>
        `;
  }

  function renderGroupProductMetaData(artistPositions) {
    return `
            <div class="grouped-product-meta-data">
                ${renderArtworkUploader()}
                ${renderArtPosition(artistPositions)}
                ${renderInstructionNote()}
            </div>
        `;
  }

  function isSizeOmitted(color, size) {
    if (!Array.isArray(color.omit_sizes)) {
      return false;
    }
    return color.omit_sizes.some((omitSize) => omitSize.value === size);
  }

  function getBrightness(hex) {
    hex = hex.replace("#", "");
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return (r * 299 + g * 587 + b * 114) / 1000;
  }

  function dynamicVariationColor() {
    $(".alarnd--opt-color span").each(function () {
      const bgColor = $(this).css("background-color");
      const rgb = bgColor.match(/\d+/g);
      const hexColor = rgb
        ? "#" +
          (
            (1 << 24) +
            (parseInt(rgb[0]) << 16) +
            (parseInt(rgb[1]) << 8) +
            parseInt(rgb[2])
          )
            .toString(16)
            .slice(1)
            .toUpperCase()
        : bgColor;

      const brightness = getBrightness(hexColor);
      if (brightness < 128) {
        $(this).css("color", "#FFFFFF"); // White text color
      } else {
        $(this).css("color", "#000000"); // Black text color
      }
    });
  }

  dynamicVariationColor();

  // Event listener for adding new item button
  $(document).on("click", "#addNewItemButton", function (event) {
    event.preventDefault();
    const modal = $(this).closest(".product-details-modal");

    const post_id = $("#post_id").val();
    const client_id = $("#add-item-modal").data("client_id");
    const order_source = $("#om__order_source").data("order_source");

    $("#addNewItemButton").addClass("ml_loading");

    const isGroupedProduct = $(this).hasClass(
      "groupedProduct_add_to_cart_button"
    );
    const newItem = [];

    if (isGroupedProduct) {
      // Loop through all grouped product inputs and collect data
      $(".group-product-input").each(function () {
        const productId = modal.find("#new_product_id").val();
        const quantity = $(this).val();
        const color = $(this).data("color");
        const size = $(this).data("size");
        const artworkUrl = modal.find(".uploaded_file_path").val();
        const artPosition = modal.find(".new_product_art_pos").val();
        const instructionNote = modal
          .find(".new_product_instruction_note")
          .val();

        if (quantity > 0) {
          newItem.push({
            product_id: productId,
            quantity: quantity,
            alarnd_color: color,
            alarnd_size: size,
            allaround_art_pos: artPosition,
            allaround_instruction_note: instructionNote,
            alarnd_artwork: artworkUrl,
            order_id: alarnd_add_item_vars.order_id,
            nonce: alarnd_add_item_vars.nonce,
          });
        }
      });
    } else {
      const productId = modal.find("#new_product_id").val();
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

      newItem.push({
        product_id: productId,
        quantity: quantity,
        alarnd_color: selectedColor,
        subtotal: subTotalPrice,
        allaround_instruction_note: instructionNote,
        alarnd_artwork: artworkUrl,
        order_id: alarnd_add_item_vars.order_id,
        nonce: alarnd_add_item_vars.nonce,
      });
    }

    let order_domain = alarnd_add_item_vars.order_domain;

    let requestData = {
      action: "update_order_transient",
      order_id: alarnd_add_item_vars.order_id,
    };

    function handleResponse(response) {
      $("#addNewItemButton").removeClass("ml_loading");
      alert("Item(s) added successfully");
      location.reload(); // Refresh the page to see the new item
    }

    console.log(newItem);

    fetch(`${order_domain}/wp-json/update-order/v1/add-item-to-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          return response.json(); // Parse the JSON response
        } else {
          return response.json().then((data) => {
            throw new Error(data.message || "Something went wrong");
          });
        }
      })
      .then((data) => {
        // Use the message and newly_added_total from the response
        // console.log("Client ID:", client_id);
        // console.log("Order Source:", order_source);

        // console.log("Response message:", data.message);
        // console.log("Newly Added Total:", data.newly_added_total);

        // Prepare data to send to the server to handle order source
        const handleOrderSourceData = {
          action: "handle_order_source_action",
          post_id: post_id,
          client_id: client_id,
          order_source: order_source,
          total_price: data.newly_added_total,
        };

        // Send data to the server via AJAX
        $.post(
          alarnd_add_item_vars.ajax_url,
          handleOrderSourceData,
          function (response) {
            console.log("Order source handled successfully:", response);
          }
        ).fail(function (error) {
          console.error("Error handling order source:", error);
          alert("Error handling order source: " + error.statusText);
        });

        ml_send_ajax(requestData, handleResponse);
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error: " + error.message);
      })
      .finally(() => {
        $("#addNewItemButton").removeClass("ml_loading");
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
});
