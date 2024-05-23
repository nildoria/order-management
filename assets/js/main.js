(function ($) {
  ("use strict");

  // Order Management Scripts Start
  var fileInputs = document.querySelectorAll(".file-input__input");

  fileInputs.forEach(function (input) {
    input.addEventListener("change", function (event) {
      var file = event.target.files[0];
      var productId = input
        .getAttribute("name")
        .replace("file-input[", "")
        .replace("]", "");
      var version = input.getAttribute("data-version");
      var orderNumber = document.querySelector(
        'input[name="order_number"]'
      ).value;

      var formData = new FormData();
      formData.append("file", file);
      formData.append("order_number", orderNumber);
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
      });
  }

  function addRow() {
    var table = document.getElementById("tableMain");
    var rws = table.rows;
    var cols = table.rows[0].cells.length;
    var row = table.insertRow(rws.length);
    var cell;
    for (var i = 0; i < cols; i++) {
      cell = row.insertCell(i);
      cell.innerHTML = '<input type="text" placeholder="Enter Value">';
    }
  }

  function addColumn() {
    var table = document.getElementById("tableMain");
    var rws = table.rows;
    var cols = table.rows[0].cells.length;
    var cell;
    for (var i = 0; i < rws.length; i++) {
      cell = rws[i].insertCell(cols);
      cell.innerHTML = '<input type="text" placeholder="Enter Value">';
    }
  }

  let rowButton = document.getElementById("rowButton");
  let columnButton = document.getElementById("columnButton");

  rowButton.addEventListener("click", addRow);
  columnButton.addEventListener("click", addColumn);
  // Add Column Row End

  // $("#uploadtextfield").click(function () {
  //   $("#fileuploadfield").click();
  // });
  // $("#uploadbrowsebutton").click(function (e) {
  //   e.preventDefault();
  //   $("#fileuploadfield").click();
  // });
  $("#file-input-1388").change(function () {
    // Get the file name from the full path
    var fileName = $(this).val(); // Split the path and get the last part (file name)
    console.log(fileName);

    // Set the file name as the value of the upload text field
    $("#uploadtextfield").val(fileName);
  });
})(jQuery); /*End document ready*/
