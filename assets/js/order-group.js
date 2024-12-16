jQuery(document).ready(function ($) {
  // Handle form submission
  $("#order-group-form").on("submit", function (e) {
    e.preventDefault();

    let formData = {
      action: "create_order_group",
      post_title: $("#post_title").val(),
      post_id: $("#order-group-form").data("post-id"),
      order_group_products_main: $("#order_group_products_main").val(),
      order_group_products_sites: $("#order_group_products_sites").val(),
      order_group_products_flash: $("#order_group_products_flash").val(),
      nonce: alarnd_order_group_vars.nonce,
    };

    $.ajax({
      type: "POST",
      url: alarnd_order_group_vars.ajax_url,
      data: formData,
      success: function (response) {
        if (response.success) {
          //   window.location.href = response.data.redirect_url;
          location.reload();
        } else {
          alert("Failed to create Order Group: " + response.data.message);
        }
      },
      error: function (xhr, status, error) {
        alert("An error occurred: " + error);
      },
    });
  });

  // Function to format the options with thumbnails
  function formatProduct(product) {
    if (!product.id) {
      return product.text;
    }
    var $product = $(
      '<span><img src="' +
        $(product.element).data("thumbnail") +
        '" class="grouping-product-thumbnail" /> ' +
        product.text +
        "</span>"
    );
    return $product;
  }

  // Initialize Select2 with custom templates
  $(
    "#order_group_products_main, #order_group_products_sites, #order_group_products_flash"
  ).select2({
    placeholder: "Select products",
    allowClear: true,
    templateResult: formatProduct,
    templateSelection: formatProduct,
    dropdownCssClass: "order-group-select2-dropdown",
  });

  $(".save_order_group_text").on("click", function (e) {
    e.preventDefault();

    let $button = $(this);
    let orderId = $button.data("order-id");
    let $textarea = $button.siblings(".orderGroup_textInput");
    let textValue = $textarea.val();

    // Add loading state to the button
    $button.prop("disabled", true).text("Saving...");

    $.ajax({
      type: "POST",
      url: alarnd_order_group_vars.ajax_url,
      data: {
        action: "save_order_group_text",
        order_id: orderId,
        text_value: textValue,
        nonce: alarnd_order_group_vars.nonce,
      },
      success: function (response) {
        if (response.success) {
          $button.text("Saved!").removeClass("error");
          Toastify({
            text: `#${orderId} note saved successfully!`,
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
          alert("Failed to save text: " + response.data.message);
          $button.addClass("error");
        }
      },
      error: function (xhr, status, error) {
        alert("An error occurred: " + error);
      },
      complete: function () {
        // Reset the button state after 2 seconds
        setTimeout(function () {
          $button.prop("disabled", false).text("Save");
        }, 2000);
      },
    });
  });

  // AGENT SALES JS START

  // Datepicker setup
  $("#start_date, #end_date").datepicker({
    dateFormat: "yy-mm-dd",
  });

  // Load current year's data on page load
  loadSalesData({ year_filter: currentYear });

  // Handle form submission
  $("#filter-submit").on("click", function () {
    const startDate = $("#start_date").val();
    const endDate = $("#end_date").val();
    const yearFilter = startDate && endDate ? "" : $("#year-select").val();

    if (startDate && endDate) {
      $("#year-select").prop("disabled", true);
    } else {
      $("#year-select").prop("disabled", false);
    }

    loadSalesData({
      start_date: startDate,
      end_date: endDate,
      year_filter: yearFilter,
    });
  });

  // on click to reset filter enable year select
  $(".reset_button").on("click", function () {
    $("#year-select").prop("disabled", false);

    setTimeout(() => {
      $("#filter-submit").trigger("click");
      $("#filter-submit-agent").trigger("click");
    }, 200);
  });

  // AJAX function to load sales data
  function loadSalesData(params) {
    const defaultParams = {
      action: "filter_agent_sales",
      nonce: alarnd_order_group_vars.nonce,
    };

    $.ajax({
      url: alarnd_order_group_vars.ajax_url,
      method: "POST",
      data: { ...defaultParams, ...params },
      beforeSend: function () {
        $("#agent-sales-results").html("<p>Loading...</p>");
      },
      success: function (response) {
        if (response.success) {
          renderSalesData(response.data);
        } else {
          $("#agent-sales-results").html("<p>No sales data available!</p>");
        }
      },
      error: function () {
        $("#agent-sales-results").html("<p>Error loading data.</p>");
      },
    });
  }

  // Render sales data
  function renderSalesData(data) {
    let html =
      "<table><thead><tr><th>Agent Name</th><th>Month</th><th>Total Sales</th></tr></thead><tbody>";
    if ($.isEmptyObject(data)) {
      html += '<tr><td colspan="3">No sales data available!</td></tr>';
    } else {
      $.each(data, function (agentId, agent) {
        const agentNameLink = `<a href="${agent.url}" target="_blank">${agent.name}</a>`;
        html += `<tr><td rowspan="${
          Object.keys(agent.sales).length + 1
        }">${agentNameLink}</td></tr>`;
        $.each(agent.sales, function (month, total) {
          html += `<tr><td>${month}</td><td>${total.toFixed(2)}</td></tr>`;
        });
      });
    }
    html += "</tbody></table>";
    $("#agent-sales-results").html(html);
  }

  // Agent Single Sales Data Ajax
  // Load current year's data on page load
  loadAgentSalesData({ year_filter: currentYear });

  // Handle form submission
  $("#filter-submit-agent").on("click", function () {
    const startDate = $("#start_date").val();
    const endDate = $("#end_date").val();
    const yearFilter = startDate && endDate ? "" : $("#year-select").val();

    if (startDate && endDate) {
      $("#year-select").prop("disabled", true);
    } else {
      $("#year-select").prop("disabled", false);
    }
    
    loadAgentSalesData({
      author_id: authorId,
      start_date: startDate,
      end_date: endDate,
      year_filter: yearFilter,
    });
  });

  // AJAX function to fetch sales data
  function loadAgentSalesData(params) {
    const defaultParams = {
      action: "filter_author_sales",
      author_id: authorId,
    };

    $.ajax({
      url: ajaxUrl,
      method: "POST",
      data: { ...defaultParams, ...params },
      beforeSend: function () {
        $("#ajax-results").html("<p>Loading...</p>");
      },
      success: function (response) {
        if (response.success) {
          renderAgentSalesData(response.data);
        } else {
          $("#ajax-results").html("<p>No sales data available!</p>");
        }
      },
      error: function () {
        $("#ajax-results").html("<p>Error loading data.</p>");
      },
    });
  }

  // Render agent sales data in table format
  function renderAgentSalesData(data) {
    let html = '<div class="om_agent_sale_page">';

    // Sales by Month
    if (!$.isEmptyObject(data.sales_by_month)) {
      html += "<div class='om__sales_by_month'><h5>Sales By Month</h5>";
      html +=
        "<table><thead><tr><th>Month</th><th>Total Sales</th></tr></thead><tbody>";
      $.each(data.sales_by_month, function (month, total) {
        html += `<tr><td>${month}</td><td>${total.toFixed(2)}₪</td></tr>`;
      });
      html += "</tbody></table></div>";
    }

    // Sales by Day
    if (!$.isEmptyObject(data.sales_by_day)) {
      html += "<div class='om__sales_by_days'><h5>Sales By Day</h5>";
      html +=
        "<table><thead><tr><th>Day</th><th>Total Sales</th></tr></thead><tbody>";
      $.each(data.sales_by_day, function (day, total) {
        html += `<tr><td>${day}</td><td>${total.toFixed(2)}₪</td></tr>`;
      });
      html += "</tbody></table></div>";
    }

    // If no data available
    if (
      $.isEmptyObject(data.sales_by_month) &&
      $.isEmptyObject(data.sales_by_day)
    ) {
      html += "<p>No sales data available!</p>";
    }

    html += "</div>";
    $("#ajax-results").html(html);
  }
});
