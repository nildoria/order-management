(function ($) {
  ("use strict");

  // Function to check if the current user is an Administrator
  function isAdmin() {
    return allaround_vars.user_role === "administrator";
  }

  // Function to check if the current user is an Employee
  function isEmployee() {
    return allaround_vars.user_role === "author";
  }

  // Function to check if the current user is an Designer
  function isDesigner() {
    return allaround_vars.user_role === "contributor";
  }

  // ********** Order Management Scripts Start **********//
  const mainTable = document.getElementById("tableMain");
  // ********** Mockup Image Upload Post Request **********//

  // ********** Send Mockup to ArtWork Post Request **********//

  $("#send-proof-button").on("click", function () {
    let orderId = allaround_vars.order_id;
    let orderNumber = allaround_vars.order_number;
    let root_domain = allaround_vars.redirecturl;
    let customerName =
      $("#billing_first_name").val() + " " + $("#billing_last_name").val();
    let customerEmail = $("#billing_email").val();
    let customerPhone = $("#billing_phone").val();
    let commentText = $("#mockup-proof-comments").val() || "";
    let orderType = $(this).data("order_type");
    let prevClientType = $(this).data("prev_client_type");
    let orderSource = $(this).data("order_source");
    let totalPaid = $(".om__orderTotal").text().replace("₪", "").trim();

    console.log("Email Address:", customerEmail);

    if (!mainTable) {
      alert("Nothing to send! Please add Mockup to the order.");
      return;
    }

    $("#send-proof-button").addClass("ml_loading");

    // Select the first <tr> element
    let firstTr = document.querySelector("tbody > tr");

    // Get all <td> elements with the 'item_mockup_column' class within the first <tr>
    let itemMockupColumns = firstTr.querySelectorAll(".item_mockup_column");

    // Select the last element in the NodeList
    let lastItemMockupColumn = itemMockupColumns[itemMockupColumns.length - 1];

    // If the column is not ready, show an alert
    if (!lastItemMockupColumn) {
      alert("Hey quicky! Wait for proofs to load, please.");
      $("#send-proof-button").removeClass("ml_loading");
      return;
    }

    // Get the value of the 'data-version_number' attribute from the last <td>
    let version = lastItemMockupColumn.getAttribute("data-version_number");

    // If version is missing, stop the process
    if (!version) {
      alert("Hey quicky! Wait for proofs to load, please.");
      $("#send-proof-button").removeClass("ml_loading");
      return;
    }

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

    // Check if imageUrls are empty
    if (imageUrls.length === 0) {
      alert("Hey quicky! Wait for proofs to load, please.");
      $("#send-proof-button").removeClass("ml_loading");
      return;
    }

    let imageUrlString = imageUrls.join(",");
    console.log(imageUrlString); // Outputs: url1,url2,url3...

    const webhookData = {
      om_status: "proof_sent_successfully",
      order_id: orderId,
      order_number: orderNumber,
      customer_name: customerName,
      customer_email: customerEmail,
      customer_phone: customerPhone,
    };

    let data = {
      comment_text: commentText,
      order_id: orderId,
      order_number: orderNumber,
      image_urls: imageUrlString,
      proof_status: proofStatus,
      customer_name: customerName,
      customer_email: customerEmail,
      customer_phone: customerPhone,
      total_paid: totalPaid,
      order_type: orderType,
      prev_client_type: prevClientType,
      order_source: orderSource,
    };

    // Check if the order type is "company"
    if ($("#order_type").val() === "company") {
      data.logo_darker = $("#dark_logo").val();
      data.logo_lighter = $("#lighter_logo").val();
    }

    let requestData = {
      action: "send_proof_version",
      post_id: allaround_vars.post_id,
      version: version,
    };

    function handleResponse(data) {
      console.log("handleResponse log:", data);
    }

    let proof_api = "";
    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Proof API for test environment
      proof_api =
        "https://artwork.lukpaluk.xyz/wp-json/artwork-review/v1/add-proof";
    } else {
      // Proof API for production environment
      proof_api =
        "https://artwork.allaround.co.il/wp-json/artwork-review/v1/add-proof";
    }

    fetch(proof_api, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((errData) => {
            throw new Error(errData.message || "Failed to send proof.");
          });
        }
        return response.json();
      })
      .then((data) => {
        console.log("Success:", data);

        if (data.success) {
          // Extract and use the returned data
          webhookData.post_url = data.post_url; // Assuming `post_url` exists in response
          webhookData.message = data.message || "Proof sent successfully";

          // Call the function to send data to the webhook
          sendDataToWebhook(webhookData);

          // Trigger additional AJAX request to update proof status
          ml_send_ajax(requestData, handleResponse);

          // Show a success toast
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

          $("#send-proof-button").removeClass("ml_loading");

          // Optionally reload the page after a short delay
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          // Handle cases where the response is not successful
          throw new Error(data.message || "Unknown error occurred.");
        }
      })
      .catch((error) => {
        console.error("Error:", error);

        // Show an error toast
        Toastify({
          text: `Error: ${error.message}`,
          className: "info",
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          style: {
            background: "linear-gradient(to right, #cc3366, #a10036)",
          },
        }).showToast();

        alert("There was an error sending the proof. Please try again.");
        $("#send-proof-button").removeClass("ml_loading");
      });
  });

  $(document).on("click", "#delete_order_transient", function () {
    let order_id = allaround_vars.order_id;

    let requestData = {
      action: "update_order_transient",
      order_id: order_id,
    };

    function handleResponse(response) {
      alert("Updated Transient successfully");
      location.reload(); // Refresh the page to see the new item
    }

    ml_send_ajax(requestData, handleResponse);
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

    const post_id = allaround_vars.post_id;
    const client_id = $("#client-select").val();
    const order_source = $("#om__order_source").data("order_source");

    // stock_management_addition_limon - set variables for delete item
    const currentItem = $(this).closest("tr.om__orderRow");
    const sku = currentItem.data("item-sku");
    const is_variable = currentItem.data("is_variable");
    const variations = currentItem.find(".item_name_variations");
    const colorItem = variations.find("[data-meta_key='Color']");
    const sizeItem = variations.find("[data-meta_key='Size']");
    const inputField = currentItem.find(".item-quantity-input");
    let old_quantity = inputField.data("item-qty");

    let itemType = "quantity";
    let colorValue = "";
    let sizeValue = "";

    if (colorItem.length !== 0) {
      itemType = "group";
      colorValue = colorItem.find("strong").text();
      sizeValue = sizeItem.find("strong").text();
    }

    console.log("is_variable", is_variable);
    console.log("is_variable typeof", typeof is_variable);

    if (is_variable == true) {
      itemType = "variable";
    }

    // get different from old quantity to new quantity
    old_quantity = parseInt(old_quantity);

    const stockReqsData = {
      source: allaround_vars.home_url,
      action: "item_duplicate",
      order_id: order_id,
      data: {},
    };

    if (itemType === "group") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          [colorValue]: {
            [sizeValue.toLowerCase()]: old_quantity,
          },
        },
      };
    }

    if (itemType === "quantity") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          qty: old_quantity,
        },
      };
    }

    console.log("stockReqsData", stockReqsData);
    // end stock_management_addition_limon - set variables for delete item

    $(".sitewide_spinner").addClass("loading");

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
      $(".sitewide_spinner").removeClass("loading");
      alert("Item duplicated successfully");
      location.reload(); // Refresh the page to see the new item
    }

    fetch(`${order_domain}/wp-json/update-order/v1/duplicate-delete-to-order`, {
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
        console.log("Client ID:", client_id);
        console.log("Order Source:", order_source);

        console.log("Response message:", data.message);
        console.log("Newly Added Total:", data.newly_added_total);

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
          allaround_vars.ajax_url,
          handleOrderSourceData,
          function (response) {
            console.log("Order source handled successfully:", response);
          }
        ).fail(function (error) {
          console.error("Error handling order source:", error);
          alert("Error handling order source: " + error.statusText);
        });

        // stock_management_addition_limon - trigger item duplicate `sendRequestToUpdateStock`
        if (is_variable != "true") {
          sendRequestToUpdateStock("stock-update", stockReqsData);
        }
        ml_send_ajax(requestData, handleResponse);
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred: " + error.message);
      })
      .finally(() => {
        $(".sitewide_spinner").removeClass("loading");
      });
  });

  // ********** Delete Order Item **********//
  $(document).on("click", ".om_delete_item", function () {
    let order_id = allaround_vars.order_id;
    let item_id = $(this).siblings('input[name="item_id"]').val();
    let order_domain = allaround_vars.order_domain;

    const post_id = allaround_vars.post_id;
    const client_id = $("#client-select").val();
    const order_source = $("#om__order_source").data("order_source");

    // stock_management_addition_limon - set variables for delete item
    const currentItem = $(this).closest("tr.om__orderRow");
    const sku = currentItem.data("item-sku");
    const is_variable = currentItem.data("is_variable");
    const variations = currentItem.find(".item_name_variations");
    const colorItem = variations.find("[data-meta_key='Color']");
    const sizeItem = variations.find("[data-meta_key='Size']");
    const inputField = currentItem.find(".item-quantity-input");
    let old_quantity = inputField.data("item-qty");

    let itemType = "quantity";
    let colorValue = "";
    let sizeValue = "";

    if (colorItem.length !== 0) {
      itemType = "group";
      colorValue = colorItem.find("strong").text();
      sizeValue = sizeItem.find("strong").text();
    }

    // get different from old quantity to new quantity
    old_quantity = parseInt(old_quantity);

    // add minus so it can be added to the stock
    old_quantity = old_quantity * -1;

    console.log("is_variable", is_variable);
    console.log("is_variable typeof", typeof is_variable);

    if (is_variable == true) {
      itemType = "variable";
    }

    const stockReqsData = {
      source: allaround_vars.home_url,
      action: "item_delete",
      order_id: order_id,
      data: {},
    };

    if (itemType === "group") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          [colorValue]: {
            [sizeValue.toLowerCase()]: old_quantity,
          },
        },
      };
    }

    if (itemType === "quantity") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          qty: old_quantity,
        },
      };
    }

    console.log("stockReqsData", stockReqsData);
    // end stock_management_addition_limon - set variables for delete item

    $(".sitewide_spinner").addClass("loading");

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
      $(".sitewide_spinner").removeClass("loading");
      alert("Item deleted successfully");
      location.reload(); // Refresh the page to see the new item
    }

    fetch(`${order_domain}/wp-json/update-order/v1/duplicate-delete-to-order`, {
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
        // Use the message and deleted_item_total from the response
        console.log("Client ID:", client_id);
        console.log("Order Source:", order_source);

        console.log("Response message:", data.message);
        console.log("Newly Added Total:", data.deleted_item_total);

        // Prepare data to send to the server to handle order source
        const handleOrderSourceData = {
          action: "handle_order_source_action",
          post_id: post_id,
          client_id: client_id,
          order_source: order_source,
          total_price: data.deleted_item_total,
        };

        // Send data to the server via AJAX
        $.post(
          allaround_vars.ajax_url,
          handleOrderSourceData,
          function (response) {
            console.log("Order source handled successfully:", response);
          }
        ).fail(function (error) {
          console.error("Error handling order source:", error);
          alert("Error handling order source: " + error.statusText);
        });

        // stock_management_addition_limon - trigger item delete `sendRequestToUpdateStock`
        if (is_variable != "true") {
          sendRequestToUpdateStock("stock-update", stockReqsData);
        }
        ml_send_ajax(requestData, handleResponse);
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred: " + error.message);
      })
      .finally(() => {
        $(".sitewide_spinner").removeClass("loading");
      });
  });

  // ********** Order Meta Update Script **********//
  $(document).on("click", "[id^=update-item-meta-btn_]", function () {
    const $this = $(this);
    const itemId = $this.data("item_id");
    const orderId = $this.data("order_id");
    const newSize = $("#size-input_" + itemId).val();
    const newColor = $("#color-input_" + itemId).val();
    const newArtPosition = $("#art-position-input_" + itemId).val();
    const newInstructionNote = $("#instruction-note-input_" + itemId).val();

    $this.addClass("ml_loading");

    // stock_management_addition_limon - set variables for update meta
    const group_item = $(".om__orderRow[data-product_id=" + itemId + "]");
    const sku = group_item.data("item-sku");
    const is_variable = group_item.data("is_variable");
    const variations = group_item.find(".item_name_variations");
    const colorItem = variations.find("[data-meta_key='Color']");
    const sizeItem = variations.find("[data-meta_key='Size']");
    const inputField = group_item.find(".item-quantity-input");
    let old_quantity = inputField.data("item-qty");

    let itemType = "quantity";
    let colorValue = "";
    let sizeValue = "";

    if (colorItem.length !== 0) {
      itemType = "group";
      colorValue = colorItem.find("strong").text();
      sizeValue = sizeItem.find("strong").text();
    }

    console.log("is_variable", is_variable);
    console.log("is_variable typeof", typeof is_variable);

    if (is_variable == true) {
      itemType = "variable";
    }

    // get different from old quantity to new quantity
    old_quantity = parseInt(old_quantity);

    const stockReqsData = {
      source: allaround_vars.home_url,
      action: "meta_update",
      order_id: orderId,
      data: {},
    };

    if (itemType === "group") {
      stockReqsData.data[sku] = {
        type: itemType,
        value: old_quantity,
        old_meta: {
          color: colorValue,
          size: sizeValue,
        },
        new_meta: {
          color: newColor,
          size: newSize,
        },
      };
    }

    console.log("stockReqsData", stockReqsData);

    // end stock_management_addition_limon - set variables for update meta

    // Collect only the fields that have changed
    let newItemMeta = {
      order_id: orderId,
      item_id: itemId,
    };

    if (newSize) newItemMeta.size = newSize;
    if (newColor) newItemMeta.color = newColor;
    if (newArtPosition) newItemMeta.art_position = newArtPosition;
    newItemMeta.instruction_note = newInstructionNote;

    // stock_management_addition_limon - added 3rd parameter to updateItemMeta `stockReqsData`
    updateItemMeta(newItemMeta, $this, stockReqsData, is_variable);
  });

  function updateItemMeta(
    newItemMeta,
    $this,
    stockReqsData,
    is_variable = false
  ) {
    let order_domain = allaround_vars.order_domain;

    let requestData = {
      action: "update_order_transient",
      order_id: newItemMeta.order_id,
    };

    function handleResponse(response) {
      $this.removeClass("ml_loading");
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
          // stock_management_addition_limon - trigger meta update `sendRequestToUpdateStock`
          if (is_variable != "true") {
            sendRequestToUpdateStock("meta-update", stockReqsData);
          }
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
      })
      .finally(() => {
        $this.removeClass("ml_loading");
      });
  }

  function isValidDataFormat(data) {
    if (!data || typeof data !== "object") return false;

    // Check the `data` property exists and is an object
    if (!data.data || typeof data.data !== "object") return false;

    // Iterate over the keys in the `data` property
    for (const key in data.data) {
      if (!key.trim() || key === "undefined") {
        // If a key is empty, consists of whitespace, or explicitly "undefined", return false
        return false;
      }

      const subData = data.data[key];
      if (!subData || typeof subData !== "object") return false;

      // Ensure the `data` property of the subData is present and valid
      if (!subData.data || typeof subData.data !== "object") {
        return false;
      }
    }

    return true;
  }

  // stock_management_addition_limon - create function to send request to update stock
  function sendRequestToUpdateStock(endpoint, requestData) {
    try {
      if (!isValidDataFormat(requestData)) {
        console.log("no_sku_found", requestData);
        return false;
      }

      const xhr = new XMLHttpRequest();
      const rest_url = allaround_vars.rest_url + "/update-stock";
      xhr.open("POST", rest_url, true);

      // Add necessary headers (e.g., Content-Type for JSON and Authorization if needed)
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.setRequestHeader("X-WP-Nonce", allaround_vars.rest_nonce);

      requestData = {
        action: endpoint,
        requestData: requestData,
      };

      // Handle response
      xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          if (xhr.status >= 200 && xhr.status < 300) {
            console.log("Request succeeded:", xhr.responseText);

            // Parse response
            const response = JSON.parse(xhr.responseText || "{}");

            if (response.message) {
              // Show success message using Toastify
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
            } else {
              // Show alert if message is missing
              alert(
                "Stock update: Response received but no message was found."
              );
            }
          } else {
            alert(
              `Stock update: Request failed with status ${xhr.status}: ${xhr.statusText}`
            );
          }
        }
      };

      // Handle network errors
      xhr.onerror = function () {
        alert(
          "Stock update: Unable to send data to the endpoint. Please check your connection or endpoint settings."
        );
      };

      // Send the request
      xhr.send(JSON.stringify(requestData));
    } catch (error) {
      alert(
        "Stock update: An error occurred while trying to send the request: " +
          error.message
      );
    }
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
    const metaId = $(this).data("meta_id");
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
        art_meta_id: metaId,
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

          // Check if an img tag already exists
          let img = link.find("img");
          if (img.length) {
            // Update the existing img src
            if (/\.(png|jpg|jpeg)$/i.test(fileInfo)) {
              img.attr("src", fileInfo);
            } else if (/\.(pdf)$/i.test(fileInfo)) {
              img.attr("src", `${themeAssets}images/pdf-icon.svg`);
            } else {
              img.attr("src", `${themeAssets}images/document.png`);
            }
          } else {
            // Create a new img tag and append it to the a tag
            img = $('<img class="alarnd__artwork_img">');
            if (/\.(png|jpg|jpeg)$/i.test(fileInfo)) {
              img.attr("src", fileInfo);
            } else if (/\.(pdf)$/i.test(fileInfo)) {
              img.attr("src", `${themeAssets}images/pdf-icon.svg`);
            } else {
              img.attr("src", `${themeAssets}images/document.png`);
            }
            link.append(img);
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

  // ********** Handle Delete Artwork button click **********//
  $(document).on("click", ".om__DeleteArtwork", function (e) {
    e.preventDefault();
    const $this = $(this);
    const order_domain = allaround_vars.order_domain;
    const orderId = allaround_vars.order_id;
    const itemId = $this.data("item_id");
    const metaId = $this.data("meta_id");
    let artworkContainer = $this.closest(".uploaded_graphics");

    let requestData = {
      action: "update_order_transient",
      order_id: orderId,
    };

    function handleResponse(response) {
      Toastify({
        text: `Artwork Deleted successfully!`,
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

    if (confirm("Are you sure you want to delete this artwork?")) {
      $("body").addClass("updating");
      showSpinner(artworkContainer); // Show spinner

      let deleteArtworkMeta = {
        order_id: orderId,
        item_id: itemId,
        art_meta_id: metaId,
      };

      fetch(`${order_domain}/wp-json/update-order/v1/delete-item-meta`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(deleteArtworkMeta),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            ml_send_ajax(requestData, handleResponse);
            // Remove artwork from the DOM
            artworkContainer.find("a").remove();
            artworkContainer.find(".om__DeleteArtwork").hide();
            artworkContainer.append(
              '<span class="no_artwork_text">No Artwork Attached</span>'
            );
          } else {
            alert("Failed to delete artwork: " + data.message);
          }
          console.log("Response:", data.message);
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error: " + error.message);
        })
        .finally(() => {
          $("body").removeClass("updating");
          hideSpinner(artworkContainer); // Hide spinner
        });
    }
  });

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

    // stock_management_addition_limon - set variables
    const current = $(this);
    const group_item = current.closest("tr.om__orderRow");
    const sku = group_item.data("item-sku");
    const is_variable = group_item.data("is_variable");
    const variations = group_item.find(".item_name_variations");
    const colorItem = variations.find("[data-meta_key='Color']");
    const sizeItem = variations.find("[data-meta_key='Size']");
    const old_quantity = current.data("item-qty");

    console.log("is_variable", is_variable);
    console.log("is_variable typeof", typeof is_variable);

    let itemType = "quantity";
    let colorValue = "";
    let sizeValue = "";

    if (colorItem.length !== 0) {
      itemType = "group";
      colorValue = colorItem.find("strong").text();
      sizeValue = sizeItem.find("strong").text();
    }

    if (is_variable == true) {
      itemType = "variable";
    }

    // get different from old quantity to new quantity
    let last_qty = parseInt(newQuantity) - parseInt(old_quantity);

    const stockReqsData = {
      source: allaround_vars.home_url,
      action: "quantity_update",
      order_id: orderId,
      data: {},
    };

    if (itemType === "group") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          [colorValue]: {
            [sizeValue.toLowerCase()]: last_qty,
          },
        },
      };
    }

    if (itemType === "quantity") {
      stockReqsData.data[sku] = {
        type: itemType,
        data: {
          qty: last_qty,
        },
      };
    }
    console.log("stockReqsData", stockReqsData);
    // end stock_management_addition_limon - set variables

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
      // stock_management_addition_limon - update qty atts and send stock updat request
      current.data("item-qty", newQuantity);
      if (is_variable != true && current.hasClass("item-quantity-input")) {
        sendRequestToUpdateStock("stock-update", stockReqsData);
      }
      // end stock_management_addition_limon - update qty atts and send stock updat request
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

  function populateCustomQuantityColors(selector, colors) {
    const dropdown = $(selector);
    const currentValue = dropdown.val();
    dropdown.empty();
    dropdown.append(
      `<option value="N/A">No Applicable</option><option value="${currentValue}" selected>${currentValue}</option>`
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
    if (currentValue !== "N/A") {
      dropdown.append(`<option value="N/A">Not Applicable</option>`);
    }
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
    if (currentValue !== "N/A") {
      dropdown.append(`<option value="N/A">Not Applicable</option>`);
    }
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
    if (currentValue !== "N/A") {
      dropdown.append(`<option value="N/A">Not Applicable</option>`);
    }
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

  // ********** Sending Data to Make.com Webhook **********//
  function sendDataToWebhook(data, retryCount = 3) {
    let root_domain = allaround_vars.redirecturl;
    let webhook_url;

    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Webhook URL for test environment
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else if (root_domain.includes("allaround.co.il")) {
      // Webhook URL for production environment
      webhook_url =
        "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    } else {
      // Default to test webhook URL
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    }

    fetch(webhook_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          return response.text().then((text) => {
            throw new Error(text);
          });
        }
        return response.json().catch(() => {
          // Handle non-JSON response
          return { message: "Accepted" };
        });
      })
      .then((result) => {
        console.log("Webhook response:", result);
        Toastify({
          text: `Data sent to Make.com webhook successfully!`,
          duration: 3000,
          close: true,
          gravity: "bottom", // `top` or `bottom`
          position: "right", // `left`, `center` or `right`
          stopOnFocus: true, // Prevents dismissing of toast on hover
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();
      })
      .catch((error) => {
        console.error("Error sending data to webhook:", error);

        if (retryCount > 0) {
          console.log(`Retrying... Attempts remaining: ${retryCount}`);
          // Retry after a short delay
          setTimeout(() => {
            sendDataToWebhook(data, retryCount - 1);
          }, 2000); // Retry after 2 seconds
        } else {
          alert("Failed to send data to webhook after multiple attempts.");
          console.error("Final error after retries:", error);
        }
      });
  }

  // ********** Add New Item to the Existing Order **********//
  $("#new_product_artwork").on("change", function (event) {
    let files = event.target.files;
    let uploadedFiles = [];

    if (files.length > 0) {
      $("#addNewItemButton").addClass("ml_loading").prop("disabled", true);
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

  // ********** Shipping Method Update Ajax **********//
  // on submit of #shipping-method-form form
  $("#shipping-method-form").on("submit", function (event) {
    event.preventDefault();

    let order_id = allaround_vars.order_id;
    let order_number = allaround_vars.order_number;
    let order_domain = allaround_vars.order_domain;
    let shipping_method_id = document.querySelector(
      'select[name="shipping_method"]'
    ).value;
    let shipping_method_title = document.querySelector(
      'select[name="shipping_method"] option:checked'
    ).text;
    let nonce = allaround_vars.nonce;
    $(".om_shipping_submit").addClass("pulse");

    let webhookData = {
      om_status: "shipping_method_updated",
      order_id: order_number,
      shipping_method: shipping_method_title,
    };

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

          let shipping_method = data.data.shipping_method;

          let shippingDetailsForm = $(".om__orderShippingDetails");

          if (shipping_method === "local_pickup") {
            shippingDetailsForm.hide();
          } else {
            shippingDetailsForm.show();
          }

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

          Toastify({
            text: `Shipping Method Updated Successfully!`,
            duration: 3000,
            close: true,
            gravity: "bottom", // `top` or `bottom`
            position: "right", // `left`, `center` or `right`
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
              background: "linear-gradient(to right, #00b09b, #96c93d)",
            },
          }).showToast();

          // Call the function to send data to the webhook
          sendDataToWebhook(webhookData);

          // location reload
          // location.reload();
        } else {
          alert("Failed to update shipping method: " + data.data);
        }
      })
      .catch((error) => {
        alert("An error occurred: " + error);
      });
  });

  // ********** Edit Shipping form open **********//
  // Store the current value before any change
  var currentValue = $("#shipping-method-list").val();

  $("#shipping-method-list").on("change", function () {
    // Check if the selected value is not equal to the current value
    if ($(this).val() !== "") {
      $(".om_shipping_submit").show();
    } else {
      // Optionally, hide the button if the selected value is the current value
      $(".om_shipping_submit").hide();
    }
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

  // ********** Update Order General Comment/Note **********//
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
          $(".om_displayOrderNotesGrid").removeClass("om_no_notes_gridNote");
          $.magnificPopup.close();

          // Update the UI with the new comment and attachments
          if (orderComment !== "") {
            $(".om__orderGeneralComment_text p").remove();
            $(".om__displayOrderComment").removeClass("om_no_notes");
            $(".om__orderGeneralComment_text").append(`
                            <p>${response.data.order_general_comment}</p>
                        `);
          }

          if (response.data.attachments.length > 0) {
            let attachmentsHtml =
              '<h5>Attachments!</h5><div class="om__orderNoteFiles">';
            response.data.attachments.forEach(function (attachment) {
              attachmentsHtml += `
                                <div class="attachment-item">
                                    <a href="${attachment.url}" target="_blank">${attachment.name}</a>
                                    <span class="delete-attachment" data-attachment-id="${attachment.id}" data-attachment-type="general">&times;</span>
                                </div>`;
            });
            attachmentsHtml += "</div>";

            $(".om__orderNoteFiles_container").removeClass("om_no_notes");
            $(".om__orderNoteFiles_container").html(attachmentsHtml);
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

  // Handle deleting attachments
  $(document).on("click", ".delete-attachment", function () {
    let attachmentId = $(this).data("attachment-id");
    let attachmentType = $(this).data("attachment-type");
    let postId = allaround_vars.post_id;
    let nonce = allaround_vars.nonce;

    $.ajax({
      type: "POST",
      url: allaround_vars.ajax_url,
      data: {
        action: "delete_order_attachment",
        attachment_id: attachmentId,
        post_id: postId,
        attachment_type: attachmentType,
        nonce: nonce,
      },
      success: function (response) {
        if (response.success) {
          $(`.delete-attachment[data-attachment-id="${attachmentId}"]`)
            .parent()
            .remove();

          // Check if there are any more attachments
          if ($(".om__orderNoteFiles .attachment-item").length === 0) {
            // If no more attachments, empty the container
            $(".om__orderNoteFiles_container").empty().addClass("om_no_notes");
          }

          // Check if there are any more attachments
          if ($(".om__designerNoteFiles .attachment-item").length === 0) {
            // If no more attachments, empty the container
            $(".om__designerNoteFiles_container")
              .empty()
              .addClass("om_no_notes");
          }

          Toastify({
            text: `Attachment Deleted Successfully!`,
            duration: 3000,
            close: true,
            gravity: "bottom", // `top` or `bottom`
            position: "right", // `left`, `center` or `right`
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
              background: "linear-gradient(to right, #f44336, #ff5252)",
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

  if (!isEmployee()) {
    $(".om_addOrderNote, .om__orderGeneralComment_text").magnificPopup({
      items: {
        src: "#om__orderNote_container",
        type: "inline",
      },
      closeBtnInside: true,
      callbacks: {
        open: function () {
          let orderComment = $(".om__orderGeneralComment_text p").html();
          if (orderComment) {
            // Replace <br> tags with \n and remove any trailing line breaks
            orderComment = orderComment.replace(/<br\s*[\/]?>/gi, "").trim();
            $("#order_general_comment").val(orderComment);
          }
        },
      },
    });
  }
  // ********** END Order General Comment/Note **********//

  // ********** Update Order Designer Note **********//
  $("#order_designer_extra_attachments").on("change", function () {
    let files = $(this)[0].files;
    let fileNames = [];
    for (let i = 0; i < files.length; i++) {
      fileNames.push(files[i].name);
    }
    $("#uploaded_designer_extra_file_path").val(fileNames.join(", "));
  });

  // Handle adding or updating designer notes and attachments
  $("#add-designer-note").on("click", function () {
    let orderComment = $("#order_designer_note").val();
    let postId = allaround_vars.post_id;
    let nonce = allaround_vars.nonce;
    $(this).addClass("ml_loading");

    let files = $("#order_designer_extra_attachments")[0].files;

    if (orderComment === "" && files.length === 0) {
      alert("Please enter a comment or select files to upload.");
      return;
    }

    let formData = new FormData();
    formData.append("action", "save_order_designer_notes");
    formData.append("order_designer_notes", orderComment);
    formData.append("post_id", postId);
    formData.append("nonce", nonce);

    for (let i = 0; i < files.length; i++) {
      formData.append("order_designer_extra_attachments[]", files[i]);
    }

    $.ajax({
      type: "POST",
      url: allaround_vars.ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      timeout: 300000,
      success: function (response) {
        if (response.success) {
          $("#add-designer-note").removeClass("ml_loading");
          $(".om_addDesignerNote").removeClass("om_no_notes_addNote");
          $(".om_displayOrderNotesGrid").removeClass("om_no_notes_gridNote");
          $.magnificPopup.close();
          // Update the UI with the new comment and attachments
          if (orderComment !== "") {
            $(".om__orderDesignerNote_text p").remove();
            $(".om__displayDesignerComment").removeClass("om_no_notes");
            $(".om__orderDesignerNote_text").append(`
                            <p>${response.data.order_designer_notes}</p>
                        `);
          }

          if (response.data.attachments.length > 0) {
            let attachmentsHtml =
              '<h5>Attachments!</h5><div class="om__designerNoteFiles">';
            response.data.attachments.forEach(function (attachment) {
              attachmentsHtml += `
                                <div class="attachment-item">
                                    <a href="${attachment.url}" target="_blank">${attachment.name}</a>
                                    <span class="delete-attachment" data-attachment-id="${attachment.id}" data-attachment-type="designer">&times;</span>
                                </div>`;
            });
            attachmentsHtml += "</div>";
            $(".om__designerNoteFiles_container").removeClass("om_no_notes");
            $(".om__designerNoteFiles_container").html(attachmentsHtml);
          }

          if (files.length > 0) {
            $("#order_designer_extra_attachments").val(""); // Clear the file input
            $("#uploaded_designer_extra_file_path").val(""); // Clear the hidden input
          }
          Toastify({
            text: `Designer Note Updated Successfully!`,
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
      error: function (xhr, status, error) {
        let errorMessage = "An error occurred: " + error;
        if (xhr.responseText) {
          errorMessage += "\nResponse: " + xhr.responseText;
        }
        if (xhr.status) {
          errorMessage += "\nStatus: " + xhr.status;
        }
        if (xhr.statusText) {
          errorMessage += "\nStatus Text: " + xhr.statusText;
        }
        alert(errorMessage);
      },
    });
  });

  $(".om_addDesignerNote, .om__orderDesignerNote_text").magnificPopup({
    items: {
      src: "#om__designerNote_container",
      type: "inline",
    },
    closeBtnInside: true,
    callbacks: {
      open: function () {
        let orderComment = $(".om__orderDesignerNote_text p").html();
        if (orderComment) {
          // Replace <br> tags with \n and remove any trailing line breaks
          orderComment = orderComment.replace(/<br\s*[\/]?>/gi, "").trim();
          $("#order_designer_note").val(orderComment);
        }
      },
    },
  });
  // ********** END Order Designer Note **********//

  // .alarnd__artwork_img src is not jpg, png or jpeg change to document.png
  $(".alarnd__artwork_img").each(function () {
    if (
      !/\.(png|jpg|jpeg|pdf)$/i.test($(this).attr("src")) &&
      $(this).attr("src") !== ""
    ) {
      $(this).attr("src", `${allaround_vars.assets}images/document.png`);
    }
  });

  // ********** Sortable Order List **********//
  if ($("#tableMain").length) {
    if (!isDesigner() && !isEmployee()) {
      $("#tableMain tbody").sortable({
        update: function (event, ui) {
          let new_order = [];
          $("#tableMain tbody tr").each(function () {
            new_order.push($(this).attr("id"));
          });

          let post_id = allaround_vars.post_id;

          console.log("New Order:", new_order);

          $.ajax({
            url: allaround_vars.ajax_url,
            method: "POST",
            data: {
              action: "rearrange_items",
              post_id: post_id,
              new_order: new_order,
              nonce: allaround_vars.nonce,
            },
            success: function (response) {
              if (response.success) {
                // alert(response.message);
                Toastify({
                  text: `Items Rearranged successfully!`,
                  duration: 3000,
                  close: true,
                  gravity: "bottom", // `top` or `bottom`
                  position: "right", // `left`, `center` or `right`
                  stopOnFocus: true, // Prevents dismissing of toast on hover
                  style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                  },
                }).showToast();
                console.log("Response:", response.data);
              } else {
                alert("Error: " + response.message);
                console.log("Error:", response);
              }
            },
            error: function (xhr, status, error) {
              console.error("Error:", error);
              console.error("Response Text:", xhr.responseText);
              alert("Failed to rearrange order items: " + xhr.responseText);
            },
          });
        },
      });
    }
  }

  // Store the current value before any change
  var currentClient = $(".om__client-select").val();
  // Show Save Mark when client_select is changed
  $(".om__client-select").on("change", function () {
    if ($(this).val() !== currentClient && $(this).val() !== "") {
      $(".om__client_update_btn").addClass("show");
    } else {
      $(".om__client_update_btn").removeClass("show");
      $(".select2-selection__arrow").show();
    }
  });

  // Update Order Shipping data on Order page
  $("#update-shipping-details").on("click", function () {
    const $this = $(this);
    const post_id = allaround_vars.post_id;
    let client_data = {
      first_name: $("#shipping_first_name").val(),
      last_name: $("#shipping_last_name").val(),
      address_1: $("#shipping_address_1").val(),
      address_2: $("#shipping_address_2").val(),
      city: $("#shipping_city").val(),
      phone: $("#shipping_phone").val(),
      boxes: $("#shipping_boxes").val(),
      nonce: allaround_vars.nonce,
    };

    $(this).addClass("ml_loading");

    $.ajax({
      url: allaround_vars.ajax_url,
      type: "POST",
      data: {
        action: "update_post_shipping_details",
        post_id: post_id,
        ...client_data,
      },
      success: function (response) {
        if (response.success) {
          // remove loading class
          $this.removeClass("ml_loading");

          let shipping_details = response.data.shipping_details;

          $("#shipping_first_name").val();

          console.log(response);

          Toastify({
            text: `Order shipping info updated successfully!`,
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
            "Failed to update shipping information: " + response.data.message
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert("Failed to update shipping information.");
      },
    });
  });

  // Update Client on Order Manage post
  $(".om__client_update_btn").on("click", function () {
    $(this).addClass("ml_loading");
    let selectedClientId = $("#client-select").val();
    let post_id = allaround_vars.post_id;
    let client_data = {
      client_id: selectedClientId,
      first_name: $("#billing_first_name").val(),
      last_name: $("#billing_last_name").val(),
      address_1: $("#billing_address_1").val(),
      address_2: $("#billing_address_2").val(),
      city: $("#billing_city").val(),
      phone: $("#billing_phone").val(),
      nonce: allaround_vars.nonce,
    };

    if (!selectedClientId) {
      alert("Please select a client.");
      return;
    }

    $.ajax({
      url: allaround_vars.ajax_url,
      type: "POST",
      data: {
        action: "update_order_client",
        client_id: selectedClientId,
        post_id: post_id,
        ...client_data,
      },
      success: function (response) {
        if (response.success) {
          $(".om__client_update_btn").removeClass("ml_loading");
          location.reload();
        } else {
          alert("Failed to update client: " + response.data.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert("Failed to update client information.");
      },
    });
  });

  $(".om__edit_clientButton").on("click", function () {
    $(".om__orderedClientName").css({
      opacity: 0,
      transition: "opacity 0.3s",
    });
    $(".om__change-client").slideDown();
  });

  $(".toogle-select-client").on("click", function () {
    $(".om__orderedClientName").css({
      opacity: 1,
      transition: "opacity 0.3s",
    });
    $(".om__change-client").slideUp();
  });

  $(".designerSendWebhook").on("click", function () {
    let order_id = allaround_vars.order_id;
    let order_number = allaround_vars.order_number;
    let status = $(this).data("status");
    let root_domain = allaround_vars.redirecturl;
    let missing_info_details = $(".designer_missing_info_text").val();

    $(this).addClass("ml_loading");

    let webhook_url = "";

    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Webhook URL for test environment
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else if (root_domain.includes("allaround.co.il")) {
      // Webhook URL for production environment
      webhook_url =
        "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    } else {
      // Default to test webhook URL
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    }

    // Data to send to the webhook
    let data = {
      order_id: order_number,
      om_status: status,
    };

    // Conditionally add missing_info_details if it is available
    if (missing_info_details) {
      data.missing_info_details = missing_info_details;
    }

    // AJAX request to send the data to the webhook
    $.ajax({
      url: webhook_url,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        console.log("Webhook request successful:", response);
        // Optionally, you can show a success message to the user
        Toastify({
          text: "Webhook request sent successfully!",
          duration: 3000,
          close: true,
          gravity: "bottom",
          position: "right",
          stopOnFocus: true,
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();

        $(".designerSendWebhook").removeClass("ml_loading");
        // clear .designer_missing_info_text
        $(".designer_missing_info_text").val("");
        $.magnificPopup.close();
      },
      error: function (xhr, status, error) {
        $(".designerSendWebhook").removeClass("ml_loading");
        console.error("Error sending webhook request:", error);
        // Optionally, you can show an error message to the user
        alert("Failed to send webhook request. Please try again.");
      },
    });
  });

  $("#printLabelSendWebhook").on("click", function () {
    let order_id = allaround_vars.order_id;
    let order_number = allaround_vars.order_number;
    let post_id = allaround_vars.post_id;
    let root_domain = allaround_vars.redirecturl;

    // Gather shipping details from input fields
    let shipping_method = $("#shipping-method-list").val();
    let shipping_first_name = $("#shipping_first_name").val();
    let shipping_last_name = $("#shipping_last_name").val();
    let shipping_address_1 = $("#shipping_address_1").val();
    let shipping_address_2 = $("#shipping_address_2").val();
    let shipping_city = $("#shipping_city").val();
    let shipping_phone = $("#shipping_phone").val();
    let shipping_boxes = $("#shipping_boxes").val();
    if (!shipping_boxes) {
      shipping_boxes = 1;
    }
    let shipping_method_text = $(
      "#shipping-method-list option:selected"
    ).text();

    if (
      shipping_method_text ===
        "איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!" &&
      shipping_method !== "local_pickup"
    ) {
      shipping_method = "local_pickup";
    }

    let full_name = `${shipping_first_name} ${shipping_last_name}`;

    $(this).addClass("ml_loading");

    let webhook_url = "";

    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Webhook URL for test environment
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else if (root_domain.includes("allaround.co.il")) {
      // Webhook URL for production environment
      webhook_url =
        "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    } else {
      // Default to test webhook URL
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    }

    // Data to send to the webhook
    let data = {
      order_id: order_number,
      om_status: "Print Label",
      shipping_method: shipping_method,
      shipping_fullName: full_name,
      shipping_addressName: shipping_address_1,
      shipping_addressNumber: shipping_address_2,
      shipping_city: shipping_city,
      shipping_phone: shipping_phone,
      boxes: shipping_boxes,
    };

    console.log("Data to send:", data);

    let requestData = {
      action: "update_order_transient",
      order_id: order_id,
    };

    function handleResponse(response) {
      Toastify({
        text: "Webhook request sent successfully!",
        duration: 3000,
        close: true,
        gravity: "bottom",
        position: "right",
        stopOnFocus: true,
        style: {
          background: "linear-gradient(to right, #00b09b, #96c93d)",
        },
      }).showToast();

      // Disable the button after success
      $("#printLabelOpenModal").prop("disabled", true);

      // Add _print_label_done meta to the order via AJAX
      $.ajax({
        url: allaround_vars.ajax_url,
        type: "POST",
        data: {
          action: "add_print_label_meta",
          post_id: post_id,
          nonce: allaround_vars.nonce,
        },
        success: function (metaResponse) {
          console.log("Meta updated successfully:", metaResponse);

          // If #orderCompleted button exists, trigger its click
          if ($("#orderCompleted").length) {
            $("#orderCompleted").trigger("click");
          }
        },
        error: function (xhr, status, error) {
          console.error("Error updating meta:", error);
        },
      });
    }

    // AJAX request to send the data to the webhook
    $.ajax({
      url: webhook_url,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        console.log("Webhook request successful:", response);
        ml_send_ajax(requestData, handleResponse);

        $("#printLabelSendWebhook").removeClass("ml_loading");
        $.magnificPopup.close();

        if (isAdmin()) {
          // Set flag for updating order status text on reload
          localStorage.setItem("orderStatusCompleted", "true");
        }

        // locatio reload after 1 sec
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function (xhr, status, error) {
        $("#printLabelSendWebhook").removeClass("ml_loading");
        console.error("Error sending webhook request:", error);
        // Optionally, you can show an error message to the user
        alert("Failed to send webhook request. Please try again.");
      },
    });
  });

  if (isAdmin()) {
    $(document).ready(function () {
      if (localStorage.getItem("orderStatusCompleted") === "true") {
        $("#om__orderStatus").text("Completed");
        localStorage.removeItem("orderStatusCompleted");
      }
    });
  }

  // Open Modal on click of #sendProofOpenModal
  $("#sendProofOpenModal").on("click", function (e) {
    e.preventDefault();

    var orderType = $("#order_type").val();

    // Check if #order_type value is empty
    if (!orderType) {
      if (isDesigner()) {
        alert(
          "Please wait with submitting the order until order type is selected by admin."
        );
        return;
      }
      alert("Select an Order Type first to proceed.");
      return;
    }

    // If #order_type value is 'company', check #mini_url and #mini_header fields
    if (orderType === "company") {
      let miniUrl = $("#mini_url").val();
      let miniHeader = $("#mini_header").val();
      let darkLogo = $("#dark_logo").val();
      let lighterLogo = $("#lighter_logo").val();

      if (!miniUrl || !miniHeader) {
        alert("Fill the client's Business fields.");
        return;
      }

      if (!darkLogo || !lighterLogo) {
        alert("Please upload dark and lighter logo versions.");
        return;
      }
    }

    // Open the modal if all checks pass
    $.magnificPopup.open({
      items: {
        src: "#sendProofConfirmationModal",
        type: "inline",
      },
      closeBtnInside: true,
    });
  });

  // Open Modal on click of #missingInfoOpenModal
  $("#missingInfoOpenModal").magnificPopup({
    items: {
      src: "#missingInfoConfirmationModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #revisionOpenModal
  $("#revisionOpenModal").magnificPopup({
    items: {
      src: "#revisionConfirmationModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #DTFDoneOpenModal
  $("#DTFDoneOpenModal").magnificPopup({
    items: {
      src: "#DTFDoneConfirmationModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #mockupDoneOpenModal
  $("#mockupDoneOpenModal").on("click", function (e) {
    e.preventDefault();

    var orderType = $("#order_type").val();

    // Check if #order_type value is empty
    if (!orderType) {
      if (isDesigner()) {
        alert(
          "Please wait with submitting the order until order type is selected by admin."
        );
        return;
      }
      alert("Select an Order Type first to proceed.");
      return;
    }

    // If #order_type value is 'company', check #mini_url and #mini_header fields
    if (orderType === "company") {
      let miniUrl = $("#mini_url").val();
      let miniHeader = $("#mini_header").val();
      let darkLogo = $("#dark_logo").val();
      let lighterLogo = $("#lighter_logo").val();

      if (!miniUrl || !miniHeader) {
        alert("Fill the client's Business fields.");
        return;
      }

      if (!darkLogo || !lighterLogo) {
        alert("Please upload dark and lighter logo versions.");
        return;
      }
    }

    // Open the modal if all checks pass
    $.magnificPopup.open({
      items: {
        src: "#mockupDoneConfirmationModal",
        type: "inline",
      },
      closeBtnInside: true,
    });
  });

  // send to webhook with order id and om_status missing_graphic
  $("#missingGraphic").on("click", function () {
    let order_id = allaround_vars.order_id;
    let order_number = allaround_vars.order_number;
    let root_domain = allaround_vars.redirecturl;
    let customerEmail = $("#billing_email").val();

    $(this).addClass("ml_loading");

    let webhook_url = "";

    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Webhook URL for test environment
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else if (root_domain.includes("allaround.co.il")) {
      // Webhook URL for production environment
      webhook_url =
        "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    } else {
      // Default to test webhook URL
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    }

    // Data to send to the webhook
    let data = {
      order_id: order_number,
      customer_email: customerEmail,
      om_status: "missing_graphic",
    };

    // AJAX request to send the data to the webhook
    $.ajax({
      url: webhook_url,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        console.log("Webhook request successful:", response);
        // Optionally, you can show a success message to the user
        Toastify({
          text: "Webhook request sent successfully!",
          duration: 3000,
          close: true,
          gravity: "bottom",
          position: "right",
          stopOnFocus: true,
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();

        $("#missingGraphic").removeClass("ml_loading");
        $.magnificPopup.close();
      },
      error: function (xhr, status, error) {
        $("#missingGraphic").removeClass("ml_loading");
        console.error("Error sending webhook request:", error);
        // Optionally, you can show an error message to the user
        alert("Failed to send webhook request. Please try again.");
      },
    });
  });

  // send to webhook with order id and om_status missing_graphic
  $("#mockupApproved").on("click", function () {
    let order_id = allaround_vars.order_id;
    let order_number = allaround_vars.order_number;
    let root_domain = allaround_vars.redirecturl;

    $(this).addClass("ml_loading");

    let webhook_url = "";

    if (root_domain.includes(".test") || root_domain.includes("lukpaluk.xyz")) {
      // Webhook URL for test environment
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else if (root_domain.includes("allaround.co.il")) {
      // Webhook URL for production environment
      webhook_url =
        "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    } else {
      // Default to test webhook URL
      webhook_url =
        "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    }

    // Data to send to the webhook
    let data = {
      om_status: "mockups_approved",
      order_id: order_number,
    };

    // AJAX request to send the data to the webhook
    $.ajax({
      url: webhook_url,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        console.log("Webhook request successful:", response);
        // Optionally, you can show a success message to the user
        Toastify({
          text: "Webhook request sent successfully!",
          duration: 3000,
          close: true,
          gravity: "bottom",
          position: "right",
          stopOnFocus: true,
          style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
          },
        }).showToast();

        $("#mockupApproved").removeClass("ml_loading");
        $.magnificPopup.close();
      },
      error: function (xhr, status, error) {
        $("#mockupApproved").removeClass("ml_loading");
        console.error("Error sending webhook request:", error);
        // Optionally, you can show an error message to the user
        alert("Failed to send webhook request. Please try again.");
      },
    });
  });

  // Open Modal on click of #printLabelOpenModal
  $("#printLabelOpenModal").magnificPopup({
    items: {
      src: "#printLabelConfirmationModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #missingGraphicOpenModal
  $("#missingGraphicOpenModal").magnificPopup({
    items: {
      src: "#missingGraphicConfirmModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #mockupApprovedOpenModal
  $("#mockupApprovedOpenModal").magnificPopup({
    items: {
      src: "#mockupApprovedConfirmModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Open Modal on click of #orderCompletedOpenModal
  $("#orderCompletedOpenModal").magnificPopup({
    items: {
      src: "#orderCompletedConfirmModal",
      type: "inline",
    },
    closeBtnInside: true,
  });

  // Close the modal on click of #printLabelCancel
  $(".confmodalCancel").on("click", function () {
    $.magnificPopup.close();
  });

  // Close the modal on click of #printLabelCancel
  $("#printLabelCancel").on("click", function () {
    $.magnificPopup.close();
  });

  // Close the modal on click of #missingGraphicCancel
  // $("#missingGraphicCancel").on("click", function () {
  //   $.magnificPopup.close();
  // });

  // check the length of tableMain
  if ($("#tableMain").length) {
    if (isDesigner()) {
      // Get the data-order_status attribute value from #om__orderStatus
      var orderStatus = $("#tableMain").data("order_status");
      // Disable OM events if status is 'completed' or 'static'
      if (orderStatus === "completed" || orderStatus === "static") {
        $(".om__orderShippingDetails").hide();
        // $(".om__companyLogoUpload").hide();
        $(".om__ordernoteContainer").hide();

        // $("#order_mngmnt_headings").css("pointer-events", "none");
        // $("#order_mngmnt_headings").css("opacity", "0.6");

        $(".om__afterTable_buttonSet").css("pointer-events", "none");
        $(".om__afterTable_buttonSet").css("opacity", "0.6");

        $("#tableMain .om__orderRow").css("pointer-events", "none");
        // $("#tableMain .om__orderRow").css("opacity", "0.6");
      }
    }

    if (isAdmin()) {
      // Trigger AJAX call when the Save button is clicked
      $(".save_printing_note").on("click", function () {
        let item_id = $(this).data("item_id");
        let printing_note = $(this)
          .closest("tr")
          .find(".printing_note_textarea")
          .val();
        // Get post_id from a hidden input field or other identifier
        let post_id = $("input[name='post_id']").val();

        $.ajax({
          url: allaround_vars.ajax_url,
          type: "POST",
          data: {
            action: "save_printing_note",
            item_id: item_id,
            printing_note: printing_note,
            post_id: post_id,
            nonce: allaround_vars.nonce,
          },
          success: function (response) {
            if (response.success) {
              alert(response.data.message);
            } else {
              alert(response.data.message);
            }
          },
          error: function (xhr, status, error) {
            console.log(xhr.responseText);
            alert("An error occurred.");
          },
        });
      });

      // Function to update button text based on selected items
      function updateButtonText() {
        const selectedItems = $(".combine-item-checkbox:checked");
        const allCheckedHaveDisabled =
          selectedItems.length > 0 &&
          selectedItems.filter(".checked").length === selectedItems.length;

        if (allCheckedHaveDisabled) {
          $("#combine-items-button").text("Enable Items");
        } else {
          $("#combine-items-button").text("Disable Items");
        }
      }

      // Listen for checkbox changes to update the button text
      $(".combine-item-checkbox").on("change", updateButtonText);

      // Combine items button click event
      $("#combine-items-button").on("click", function () {
        const selectedItems = [];
        const post_id = $('input[name="post_id"]').val();

        $(".combine-item-checkbox:checked").each(function () {
          selectedItems.push($(this).val());
        });

        if (selectedItems.length < 1) {
          alert("Please select at least one item.");
          return;
        }

        console.log("Selected Items:", selectedItems);
        console.log("Post ID:", post_id);

        $.ajax({
          url: allaround_vars.ajax_url,
          method: "POST",
          data: {
            action: "combine_items",
            post_id: post_id,
            item_ids: selectedItems,
            nonce: allaround_vars.nonce,
          },
          success: function (response) {
            if (response.success) {
              alert(response.data.message); // Show success message
              location.reload(); // Reload the page to reflect changes
            } else {
              alert(response.data.message); // Show error message
            }
          },
          error: function (xhr, status, error) {
            let errorMessage = "An error occurred while combining the items.";
            if (
              xhr.responseJSON &&
              xhr.responseJSON.data &&
              xhr.responseJSON.data.message
            ) {
              errorMessage = xhr.responseJSON.data.message;
            }
            alert(errorMessage); // Show the JSON error message
          },
        });
      });
    }

    if (isAdmin() || isEmployee()) {
      // Handle the click event on the #orderCompleted button
      $("#orderCompleted").on("click", function () {
        let order_id = allaround_vars.order_id;
        let order_number = allaround_vars.order_number;
        let order_domain = allaround_vars.order_domain;
        let post_id = allaround_vars.post_id;
        let nonce = allaround_vars.nonce;

        // Show loading spinner or disable button to prevent multiple clicks
        $(this).addClass("ml_loading").prop("disabled", true);

        let webhookData = {
          om_status: "completed",
          order_id: order_number,
        };

        let requestData = {
          action: "update_order_transient",
          order_id: order_id,
        };

        function handleResponse(response) {
          $("#orderCompleted").removeClass("ml_loading");
          alert("Order marked as completed successfully!");
          // Optionally, you can reload the page or update the UI
          location.reload();
        }
        // Send AJAX request to the WordPress backend
        $.ajax({
          type: "POST",
          url: allaround_vars.ajax_url,
          data: {
            action: "complete_order",
            order_id: order_id,
            order_domain: order_domain,
            post_id: post_id,
            nonce: nonce,
          },
          success: function (response) {
            if (response.success) {
              sendDataToWebhook(webhookData);
              ml_send_ajax(requestData, handleResponse);
            } else {
              alert(
                "Failed to mark order as completed: " + response.data.message
              );
            }
          },
          error: function (xhr, status, error) {
            alert("An error occurred: " + error);
          },
          complete: function () {
            // Hide loading spinner or re-enable button
            $("#orderCompleted")
              .removeClass("ml_loading")
              .prop("disabled", false);
          },
        });
      });
    }
  }

  // ********** Open Order Note for Employees **********//
  $(document).ready(function () {
    // 1. Check if #autoOpenEmployeeNoteModal is in the DOM
    const $autoNoteModal = $("#autoOpenEmployeeNoteModal");
    if ($autoNoteModal.length) {
      // 2. We only got here if user is an Employee and the order note is not empty
      //    Let's check localStorage for the last shown time.

      // We'll store the last shown time keyed by the post/order ID
      let postId = allaround_vars.post_id; // from your existing global
      let storageKey = "employeeNoteModalLastShown_" + postId;
      let lastShown = localStorage.getItem(storageKey);
      let now = Date.now();
      let thirtyMinutes = 30 * 60 * 1000;

      // If never shown OR it's been more than 30 minutes, show the modal
      if (!lastShown || now - parseInt(lastShown, 10) > thirtyMinutes) {
        $.magnificPopup.open({
          items: {
            src: "#autoOpenEmployeeNoteModal",
          },
          type: "inline",
          removalDelay: 300,
          mainClass: "mfp-fade",
          // You can add more Magnific config if desired
          callbacks: {
            open: function () {
              console.log("Opened auto Employee Note modal.");
            },
            close: function () {
              console.log("Closed auto Employee Note modal.");
            },
          },
        });

        // Update localStorage with the new timestamp
        localStorage.setItem(storageKey, now.toString());
      }
    }
  });

  $(window).on("load", function () {});
})(jQuery); /*End document ready*/

// ********** Mockup Image Upload Post Request **********//
document.addEventListener("DOMContentLoaded", function () {
  // Use a parent element that exists when the DOM is loaded
  let orderId = allaround_vars.order_id;
  let selfHostedManualOrder = document.querySelector(
    ".om__selfHostedManualOrder"
  );
  let mainTable = document.getElementById("tableMain");
  if (!mainTable || selfHostedManualOrder) return;

  // Function to check if the current user is an Employee
  function isEmployee() {
    return allaround_vars.user_role === "author";
  }

  // Function to check if the current user is an Designer
  function isDesigner() {
    return allaround_vars.user_role === "contributor";
  }

  console.log(allaround_vars.user_role);
  // Existing logic to fetch and display all versions
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

        if (data.success) {
          let maxVersion = 0;
          if (data.data.no_mockup_state) {
            // Handle no mockup state
            const noMockupTd = document.createElement("td");
            noMockupTd.className = "no-mockup-state";
            noMockupTd.innerHTML = `<span>No Mockups!</span>`;
            thisTr.appendChild(noMockupTd);
            if (!isEmployee()) {
              initialAddNewMockupColumn(thisTr, maxVersion + 1);
            }
          } else {
            for (const mockup of data.data.mockup_versions) {
              maxVersion = Math.max(maxVersion, mockup.version);
              const mockupVersion = mockup.version;
              const mockupTd = document.createElement("td");
              mockupTd.className = `item_mockup_column om_expired_mockups`;
              mockupTd.setAttribute("data-version_number", mockupVersion);
              if (isEmployee()) {
                mockupTd.innerHTML = `
                                <input type="hidden" class="hidden_mockup_url" name="mockup-image-v${mockupVersion}" value="">
                                <div class="mockup-image mockupUUID_${orderId}_${productId}_${mockupVersion}">Loading mockup images...</div>
                                <div class="this-mockup-version"><span>V${mockupVersion}</span></div>
                            `;
              } else {
                mockupTd.innerHTML = `
                                <div class="lds-spinner-wrap" style="display: flex;">
                                    <div class="lds-spinner">
                                        ${"<div></div>".repeat(12)}
                                    </div>
                                </div>
                                <input type="hidden" class="hidden_mockup_url" name="mockup-image-v${mockupVersion}" value="">
                                <div class="mockup-image mockupUUID_${orderId}_${productId}_${mockupVersion}">Loading mockup images...</div>
                                <div class="this-mockup-version"><span>V${mockupVersion}</span></div>
                                <input class="file-input__input" name="file-input[${productId}]" id="file-input-${productId}-v${mockupVersion}" data-version="V${mockupVersion}" type="file" placeholder="Upload Mockup" multiple>
                                <label class="file-input__label" for="file-input-${productId}-v${mockupVersion}">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                </label>
                            `;
              }
              thisTr.appendChild(mockupTd);

              // Fetch the image URLs for this column
              await fetchImageURLs(orderId, productId, mockupVersion, mockupTd);
            }

            if (!isEmployee()) {
              initialAddNewMockupColumn(thisTr, maxVersion + 1);
            }

            if (maxVersion > 0) {
              populateTableHeader();
              const maxColumn = thisTr.querySelector(
                `td[data-version_number="${maxVersion}"]`
              );
              if (maxColumn) {
                maxColumn.classList.remove("om_expired_mockups");
                maxColumn.classList.add("last_send_version");
                if (!isEmployee()) {
                  // Add delete button for the most recent version
                  createDeleteButton(maxColumn, "V" + maxVersion, productId);
                }
              }
            }
          }
        } else {
          // Handle error case if needed
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

            // Add <span>Gallery</span> if there is more than one file
            if (fileList.length > 1) {
              let gallerySpan = document.createElement("span");
              gallerySpan.className = "mock-gallery-span";
              gallerySpan.innerHTML = `<span class="dashicons dashicons-format-gallery"></span>`;
              column.prepend(gallerySpan);

              // Initialize Slick Carousel on the mockupImageContainer
              const uniqueClass = `mockupUUID_${orderId}_${productId}_${version}`;
              // mockupImageContainer.classList.add(uniqueClass);

              // Ensure the carousel initialization is done after the DOM update
              setTimeout(() => {
                jQuery(`.${uniqueClass}`).slick({
                  infinite: true,
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  arrows: false,
                  dots: true,
                  adaptiveHeight: true,
                });
              }, 100);
            }
            // Pass the newly added images to the tooltip function
            const newImages = mockupImageContainer.querySelectorAll(
              ".om_mockup-thumbnail"
            );
            attachTooltipToProductThumbnails(newImages, productId, version);
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
      '<div class="mockup-image"></div>' +
      '<input class="file-input__input" name="file-input[' +
      productId +
      ']" id="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '" data-version="V' +
      nextVersion +
      '" type="file" placeholder="Upload Mockup" multiple >' +
      '<label class="file-input__label om__newMockupUpBtn" for="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '">' +
      '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<g clip-path="url(#clip0_2744_3844)"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.99967 0.833374C8.14561 0.833374 8.28434 0.897154 8.37927 1.00798L10.3793 3.34131C10.559 3.55097 10.5347 3.86663 10.3251 4.04633C10.1154 4.22605 9.79974 4.20177 9.62007 3.9921L8.49967 2.685V10C8.49967 10.2762 8.27581 10.5 7.99967 10.5C7.72354 10.5 7.49967 10.2762 7.49967 10V2.685L6.3793 3.9921C6.19959 4.20177 5.88394 4.22605 5.67428 4.04633C5.46461 3.86663 5.44033 3.55097 5.62005 3.34131L7.62007 1.00798C7.71501 0.897154 7.85374 0.833374 7.99967 0.833374ZM4.66356 5.50135C4.93969 5.49981 5.16479 5.72242 5.16633 5.99856C5.16787 6.27469 4.94526 6.49979 4.66913 6.50133C3.94013 6.50539 3.42341 6.52432 3.03124 6.59636C2.65337 6.66577 2.43465 6.77724 2.27235 6.93951C2.08784 7.12404 1.96754 7.38311 1.90177 7.87224C1.83407 8.37584 1.83301 9.04324 1.83301 10.0002V10.6668C1.83301 11.6238 1.83407 12.2912 1.90177 12.7948C1.96754 13.284 2.08784 13.543 2.27235 13.7275C2.45685 13.912 2.7159 14.0323 3.20509 14.0981C3.70865 14.1658 4.37606 14.1668 5.33301 14.1668H10.6663C11.6233 14.1668 12.2907 14.1658 12.7943 14.0981C13.2835 14.0323 13.5425 13.912 13.727 13.7275C13.9115 13.543 14.0318 13.284 14.0976 12.7948C14.1653 12.2912 14.1663 11.6238 14.1663 10.6668V10.0002C14.1663 9.04324 14.1653 8.37584 14.0976 7.87224C14.0318 7.38311 13.9115 7.12404 13.727 6.93951C13.5647 6.77724 13.346 6.66577 12.9681 6.59636C12.5759 6.52432 12.0592 6.50539 11.3302 6.50133C11.0541 6.49979 10.8315 6.27469 10.833 5.99856C10.8345 5.72242 11.0597 5.49981 11.3358 5.50135C12.0568 5.50536 12.6577 5.52262 13.1487 5.61281C13.6541 5.70563 14.0841 5.88246 14.4341 6.23241C14.8354 6.63369 15.008 7.13897 15.0887 7.73904C15.1663 8.31697 15.1663 9.05191 15.1663 9.96357V10.7034C15.1663 11.6152 15.1663 12.35 15.0887 12.928C15.008 13.5281 14.8354 14.0333 14.4341 14.4346C14.0328 14.8359 13.5276 15.0085 12.9275 15.0892C12.3495 15.1669 11.6147 15.1668 10.7029 15.1668H5.29643C4.3847 15.1668 3.64982 15.1669 3.07184 15.0892C2.47177 15.0085 1.96652 14.8359 1.56524 14.4346C1.16396 14.0333 0.991368 13.5281 0.910688 12.928C0.832982 12.35 0.832995 11.6152 0.833008 10.7034V9.96357C0.832995 9.05184 0.832982 8.31697 0.910688 7.73904C0.991368 7.13897 1.16396 6.63369 1.56524 6.23241C1.91519 5.88246 2.34524 5.70563 2.85057 5.61281C3.34161 5.52261 3.94254 5.50536 4.66356 5.50135Z" fill="#1A1A1A"/></g><defs><clipPath><rect width="16" height="16" fill="white"/></clipPath></defs>' +
      "</svg>" +
      "<span>Upload</span></label>";

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
    let uploadButton = mockupColumn.querySelector(".file-input__label");
    let mockupImageContainer = mockupColumn.querySelector(".mockup-image");
    const tableMain = mockupColumn.closest("table#tableMain");
    const tableBody = mockupColumn.closest("table#tableMain > tbody");
    let noMockupCell = mockupColumn
      .closest("tr.om__orderRow")
      .querySelector(".no-mockup-state");
    let productTitle = mockupColumn
      .closest("tr")
      .querySelector(".product_item_title").innerText;

    let sendProofButton = document.querySelector("#send-proof-button");

    // tableBody.style.pointerEvents = "none";

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
        tableBody.style.pointerEvents = "all";

        // Check if there are multiple file paths in the response
        if (responses.length > 1) {
          // Add the gallery icon to the top of .item_mockup_column
          const galleryIcon = document.createElement("span");
          galleryIcon.className = "mock-gallery-span";
          galleryIcon.innerHTML =
            '<span class="dashicons dashicons-format-gallery"></span>';

          // Insert the gallery icon at the beginning of the mockup column
          mockupColumn.insertAdjacentElement("afterbegin", galleryIcon);
        }

        responses.forEach((data, index) => {
          if (data.success && data.file_path) {
            if (mockupInput.value) {
              mockupInput.value += "," + data.file_path; // Append new file path
            } else {
              mockupInput.value = data.file_path;
            }

            // Add Last Mockup class
            mockupColumn.classList.add("last_send_version");

            // Remove the no mockup state if it exists
            if (noMockupCell) {
              noMockupCell.remove();
            }

            // Create new anchor tag with image
            const newAnchor = document.createElement("a");
            newAnchor.href = data.file_path;
            newAnchor.className = "mfp-image";
            newAnchor.innerHTML = `<img src="${data.file_path}" alt="Mockup Image" class="om_mockup-thumbnail">`;

            // Append the new anchor tag to the mockup image container
            mockupImageContainer.appendChild(newAnchor);

            // Apply the tooltip to the newly added image
            attachTooltipToProductThumbnails(
              [newAnchor.querySelector("img")],
              productId,
              version
            );
          }
        });

        // Check if a new mockups version column should be added
        let nextVersion = parseInt(version.replace("V", "")) + 1;
        // addNewMockupHeader(nextVersion);
        addNewMockupColumn(input, nextVersion);

        // just bellow of mockupImageContainer add <div class="this-mockup-version"><span>V2</span></div> with this version
        let thisMockupVersion = document.createElement("div");
        thisMockupVersion.className = "this-mockup-version";
        thisMockupVersion.innerHTML = `<span>${version}</span>`;
        mockupImageContainer.insertAdjacentElement(
          "afterend",
          thisMockupVersion
        );

        // Check if delete button exists, if not create it
        if (!mockupColumn.querySelector("#om_delete_mockup")) {
          createDeleteButton(mockupColumn, version, productId);
        }

        uploadButton.classList.remove("om__newMockupUpBtn");
        uploadButton.style.marginRight = "4px";
        uploadButton.innerHTML = `<span class="dashicons dashicons-plus-alt2"></span>`;

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
    deleteButton.textContent = `Delete`;
    mockupColumn.append(deleteButton, mockupColumn.firstChild);
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
      '<div class="mockup-image"></div>' +
      '<input class="file-input__input" name="file-input[' +
      productId +
      ']" id="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '" data-version="V' +
      nextVersion +
      '" type="file" placeholder="Upload Mockup" multiple >' +
      '<label class="file-input__label om__newMockupUpBtn" for="file-input-' +
      productId +
      "-v" +
      nextVersion +
      '">' +
      '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<g clip-path="url(#clip0_2744_3844)"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.99967 0.833374C8.14561 0.833374 8.28434 0.897154 8.37927 1.00798L10.3793 3.34131C10.559 3.55097 10.5347 3.86663 10.3251 4.04633C10.1154 4.22605 9.79974 4.20177 9.62007 3.9921L8.49967 2.685V10C8.49967 10.2762 8.27581 10.5 7.99967 10.5C7.72354 10.5 7.49967 10.2762 7.49967 10V2.685L6.3793 3.9921C6.19959 4.20177 5.88394 4.22605 5.67428 4.04633C5.46461 3.86663 5.44033 3.55097 5.62005 3.34131L7.62007 1.00798C7.71501 0.897154 7.85374 0.833374 7.99967 0.833374ZM4.66356 5.50135C4.93969 5.49981 5.16479 5.72242 5.16633 5.99856C5.16787 6.27469 4.94526 6.49979 4.66913 6.50133C3.94013 6.50539 3.42341 6.52432 3.03124 6.59636C2.65337 6.66577 2.43465 6.77724 2.27235 6.93951C2.08784 7.12404 1.96754 7.38311 1.90177 7.87224C1.83407 8.37584 1.83301 9.04324 1.83301 10.0002V10.6668C1.83301 11.6238 1.83407 12.2912 1.90177 12.7948C1.96754 13.284 2.08784 13.543 2.27235 13.7275C2.45685 13.912 2.7159 14.0323 3.20509 14.0981C3.70865 14.1658 4.37606 14.1668 5.33301 14.1668H10.6663C11.6233 14.1668 12.2907 14.1658 12.7943 14.0981C13.2835 14.0323 13.5425 13.912 13.727 13.7275C13.9115 13.543 14.0318 13.284 14.0976 12.7948C14.1653 12.2912 14.1663 11.6238 14.1663 10.6668V10.0002C14.1663 9.04324 14.1653 8.37584 14.0976 7.87224C14.0318 7.38311 13.9115 7.12404 13.727 6.93951C13.5647 6.77724 13.346 6.66577 12.9681 6.59636C12.5759 6.52432 12.0592 6.50539 11.3302 6.50133C11.0541 6.49979 10.8315 6.27469 10.833 5.99856C10.8345 5.72242 11.0597 5.49981 11.3358 5.50135C12.0568 5.50536 12.6577 5.52262 13.1487 5.61281C13.6541 5.70563 14.0841 5.88246 14.4341 6.23241C14.8354 6.63369 15.008 7.13897 15.0887 7.73904C15.1663 8.31697 15.1663 9.05191 15.1663 9.96357V10.7034C15.1663 11.6152 15.1663 12.35 15.0887 12.928C15.008 13.5281 14.8354 14.0333 14.4341 14.4346C14.0328 14.8359 13.5276 15.0085 12.9275 15.0892C12.3495 15.1669 11.6147 15.1668 10.7029 15.1668H5.29643C4.3847 15.1668 3.64982 15.1669 3.07184 15.0892C2.47177 15.0085 1.96652 14.8359 1.56524 14.4346C1.16396 14.0333 0.991368 13.5281 0.910688 12.928C0.832982 12.35 0.832995 11.6152 0.833008 10.7034V9.96357C0.832995 9.05184 0.832982 8.31697 0.910688 7.73904C0.991368 7.13897 1.16396 6.63369 1.56524 6.23241C1.91519 5.88246 2.34524 5.70563 2.85057 5.61281C3.34161 5.52261 3.94254 5.50536 4.66356 5.50135Z" fill="#1A1A1A"/></g><defs><clipPath><rect width="16" height="16" fill="white"/></clipPath></defs>' +
      "</svg>" +
      "<span>Upload</span></label>";

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

  function attachTooltipToProductThumbnails(images, productId, version) {
    orderId = allaround_vars.order_id;
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
      // Add a common class to group image anchors
      imageAnchor.classList.add("mockupUUID_" + orderId + productId + version);
    });

    // Initialize Magnific Popup for the gallery
    jQuery(".mockupUUID_" + orderId + productId + version).magnificPopup({
      type: "image",
      closeOnContentClick: true,
      closeBtnInside: true,
      fixedContentPos: true,
      mainClass: "mfp-no-margins mfp-with-zoom", // class to remove default margin from left and right side
      gallery: {
        enabled: true, // Enable gallery mode
      },
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
    if (!thead) return;
    let headerRow = thead.getElementsByTagName("tr")[0];
    if (!headerRow) return;

    // Find the .mockup-head th element
    let mockupHead = headerRow.querySelector(".mockup-head");
    if (!mockupHead) return;

    // Set the colspan attribute
    mockupHead.setAttribute("colspan", maxMockupColumns);

    // Find the .mockup-head th element
    let emptyTfootTd = table.querySelector(".tfoot_empty_column");
    if (!emptyTfootTd) return;

    emptyTfootTd.setAttribute("colspan", maxMockupColumns + 1);
  }

  // Remove specified elements after page load

  function removeElementsAfterLoad() {
    const selectors = [
      ".om__DeleteArtwork",
      ".om_itemQuantPriceEdit",
      ".om__editItemMeta",
      ".om_duplicate_item",
      ".om_delete_item",
      ".delete-attachment",
      ".om_addDesignerNote",
    ];

    selectors.forEach((selector) => {
      document.querySelectorAll(selector).forEach((element) => {
        element.remove();
      });
    });
  }

  // Call the function to remove elements
  if (isEmployee() || isDesigner()) {
    removeElementsAfterLoad();
  }

  // For Designer
  // function removeElementsAfterLoadDesigner() {
  //   const selectors = [
  //     ".om__DeleteArtwork",
  //     ".om_itemQuantPriceEdit",
  //     ".om__editItemMeta",
  //     ".om_duplicate_item",
  //     ".om_delete_item",
  //     ".delete-attachment",
  //     ".om_addDesignerNote",
  //   ];

  //   selectors.forEach((selector) => {
  //     document.querySelectorAll(selector).forEach((element) => {
  //       element.remove();
  //     });
  //   });
  // }

  // Call the function to remove elements
  // if (isDesigner()) {
  //   removeElementsAfterLoadDesigner();
  // }
});
