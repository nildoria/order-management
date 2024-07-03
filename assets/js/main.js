(function ($) {
  ("use strict");

  // ********** Order Management Scripts Start **********//
  const mainTable = document.getElementById("tableMain");
  // ********** Mockup Image Upload Post Request **********//

  // ********** Send Mockup to ArtWork Post Request **********//

  $("#send-proof-button").on("click", function () {
    let orderId = allaround_vars.order_id;
    let orderNumber = allaround_vars.order_number;
    let customerName = allaround_vars.customer_name;
    let customerEmail = allaround_vars.customer_email;
    let commentText = "Your comment here"; // Customize as needed

    let mainTable = document.getElementById("tableMain");
    if (!mainTable) {
      alert("Nothing to send! Please add Mockup to the order.");
      return;
    }

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

    // let proofStatus = "Mockups V" + version + " Sent";
    let proofStatus = "Mockup V1 Sent";
    console.log("proofStatus", proofStatus);

    let imageUrls = [];

    document.querySelectorAll("tbody > tr").forEach(function (row) {
      let itemMockupColumns = row.querySelectorAll(".item_mockup_column");

      // Get the second last element in the NodeList
      let secondLastItemMockupColumn =
        itemMockupColumns[itemMockupColumns.length - 2];

      if (secondLastItemMockupColumn) {
        let input = secondLastItemMockupColumn.querySelector(
          'input[type="hidden"].hidden_mockup_url'
        );
        if (input && input.value.trim() !== "") {
          imageUrls.push(input.value.trim());
        }
      }
    });

    let imageUrlString = imageUrls.join(",");
    console.log(imageUrlString); // Outputs: url1,url2,url3...

    let data = {
      comment_text: commentText,
      order_id: orderId,
      order_number: orderNumber,
      image_urls: imageUrlString,
      proof_status: proofStatus,
      customer_name: customerName,
      customer_email: customerEmail,
    };

    let requestData = {
      action: "send_proof_version",
      post_id: allaround_vars.post_id,
      version: version,
    };

    function handleResponse(data) {
      if (data.error) {
        Toastify({
          text: `There was an error sending the proof: ${data.message}`,
          className: "info",
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          style: {
            background: "linear-gradient(to right, #cc3366, #a10036)",
          },
        }).showToast();
      } else {
        Toastify({
          text: `#${orderNumber} Proof sent successfully!`,
          duration: 3000,
          close: true,
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          stopOnFocus: true, // Prevents dismissing of toast on hover
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();
        setTimeout(function () {
          location.reload();
        }, 1200);
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

  // ********** Duplicate Order Item **********//
  $(document).on("click", ".om_duplicate_item", function () {
    let order_id = allaround_vars.order_id;
    let item_id = $(this).siblings('input[name="item_id"]').val();
    let order_domain = allaround_vars.order_domain;

    // Debugging
    console.log("Order ID:", order_id);
    console.log("Item ID:", item_id);
    console.log("Order Domain:", order_domain);

    let newItem = {
      order_id: order_id,
      item_id: item_id,
      method: "duplicateItem",
      nonce: allaround_vars.nonce,
    };

    let requestData = {
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
    let order_id = allaround_vars.order_id;
    let item_id = $(this).siblings('input[name="item_id"]').val();
    let order_domain = allaround_vars.order_domain;

    // Debugging
    console.log("Order ID:", order_id);
    console.log("Item ID:", item_id);
    console.log("Order Domain:", order_domain);

    let newItem = {
      order_id: order_id,
      item_id: item_id,
      method: "deleteItem",
      nonce: allaround_vars.nonce,
    };

    let requestData = {
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

  // ********** Order Meta Update Script **********//
  $(document).on("click", "[id^=update-item-meta-btn_]", function () {
    const itemId = $(this).data("item_id");
    const orderId = $(this).data("order_id");
    const newSize = $("#size-input_" + itemId).val();
    const newColor = $("#color-input_" + itemId).val();
    const newArtPosition = $("#art-position-input_" + itemId).val();
    const newInstructionNote = $("#instruction-note-input_" + itemId).val();

    // Collect only the fields that have changed
    let newItemMeta = {
      order_id: orderId,
      item_id: itemId,
    };

    if (newSize) newItemMeta.size = newSize;
    if (newColor) newItemMeta.color = newColor;
    if (newArtPosition) newItemMeta.art_position = newArtPosition;
    if (newInstructionNote) newItemMeta.instruction_note = newInstructionNote;

    updateItemMeta(newItemMeta);
  });

  function updateItemMeta(newItemMeta) {
    let order_domain = allaround_vars.order_domain;

    let requestData = {
      action: "update_order_transient",
      order_id: newItemMeta.order_id,
    };

    function handleResponse(response) {
      $.magnificPopup.close();
      Toastify({
        text: `${response.message}`,
        duration: 3000,
        close: true,
        gravity: "bottom", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
          background: "linear-gradient(to right, #00b09b, #96c93d)",
        },
      }).showToast();
    }

    fetch(`${order_domain}/wp-json/update-order/v1/update-item-meta`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItemMeta),
    })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          return response.json().then((data) => {
            throw new Error(data.message);
          });
        }
      })
      .then((data) => {
        if (data.success) {
          handleResponse(data);
          ml_send_ajax(requestData);
          updateItemMetaInDOM(newItemMeta.item_id, data.data);
        } else {
          alert("Failed to update item meta: " + data.message);
        }
        $.magnificPopup.close();
      })
      .catch((error) => {
        console.error("Error:", error);
        $.magnificPopup.close();
        Toastify({
          text: `An error occurred: ${error.message}`,
          className: "info",
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          style: {
            background: "linear-gradient(to right, #cc3366, #a10036)",
          },
        }).showToast();
      });
  }

  function updateItemMetaInDOM(itemId, updatedData) {
    // console.log("Updated Data:", updatedData);
    // console.log("Item ID:", itemId);
    let itemRow = document.querySelector(
      'tr[data-product_id="' + itemId + '"]'
    );
    if (itemRow) {
      let metaList = itemRow.querySelector(".item_name_variations ul");

      if (updatedData.size) {
        let sizeItem = metaList.querySelector('li[data-meta_key="Size"]');
        if (sizeItem) {
          sizeItem.textContent = "Size: " + updatedData.size;
        } else {
          metaList.insertAdjacentHTML(
            "beforeend",
            '<li data-meta_key="Size">Size: ' + updatedData.size + "</li>"
          );
        }
      }

      if (updatedData.color) {
        let colorItem = metaList.querySelector('li[data-meta_key="Color"]');
        if (colorItem) {
          colorItem.textContent = "Color: " + updatedData.color;
        } else {
          metaList.insertAdjacentHTML(
            "beforeend",
            '<li data-meta_key="Color">Color: ' + updatedData.color + "</li>"
          );
        }
      }

      if (updatedData.art_position) {
        let artPositionItem = metaList.querySelector(
          'li[data-meta_key="Art Position"]'
        );
        if (artPositionItem) {
          artPositionItem.textContent =
            "Art Position: " + updatedData.art_position;
        } else {
          metaList.insertAdjacentHTML(
            "beforeend",
            '<li data-meta_key="Art Position">Art Position: ' +
              updatedData.art_position +
              "</li>"
          );
        }
      }

      if (updatedData.instruction_note) {
        let instructionNoteItem = metaList.querySelector(
          'li[data-meta_key="Instruction Note"]'
        );
        if (instructionNoteItem) {
          instructionNoteItem.textContent =
            "Instruction Note: " + updatedData.instruction_note;
        } else {
          metaList.insertAdjacentHTML(
            "beforeend",
            '<li data-meta_key="Instruction Note">Instruction Note: ' +
              updatedData.instruction_note +
              "</li>"
          );
        }
      }
    }
  }

  // ********** Order Artwork Meta Update **********//
  $(".om__upload_artwork").on("change", function (event) {
    let $this = $(this);
    let files = event.target.files;
    let uploadedFiles = [];
    const orderId = allaround_vars.order_id;
    const itemId = $(this).data("item_id");
    const metaKey = $(this).data("meta_key");
    let artworkContainer = $this.closest(".uploaded_graphics");

    if (files.length > 0) {
      $("body").addClass("updating");
      $this.siblings(".om__editItemArtwork").hide();
      showSpinner(artworkContainer); // Show spinner

      let formData = new FormData();
      for (let i = 0; i < files.length; i++) {
        formData.append("files[]", files[i]);
      }

      let newArtworkMeta = {
        order_id: orderId,
        item_id: itemId,
        art_meta_key: metaKey,
      };

      fetch("/wp-content/themes/manage-order/includes/php/artwork-upload.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            uploadedFiles = data.file_paths;
            let fileInfo = uploadedFiles[0];
            if (fileInfo) newArtworkMeta.artwork_img = fileInfo;
            updateItemArtworkMeta(newArtworkMeta, artworkContainer);
          } else {
            alert("Failed to upload files: " + data.message);
            $("body").removeClass("updating");
            hideSpinner(artworkContainer); // Hide spinner
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
          $("body").removeClass("updating");
          hideSpinner(artworkContainer); // Hide spinner
        })
        .finally(() => {
          $this.siblings(".om__editItemArtwork").show();
        });
    }
  });

  function updateItemArtworkMeta(newArtworkMeta, artworkContainer) {
    let order_domain = allaround_vars.order_domain;
    let themeAssets = allaround_vars.assets;

    artworkContainer.find(".om__editItemArtwork").hide();

    let requestData = {
      action: "update_order_transient",
      order_id: newArtworkMeta.order_id,
    };

    function handleResponse(response) {
      Toastify({
        text: `Artwork updated successfully!`,
        duration: 3000,
        close: true,
        gravity: "bottom", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
          background: "linear-gradient(to right, #00b09b, #96c93d)",
        },
      }).showToast();
    }

    fetch(`${order_domain}/wp-json/update-order/v1/update-item-meta`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newArtworkMeta),
    })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          return response.json().then((data) => {
            throw new Error(data.message);
          });
        }
      })
      .then((data) => {
        if (data.success) {
          console.log(data.data);
          ml_send_ajax(requestData, handleResponse);
          let fileInfo = data.data.attachment_url;
          let link = artworkContainer.find("a");
          if (link.length) {
            link.attr("href", fileInfo);
          } else {
            link = $("<a></a>")
              .attr("href", fileInfo)
              .appendTo(artworkContainer);
            artworkContainer.find(".no_artwork_text").remove();
          }
          let img = artworkContainer.find(".alarnd__artwork_img");
          if (img.length) {
            // Check if fileInfo ends with .png, .jpg, or .jpeg
            if (/\.(png|jpg|jpeg)$/i.test(fileInfo)) {
              img.attr("src", fileInfo);
            } else if (/\.(pdf)$/i.test(fileInfo)) {
              img.attr("src", `${themeAssets}images/pdf-icon.svg`);
            } else {
              img.attr("src", `${themeAssets}images/document.png`);
            }
          } else {
            img = $('<img class="alarnd__artwork_img">');
            if (/\.(png|jpg|jpeg)$/i.test(fileInfo)) {
              img.attr("src", fileInfo);
            } else if (/\.(pdf)$/i.test(fileInfo)) {
              img.attr("src", `${themeAssets}images/pdf-icon.svg`);
            } else {
              img.attr("src", `${themeAssets}images/document.png`);
            }
            artworkContainer.append(img);
            artworkContainer.find(".no_artwork_text").remove();
          }
        } else {
          alert("Failed to update item meta: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Toastify({
          text: `An error occurred: ${error.message}`,
          className: "info",
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          style: {
            background: "linear-gradient(to right, #cc3366, #a10036)",
          },
        }).showToast();
      })
      .finally(() => {
        $("body").removeClass("updating");
        artworkContainer.find(".om__editItemArtwork").show();
        setTimeout(() => {
          hideSpinner(artworkContainer); // Hide spinner
        }, 100);
      });
  }

  function showSpinner(container) {
    const spinnerHtml =
      '<div class="lds-spinner-wrap" style="display: flex"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
    container.prepend(spinnerHtml);
  }

  function hideSpinner(container) {
    container.find(".lds-spinner-wrap").fadeOut(500, function () {
      $(this).remove();
    });
  }

  // ********** Fetch Item Meta **********//
  $(document).on("click", ".om__editItemMeta", function () {
    let itemId = $(this).data("item_id");
    $.magnificPopup.open({
      items: {
        src: "#om__itemVariUpdateModal_" + itemId,
      },
      type: "inline",
      midClick: true, // Allow opening popup on middle mouse click.
    });
  });

  $(document).on("focus", "[id^=color-input_]", function () {
    const itemId = $(this)
      .closest(".om__itemVariUpdateModal")
      .data("source_product_id");
    fetchProductOptions(itemId, "color", $(this).attr("id"));
  });

  $(document).on("focus", "[id^=size-input_]", function () {
    const itemId = $(this)
      .closest(".om__itemVariUpdateModal")
      .data("source_product_id");
    fetchProductOptions(itemId, "size", $(this).attr("id"));
  });

  $(document).on("focus", "[id^=art-position-input_]", function () {
    const itemId = $(this)
      .closest(".om__itemVariUpdateModal")
      .data("source_product_id");
    fetchProductOptions(itemId, "art_position", $(this).attr("id"));
  });

  function fetchProductOptions(productId, optionType, selectorId) {
    let order_domain = allaround_vars.order_domain;

    fetch(`${order_domain}/wp-json/alarnd-main/v1/products`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }
        return response.json();
      })
      .then((products) => {
        const product = products.find((product) => product.id === productId);
        if (product) {
          if (optionType === "color") {
            if (product.is_custom_quantity) {
              populateCustomQuantityColors("#" + selectorId, product.colors);
            } else if (product.is_group_quantity) {
              populateGroupQuantityColors("#" + selectorId, product.colors);
            }
          } else if (optionType === "size") {
            populateSizeDropdown("#" + selectorId, product.sizes);
          } else if (optionType === "art_position") {
            populateArtPositionsDropdown(
              "#" + selectorId,
              product.art_positions
            );
          }
        }
      })
      .catch((error) => {
        console.error("Error fetching product options:", error);
      });
  }

  // ********** Update Item Details **********//
  $(".item-cost-input, .item-quantity-input").on("change", function () {
    let itemId = $(this).data("item-id");
    let newCost = $(this).closest("tr").find(".item-cost-input").val();
    let newQuantity = $(this).closest("tr").find(".item-quantity-input").val();
    let orderId = $('input[name="order_id"]').val();
    let order_domain = allaround_vars.order_domain;

    let newItem = {
      order_id: orderId,
      item_id: itemId,
      new_cost: newCost,
      new_quantity: newQuantity,
    };

    let requestData = {
      action: "update_order_transient",
      order_id: orderId,
    };

    function handleResponse(response) {
      updateOrderTotals(
        response.items_subtotal,
        response.shipping_total,
        response.order_total
      );
      updateItemDetails(itemId, newQuantity, newCost, response.item_total);
      Toastify({
        text: `${response.message}`,
        duration: 3000,
        close: true,
        gravity: "bottom", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
          background: "linear-gradient(to right, #00b09b, #96c93d)",
        },
      }).showToast();
    }

    fetch(`${order_domain}/wp-json/update-order/v1/update-item-details`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newItem),
    })
      .then((response) => {
        if (response.ok) {
          return response.json().then((data) => {
            handleResponse(data);
            ml_send_ajax(requestData);
          });
        } else {
          return response.json().then((data) => {
            Toastify({
              text: `A problem occurred: ${data.message}`,
              duration: 3000,
              close: true,
              gravity: "bottom", // `top` or `bottom`
              position: "right", // `left`, `center` or `right`
              stopOnFocus: true, // Prevents dismissing of toast on hover
              style: {
                background: "linear-gradient(to right, #cc3366, #a10036)",
              },
            }).showToast();
            throw new Error(data.message);
          });
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred: " + error.message);
      });
  });

  function updateOrderTotals(itemsSubtotal, shippingTotal, orderTotal) {
    $(".om__items_subtotal").text(
      itemsSubtotal + " " + allaround_vars.currency_symbol
    );
    $(".om__shipping_total").text(
      shippingTotal + " " + allaround_vars.currency_symbol
    );
    $(".om__orderTotal").text(
      orderTotal + " " + allaround_vars.currency_symbol
    );
  }
  function updateItemDetails(itemId, newQuantity, newCost, itemTotal) {
    let $row = $(`tr[data-product_id="${itemId}"]`);
    $row.find(".om__itemQuantity").text(newQuantity);
    $row
      .find(".om__itemRate")
      .text(newCost + "" + allaround_vars.currency_symbol);
    $row
      .find(".om__itemCostTotal")
      .text(itemTotal + "" + allaround_vars.currency_symbol);
  }
  // Initial update to reflect changes instantly
  $(document).on("input", ".item-quantity-input", function () {
    let newQuantity = $(this).val();
    $(this).closest("tr").find(".om__itemQuantity").text(newQuantity);
  });

  $(document).on("input", ".item-cost-input", function () {
    let newCost = $(this).val();
    $(this)
      .closest("tr")
      .find(".om__itemRate")
      .text(newCost + "" + allaround_vars.currency_symbol);
  });

  // ********** Fetch Products from Ordered Site **********//
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

        $("#addNewItemButton, #addProductButton")
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
    const currentValue = dropdown.val();
    dropdown.empty();
    dropdown.append(
      `<option value="${currentValue}" selected>${currentValue}</option>`
    );
    dropdown.append('<option value="">Select Color</option>');
    if (Array.isArray(colors)) {
      colors.forEach((item) => {
        dropdown.append(`<option value="${item.color}">${item.color}</option>`);
      });
    }
  }

  function populateGroupQuantityColors(selector, colors) {
    const dropdown = $(selector);
    const currentValue = dropdown.val();
    dropdown.empty();
    dropdown.append(
      `<option value="${currentValue && currentValue}" selected>${
        currentValue ? currentValue : "Select Color"
      }</option>`
    );
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
    const currentValue = dropdown.val();
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
    dropdown.append(
      `<option value="${currentValue && currentValue}" selected>${
        currentValue ? currentValue : "Select Size"
      }</option>`
    );
    if (sizes && typeof sizes === "object") {
      Object.values(sizes).forEach((item) => {
        dropdown.append(`<option value="${item}">${item}</option>`);
      });
    }
  }

  function populateArtPositionsDropdown(selector, artPositions) {
    const dropdown = $(selector);
    const currentValue = dropdown.val();
    dropdown.empty();
    if (!artPositions || !Array.isArray(artPositions)) {
      dropdown.hide();
      return;
    }

    dropdown.closest(".form-group").show();
    dropdown.show();
    dropdown.append(
      `<option value="${currentValue && currentValue}" selected>${
        currentValue ? currentValue : "Select Art Position"
      }</option>`
    );
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

  // ********** Add New Item to the Existing Order **********//
  $("#new_product_artwork").on("change", function (event) {
    let files = event.target.files;
    let uploadedFiles = [];

    if (files.length > 0) {
      $("#addNewItemButton, #addProductButton")
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
            $("#uploaded_file_path").val(JSON.stringify(uploadedFiles));
            console.log("Files uploaded successfully:", data.file_paths);
          } else {
            alert("Failed to upload files: " + data.message);
          }
          $("#addNewItemButton, #addProductButton")
            .removeClass("ml_loading")
            .prop("disabled", false);
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
          $("#addNewItemButton, #addProductButton")
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

    let requestData = {
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
  // ********** Add Item to create Order **********//

  if (!mainTable) {
    console.log("Main table not found");
    // ********** Add New Order **********//
    let products = [];

    $("#selectedProductDisplay").on("click", function () {
      if (!$(this).data("loaded")) {
        fetchAddProducts();
        $(this).data("loaded", true);
      }
      $("#productDropdown").slideDown();
    });

    $(document).on("mouseup", function (e) {
      let container = $("#selectedProductDisplay");

      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $("#productDropdown").slideUp();
      }
    });

    // ********** Fetch Products from Main Site **********//
    function fetchAddProducts() {
      const manageOrderUrl = allaround_vars.redirecturl;

      let order_domain = "https://main.lukpaluk.xyz";
      //TODO: This is for local testing only and for staging
      if (manageOrderUrl.includes(".test")) {
        order_domain = "https://allaround.test";
      }

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
      let alarnd_artwork = [];

      const uploadedFilePathValue = $("#uploaded_file_path").val();

      if (uploadedFilePathValue) {
        try {
          alarnd_artwork = JSON.parse(uploadedFilePathValue);
        } catch (error) {
          console.error("Parsing error for uploaded_file_path:", error);
          alarnd_artwork = []; // Default to an empty array if parsing fails
        }
      }

      if (!productId || !quantity) {
        alert("Please select a product and specify quantity.");
        return;
      }

      const meta_data = [
        { key: "Color", value: color },
        { key: "Size", value: size },
        { key: "Art Position", value: artPos },
        { key: "Instruction Note", value: instructionNote },
      ];

      // Add each artwork URL as a separate meta field
      // if (alarnd_artwork && Array.isArray(alarnd_artwork)) {
      //   alarnd_artwork.forEach((url) => {
      //     // Create a URL object and use the pathname property
      //     const pathname = new URL(url).pathname;
      //     // Extract the filename from the pathname
      //     const filename = pathname.split("/").pop();
      //     const formattedValue = `<p>${filename}</p><a href="${url}" target="_blank"><img class="alarnd__artwork_img" src="${url}" /></a>`;
      //     meta_data.push({
      //       key: "Attachment",
      //       value: formattedValue,
      //     });
      //   });
      // }
      if (alarnd_artwork.length > 0) {
        meta_data.push({
          key: "Attachment",
          value: JSON.stringify(alarnd_artwork),
        });
      }

      const lineItem = {
        product_id: productId,
        product_html: productHTML,
        quantity: quantity,
        meta_data: meta_data,
      };

      console.log(lineItem);

      products.push(lineItem);
      alert("Product added to order.");
      $("#line_items").val(JSON.stringify(products));
      $("#om--create-new-order")
        .prop("disabled", false)
        .addClass("om_add_item_selected");
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
        const lineItemsValue = JSON.stringify(products);
        $("#line_items").val(lineItemsValue);

        // Check if the line_items array is empty and disable the button if it is
        if (lineItemsValue === "[]") {
          $("#om--create-new-order")
            .removeClass("om_add_item_selected")
            .prop("disabled", true)
            .css("opacity", 0.7);
        } else {
          $("#om--create-new-order").prop("disabled", false).css("opacity", 1);
        }
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
  }

  // ********** Shipping Method Update Ajax **********//
  // on submit of #shipping-method-form form
  $("#shipping-method-form").on("submit", function (event) {
    event.preventDefault();

    let order_id = allaround_vars.order_id;
    let order_domain = allaround_vars.order_domain;
    let shipping_method_id = document.querySelector(
      'select[name="shipping_method"]'
    ).value;
    let shipping_method_title = document.querySelector(
      'select[name="shipping_method"] option:checked'
    ).text;
    let nonce = allaround_vars.nonce;
    $(".om_shipping_submit").addClass("pulse");

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
          $(".om__shipping_total").text(
            data.data.shipping_total + " " + allaround_vars.currency_symbol
          );

          let itemsTotal = parseFloat(
            $(".om__items_subtotal")
              .text()
              .replace(/[^0-9.-]+/g, "")
          );
          let newOrderTotal = itemsTotal + parseFloat(data.data.shipping_total);
          $(".om__orderTotal").text(
            newOrderTotal.toFixed(2) + " " + allaround_vars.currency_symbol
          );

          $(".om_shipping_submit").removeClass("pulse");
          setTimeout(() => {
            $(".om_shipping_submit").fadeOut();
          }, 500);
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

  // ********** Artwork Graphics Magnific **********//
  const fileFormats = [
    "file-format-png",
    "file-format-jpg",
    "file-format-jpeg",
  ];
  fileFormats.forEach((format) => {
    $(`.uploaded_graphics.${format} a`).magnificPopup({
      type: "image",
      closeOnContentClick: true,
      closeBtnInside: true,
      fixedContentPos: true,
      mainClass: "mfp-no-margins mfp-with-zoom", // class to remove default margin from left and right side
      image: {
        verticalFit: true,
      },
      zoom: {
        enabled: true,
        duration: 300, // don't forget to change the duration also in CSS
      },
      closeMarkup:
        '<button title="%title%" type="button" class="mfp-close">×</button>',
    });
  });

  // ********** Update Prder General Comment **********//
  //
  // on change to #order_extra_attachments show the selected file names in the input field #uploaded_extra_file_path
  $("#order_extra_attachments").on("change", function () {
    let files = $(this)[0].files;
    let fileNames = [];
    for (let i = 0; i < files.length; i++) {
      fileNames.push(files[i].name);
    }
    $("#uploaded_extra_file_path").val(fileNames.join(", "));
  });

  $("#add-order-comment").on("click", function () {
    let orderComment = $("#order_general_comment").val();
    let postId = allaround_vars.post_id;
    let nonce = allaround_vars.nonce;
    $(this).addClass("ml_loading");

    let files = $("#order_extra_attachments")[0].files;

    if (orderComment === "" && files.length === 0) {
      alert("Please enter a comment or select files to upload.");
      return;
    }

    let formData = new FormData();
    formData.append("action", "save_order_general_comment");
    formData.append("order_general_comment", orderComment);
    formData.append("post_id", postId);
    formData.append("nonce", nonce);

    for (let i = 0; i < files.length; i++) {
      formData.append("order_extra_attachments[]", files[i]);
    }

    $.ajax({
      type: "POST",
      url: allaround_vars.ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          $("#add-order-comment").removeClass("ml_loading");
          $(".om_addOrderNote").removeClass("om_no_notes_addNote");
          $.magnificPopup.close();
          // Update the UI with the new comment and attachments
          if (orderComment !== "") {
            $(".om__orderGeneralComment_text").remove();
            $(".om__displayOrderComment").removeClass("om_no_notes");
            $(".om__displayOrderComment").append(`
                        <div class="om__orderGeneralComment_text">
                            <p>${response.data.order_general_comment}</p>
                        </div>
                    `);
          }

          if (response.data.attachments.length > 0) {
            $(".om__orderNoteFiles").remove(); // Remove existing attachments
            let attachmentsHtml = '<div class="om__orderNoteFiles">';
            response.data.attachments.forEach(function (attachment) {
              attachmentsHtml += `<a href="${attachment.url}" target="_blank">${attachment.name}</a><br>`;
            });
            attachmentsHtml += "</div>";
            $(".om__orderNoteFiles_container").removeClass("om_no_notes");
            $(".om__orderNoteFiles_container").append(attachmentsHtml);
          }

          // Clear the textarea and file input only if they were used
          if (orderComment !== "") {
            $("#order_general_comment").val(""); // Clear the textarea
          }
          if (files.length > 0) {
            $("#order_extra_attachments").val(""); // Clear the file input
            $("#uploaded_extra_file_path").val(""); // Clear the hidden input
          }
          Toastify({
            text: `Order Note Updated Successfully!`,
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
          alert("Something went wrong: " + response.data);
        }
      },
      error: function (error) {
        alert("An error occurred: " + error.responseText);
      },
    });
  });

  $(".om_addOrderNote ").magnificPopup({
    items: {
      src: "#om__orderNote_container",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // .alarnd__artwork_img src is not jpg, png or jpeg change to document.png
  $(".alarnd__artwork_img").each(function () {
    if (
      !/\.(png|jpg|jpeg|pdf)$/i.test($(this).attr("src")) &&
      $(this).attr("src") !== ""
    ) {
      $(this).attr("src", `${allaround_vars.assets}images/document.png`);
    }
  });

  $(window).on("load", function () {});
})(jQuery); /*End document ready*/

// ********** Mockup Image Upload Post Request **********//
document.addEventListener("DOMContentLoaded", function () {
  // Use a parent element that exists when the DOM is loaded
  let orderId = allaround_vars.order_id;
  let mainTable = document.getElementById("tableMain");
  if (!mainTable) return;

  async function initializeMockupColumns(mainTable, orderId) {
    const mainTableTrs = mainTable.querySelectorAll("tbody > tr");
    if (mainTableTrs.length === 0) return;

    for (const column of mainTableTrs) {
      const productId = column.getAttribute("data-product_id");
      const thisTr = column;

      // Add loading indicator to the row
      const loadingIndicator = document.createElement("td");
      loadingIndicator.className = "loading-indicator";
      loadingIndicator.innerHTML = `
      <div class="lds-spinner-wrap">
        <div class="lds-spinner">
          <div></div><div></div><div></div><div></div>
          <div></div><div></div><div></div><div></div>
          <div></div><div></div><div></div><div></div>
        </div>
        <span>Loading...</span>
      </div>
    `;
      thisTr.appendChild(loadingIndicator);

      // Initialize columns by checking directories
      const formData = new FormData();
      formData.append("action", "initialize_mockup_columns");
      formData.append("order_id", orderId);
      formData.append("product_id", productId);

      try {
        const response = await fetch(allaround_vars.ajax_url, {
          method: "POST",
          body: formData,
        });
        const data = await response.json();

        if (data.success && data.data.mockup_versions) {
          let maxVersion = 0;
          for (const mockup of data.data.mockup_versions) {
            maxVersion = Math.max(maxVersion, mockup.version);
            const mockupVersion = mockup.version;
            const mockupTd = document.createElement("td");
            mockupTd.className = `item_mockup_column om_expired_mockups`;
            mockupTd.setAttribute("data-version_number", mockupVersion);

            mockupTd.innerHTML = `
            <div class="lds-spinner-wrap" style="display: flex;">
              <div class="lds-spinner">
                ${"<div></div>".repeat(12)}
              </div>
            </div>
            <input type="hidden" class="hidden_mockup_url" name="mockup-image-v${mockupVersion}" value="">
            <div class="mockup-image">Loading mockup images...</div>
            <input class="file-input__input" name="file-input[${productId}]" id="file-input-${productId}-v${mockupVersion}" data-version="V${mockupVersion}" type="file" placeholder="Upload Mockup" multiple>
            <label class="file-input__label" for="file-input-${productId}-v${mockupVersion}">
              <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
              </svg>
              <span>Upload file</span>
            </label>
          `;
            thisTr.appendChild(mockupTd);

            // Fetch the image URLs for this column
            await fetchImageURLs(orderId, productId, mockupVersion, mockupTd);
          }
          initialAddNewMockupColumn(thisTr, maxVersion + 1);
          populateTableHeader();
          if (maxVersion) {
            const maxColumn = thisTr.querySelector(
              `td[data-version_number="${maxVersion}"]`
            );
            maxColumn.classList.remove("om_expired_mockups");
            maxColumn.classList.add("last_send_version");
            createDeleteButton(maxColumn, "V" + maxVersion, productId);
          }
        }
      } catch (error) {
        console.error("Error initializing mockup columns:", error);
      } finally {
        // Remove loading indicator
        thisTr.removeChild(loadingIndicator);
      }
    }
  }

  function fetchImageURLs(orderId, productId, version, column) {
    let formData = new FormData();
    formData.append("action", "fetch_mockup_files");
    formData.append("order_id", orderId);
    formData.append("product_id", productId);
    formData.append("version", version);

    fetch(allaround_vars.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data.file_list) {
          let fileList = data.data.file_list;

          // Ensure fileList is always treated as an array
          if (!Array.isArray(fileList)) {
            fileList = Object.values(fileList);
          }

          let hiddenMockupInput = column.querySelector(".hidden_mockup_url");
          let mockupImageContainer = column.querySelector(".mockup-image");

          if (hiddenMockupInput && mockupImageContainer) {
            hiddenMockupInput.value = fileList.join(",");
            mockupImageContainer.innerHTML = fileList
              .map(
                (file) =>
                  `<a href="${file}"><img src="${file}" alt="Mockup Image"  class="om_mockup-thumbnail"></a>`
              )
              .join("");
            // Pass the newly added images to the tooltip function
            const newImages = mockupImageContainer.querySelectorAll(
              ".om_mockup-thumbnail"
            );
            attachTooltipToProductThumbnails(newImages);
          }
          // Removing the spinner after the images are loaded
          column.querySelectorAll(".lds-spinner-wrap").forEach((wrap) => {
            wrap.style.display = "none";
          });
        }
      })
      .catch((error) => {
        console.error("Error fetching mockup files:", error);
      });
  }

  function initialAddNewMockupColumn(row, nextVersion) {
    let productId = row.getAttribute("data-product_id");

    // Check if a column for the next version already exists
    if (row.querySelector('td[data-version_number="' + nextVersion + '"]')) {
      return;
    }

    let newMockupTd = document.createElement("td");
    newMockupTd.className = "item_mockup_column";
    newMockupTd.setAttribute("data-version_number", +nextVersion);
    newMockupTd.innerHTML =
      '<div class="lds-spinner-wrap"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>' +
      '<input type="hidden" class="hidden_mockup_url" name="mockup-image-v' +
      nextVersion +
      '" value="">' +
      '<div class="mockup-image">Select Mockup Image JS</div>' +
      '<input class="file-input__input" name="file-input[' +
      productId +
      ']" id="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '" data-version="V' +
      nextVersion +
      '" type="file" placeholder="Upload Mockup" multiple >' +
      '<label class="file-input__label" for="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '">' +
      '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">' +
      '<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>' +
      "</svg>" +
      "<span>Upload file</span></label>";

    row.appendChild(newMockupTd);
  }

  initializeMockupColumns(mainTable, orderId);

  document.body.addEventListener("change", function (event) {
    if (event.target.classList.contains("file-input__input")) {
      let input = event.target;
      let files = input.files;
      let version = input.getAttribute("data-version");
      let orderId = document.querySelector('input[name="order_id"]').value;
      let postId = document.querySelector('input[name="post_id"]').value;

      let mockupColumn = input.closest(".item_mockup_column");
      let spinner = mockupColumn.querySelector(".lds-spinner-wrap");
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

      let formData = new FormData();
      Array.from(files).forEach((file) => {
        formData.append("file[]", file);
      });
      formData.append("order_id", orderId);
      formData.append("post_id", postId);
      formData.append("product_id", productId);
      formData.append("version", version);

      uploadFile(formData, input, version); // Pass the input element here
    }
  });

  function uploadFile(formData, input, version) {
    let mockupColumn = input.closest(".item_mockup_column");
    const productId = mockupColumn
      .closest("tr")
      .getAttribute("data-product_id");
    let mockupInput = mockupColumn.querySelector(
      'input[type="hidden"].hidden_mockup_url'
    );
    let spinner = mockupColumn.querySelector(".lds-spinner-wrap");
    let mockupImageContainer = mockupColumn.querySelector(".mockup-image");
    const tableMain = mockupColumn.closest("table#tableMain");
    const tableBody = mockupColumn.closest("table#tableMain > tbody");
    let productTitle = mockupColumn
      .closest("tr")
      .querySelector(".product_item_title").innerText;

    let sendProofButton = document.querySelector("#send-proof-button");

    tableBody.style.pointerEvents = "none";

    fetch(allaround_vars.fileupload_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(
            "Network response was not ok: " + response.statusText
          );
        }
        return response.json();
      })
      .then((responses) => {
        console.log("Server responses:", responses);

        tableBody.style.pointerEvents = "all";

        responses.forEach((data, index) => {
          if (data.success && data.file_path) {
            if (mockupInput.value) {
              mockupInput.value += "," + data.file_path; // Append new file path
            } else {
              mockupInput.value = data.file_path;
            }

            // Create new anchor tag with image
            const newAnchor = document.createElement("a");
            newAnchor.href = data.file_path;
            newAnchor.className = "mfp-image";
            newAnchor.innerHTML = `<img src="${data.file_path}" alt="Mockup Image" class="om_mockup-thumbnail">`;

            // Append the new anchor tag to the mockup image container
            mockupImageContainer.appendChild(newAnchor);

            // Apply the tooltip to the newly added image
            attachTooltipToProductThumbnails([newAnchor.querySelector("img")]);
          }
        });

        // Check if a new mockups version column should be added
        let nextVersion = parseInt(version.replace("V", "")) + 1;
        addNewMockupHeader(nextVersion);
        addNewMockupColumn(input, nextVersion);

        // Check if delete button exists, if not create it
        if (!mockupColumn.querySelector("#om_delete_mockup")) {
          createDeleteButton(mockupColumn, version, productId);
        }

        tableMain.scrollLeft = tableMain.scrollWidth;

        // remove the delete button from the previous version mockupColumn
        let previousMockupColumn = mockupColumn.previousElementSibling;
        if (previousMockupColumn) {
          previousMockupColumn.classList.add("om_expired_mockups");
          previousMockupColumn.classList.remove("last_send_version");
          let previousDeleteButton =
            previousMockupColumn.querySelector("#om_delete_mockup");
          if (previousDeleteButton) {
            previousDeleteButton.remove();
          }
        }

        if (mockupImageContainer) {
          Array.from(mockupImageContainer.childNodes).forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
              child.remove();
            }
          });
        }

        if (sendProofButton) {
          sendProofButton.removeAttribute("disabled");
        }

        Toastify({
          text: `Mockup ${version} uploaded successfully for ${productTitle}!`,
          duration: 3000,
          close: true,
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          stopOnFocus: true, // Prevents dismissing of toast on hover
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();
        spinner.style.display = "none";
      })
      .catch((error) => {
        spinner.style.display = "none";
        tableBody.style.pointerEvents = "all";
        console.error("There was a problem with your fetch operation:", error);

        Toastify({
          text: `There was a problem uploading file: ${error.message}`,
          duration: 3000,
          close: true,
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          stopOnFocus: true, // Prevents dismissing of toast on hover
          style: {
            background: "linear-gradient(to right, #cc3366, #a10036)",
          },
        }).showToast();
      });
  }

  function createDeleteButton(mockupColumn, version, productId) {
    let deleteButton = document.createElement("button");
    deleteButton.setAttribute("data-product-id", productId);
    deleteButton.id = "om_delete_mockup";
    deleteButton.setAttribute("data-order-id", allaround_vars.order_id);
    deleteButton.setAttribute("data-version", version);
    deleteButton.textContent = "Delete Mockup";
    mockupColumn.insertBefore(deleteButton, mockupColumn.firstChild);
  }

  function addNewMockupHeader(nextVersion) {
    let headerRow = document.querySelector("thead tr");
    let headers = headerRow.querySelectorAll("th");
    let headerExists = Array.from(headers).some((header) =>
      header.innerHTML.includes(`Mockups V${nextVersion}`)
    );

    // If a header with the same version already exists, skip adding a new one
    if (headerExists) return;

    let newHeader = document.createElement("th");
    newHeader.className = "head";
    newHeader.innerHTML = `<strong>Mockups V${nextVersion}</strong>`;
    headerRow.appendChild(newHeader);
  }

  function addNewMockupColumn(input, nextVersion) {
    let row = input.closest("tr");
    let productId = row.getAttribute("data-product_id");

    // Check if a column for the next version already exists
    if (row.querySelector('td[data-version_number="' + nextVersion + '"]')) {
      return;
    }

    let newMockupTd = document.createElement("td");
    newMockupTd.className = "item_mockup_column";
    newMockupTd.setAttribute("data-version_number", +nextVersion);
    newMockupTd.innerHTML =
      '<div class="lds-spinner-wrap"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>' +
      '<input type="hidden" class="hidden_mockup_url" name="mockup-image-v' +
      nextVersion +
      '" value="">' +
      '<div class="mockup-image">Select Mockup Image JS</div>' +
      '<input class="file-input__input" name="file-input[' +
      productId +
      ']" id="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '" data-version="V' +
      nextVersion +
      '" type="file" placeholder="Upload Mockup" multiple >' +
      '<label class="file-input__label" for="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '">' +
      '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">' +
      '<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>' +
      "</svg>" +
      "<span>Upload file</span></label>";

    row.appendChild(newMockupTd);
  }

  function createTooltip(imageUrl, parentElement) {
    let tooltipSpan = document.createElement("span");
    tooltipSpan.className = "tooltipContainer tooltip-span";
    tooltipSpan.innerHTML =
      '<img width="300" height="300" src="' +
      imageUrl +
      '" class="mockup_tooltip" alt="" decoding="async" srcset="' +
      imageUrl +
      '" sizes="(max-width: 300px) 100vw, 300px">';
    parentElement.appendChild(tooltipSpan);
    return tooltipSpan;
  }

  function attachTooltipToProductThumbnails(images) {
    // let images = document.querySelectorAll(".mockup-image img");

    images.forEach(function (image) {
      let tooltipSpan;

      image.addEventListener("mouseenter", function () {
        let parentElement = image.closest("td.item_mockup_column");
        tooltipSpan = createTooltip(image.src, parentElement);
        tooltipSpan.style.display = "block";
      });

      image.addEventListener("mousemove", function (e) {
        let x = e.clientX,
          y = e.clientY;
        let tooltipWidth =
          tooltipSpan.offsetWidth || tooltipSpan.getBoundingClientRect().width;
        tooltipSpan.style.left = x + 20 + "px";
        tooltipSpan.style.top = y - 20 + "px";
      });

      image.addEventListener("mouseleave", function () {
        tooltipSpan.style.display = "none";
        tooltipSpan.remove();
      });

      let imageAnchor = image.closest("a");
      // Initialize Magnific Popup on click
      jQuery(imageAnchor).magnificPopup({
        type: "image",
        closeOnContentClick: true,
        closeBtnInside: true,
        fixedContentPos: true,
        mainClass: "mfp-no-margins mfp-with-zoom", // class to remove default margin from left and right side
        image: {
          verticalFit: true,
        },
        zoom: {
          enabled: true,
          duration: 300, // don't forget to change the duration also in CSS
        },
        closeMarkup:
          '<button title="%title%" type="button" class="mfp-close">×</button>',
      });
    });
  }

  document.body.addEventListener("click", function (event) {
    if (event.target.id === "om_delete_mockup") {
      let button = event.target;
      let orderId = button.getAttribute("data-order-id");
      let productId = button.getAttribute("data-product-id");
      let version = button.getAttribute("data-version");
      let productTitle = button
        .closest("tr")
        .querySelector(".product_item_title").innerText;

      if (confirm("Are you sure you want to delete this mockup?")) {
        let data = {
          action: "delete_mockup_folder",
          order_id: orderId,
          product_id: productId,
          version: version,
          security: allaround_vars.nonce, // Add nonce for security
        };

        fetch(allaround_vars.ajax_url, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          },
          body: new URLSearchParams(data).toString(),
        })
          .then((response) => response.json())
          .then((response) => {
            if (response.success) {
              let currentTd = button.closest("td.item_mockup_column");
              let currentVersion = parseInt(
                currentTd.getAttribute("data-version_number").replace("V", "")
              );
              let prevVersion =
                "V" +
                (parseInt(
                  currentTd.getAttribute("data-version_number").replace("V", "")
                ) -
                  1);

              let previousMockupColumn = currentTd.previousElementSibling;
              if (
                previousMockupColumn &&
                previousMockupColumn.classList.contains("item_mockup_column")
              ) {
                createDeleteButton(
                  previousMockupColumn,
                  prevVersion,
                  productId
                );
                previousMockupColumn.classList.remove("om_expired_mockups");
              }

              // Check if any of the tbody>tr>td has a data-version_number greater than currentVersion
              let hasHigherVersion = Array.from(
                document.querySelectorAll("tbody tr td.item_mockup_column")
              ).some((td) => {
                return (
                  parseInt(
                    td.getAttribute("data-version_number").replace("V", "")
                  ) >
                  currentVersion + 1
                );
              });

              console.log("Has higher version:", hasHigherVersion);

              // Update version numbers for subsequent columns
              let subsequentTds = Array.from(
                currentTd
                  .closest("tr")
                  .querySelectorAll("td.item_mockup_column")
              ).filter((td) => {
                return (
                  parseInt(
                    td.getAttribute("data-version_number").replace("V", "")
                  ) > currentVersion
                );
              });

              subsequentTds.forEach((td) => {
                let newVersion =
                  parseInt(
                    td.getAttribute("data-version_number").replace("V", "")
                  ) - 1;
                td.setAttribute("data-version_number", "V" + newVersion);
                td.querySelector(
                  ".hidden_mockup_url"
                ).name = `mockup-image-v${newVersion}`;
                td.querySelector(".file-input__input").setAttribute(
                  "data-version",
                  "V" + newVersion
                );
                td.querySelector(".file-input__input").id =
                  "file-input-" + productId + "-v" + newVersion;
                td.querySelector(".file-input__label").setAttribute(
                  "for",
                  "file-input-" + productId + "-v" + newVersion
                );
              });

              currentTd.remove();

              if (!hasHigherVersion) {
                populateTableHeader();
              }

              Toastify({
                text: `Mockup ${version} for ${productTitle} deleted successfully!`,
                className: "info",
                gravity: "bottom", // `top` or `bottom`
                position: "right", // `left`, `center` or `right`
                style: {
                  background: "linear-gradient(to right, #cc3366, #a10036)",
                },
              }).showToast();
            } else {
              Toastify({
                text: `Error: ${response.data.message}!`,
                className: "info",
                gravity: "bottom", // `top` or `bottom`
                position: "right", // `left`, `center` or `right`
                style: {
                  background: "linear-gradient(to right, #cc3366, #a10036)",
                },
              }).showToast();
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            Toastify({
              text: `An error occurred. Please try again.`,
              className: "info",
              gravity: "bottom", // `top` or `bottom`
              position: "right", // `left`, `center` or `right`
              style: {
                background: "linear-gradient(to right, #cc3366, #a10036)",
              },
            }).showToast();
          });
      }
    }
  });

  function populateTableHeader() {
    // Get the table
    let table = document.getElementById("tableMain");
    if (!table) return;

    // Get all rows in the tbody
    let rows = table
      .getElementsByTagName("tbody")[0]
      .getElementsByTagName("tr");
    if (rows.length === 0) return;

    // Initialize the maximum column count for 'item_mockup_column'
    let maxMockupColumns = 0;

    // Loop through all rows to find the maximum number of 'item_mockup_column' tds
    for (let i = 0; i < rows.length; i++) {
      let mockupColumns =
        rows[i].getElementsByClassName("item_mockup_column").length;
      if (mockupColumns > maxMockupColumns) {
        maxMockupColumns = mockupColumns;
      }
    }

    // Get the thead element and the first row in the thead
    let thead = table.getElementsByTagName("thead")[0];
    if (thead.length === 0) return;
    let headerRow = thead.getElementsByTagName("tr")[0];
    if (headerRow.length === 0) return;

    // Remove any previously added dynamic th elements
    while (headerRow.children.length > 3) {
      headerRow.removeChild(headerRow.lastChild);
    }

    // Create the appropriate number of th elements
    for (let i = 0; i < maxMockupColumns; i++) {
      let th = document.createElement("th");
      th.className = "head";
      th.innerHTML = "<strong>Mockups V" + (i + 1) + "</strong>";
      headerRow.appendChild(th);
    }
  }
});
