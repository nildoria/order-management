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
});

jQuery(document).ready(function ($) {
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
    let order_domain = "https://allaround.test"; // Replace with your domain
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

    $(".product-item").on("click", function () {
      const productId = $(this).data("id");
      fetchProductDetails(productId);
    });
  }

  function fetchProductDetails(productId) {
    let order_domain = "https://allaround.test"; // Replace with your domain
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
      })
      .catch((error) => {
        console.error("Error fetching product details:", error);
      });
  }

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

  function renderCustomQuantityProduct(container, product) {
    const modal = $("#add-item-modal");
    modal.removeClass("grouped-product");
    let html = `
            <div class="product-custom-quantity-wraper">
                ${renderColors(product.colors)}
                ${renderQuantitySteps(product.quantity_steps)}
                ${renderArtworkUploader()}
                ${renderInstructionNote()}
                <button name="add-to-cart" value="${product.id}"
                    class="single_add_to_cart_button ml_add_loading button alt ">Add to cart</button>
            </div>
        `;
    container.html(html);
  }

  function renderGroupQuantityProduct(container, product) {
    const modal = $("#add-item-modal");
    modal.addClass("grouped-product");
    var sizes = product.sizes;
    console.log(sizes);
    let html = `
            <div class="product-grouped-product-wraper" style="width: ${
              product.size_modal_width
            }px" data-regular_price="${
      product.price
    }" data-steps='${JSON.stringify(product.quantity_steps)}'>
                <div class="alarnd--select-options-cart-wrap">
                    <div class="alarnd--select-options">
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
                ${renderGroupProductMetaData()}
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
                        <button name="add-to-cart" data-product_id="${
                          product.id
                        }"
                        class="grouped_product_add_to_cart ml_add_loading button alt ">Add to cart</button>
                    </div>
                </div>
            </div>
        `;
    container.html(html);
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

  function renderInstructionNote() {
    return `
            <div class="form-group">
                <label for="new_product_instruction_note">Instruction Note</label>
                <input type="text" class="new_product_instruction_note" value="" placeholder="Enter Instruction Note" />
            </div>
        `;
  }

  function renderGroupProductMetaData() {
    return `
            <div class="grouped-product-meta-data">
                ${renderArtworkUploader()}
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
});
