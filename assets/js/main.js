(function ($) {
  ("use strict");

  // Order Management Scripts Start

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

  $("#uploadtextfield").click(function () {
    $("#fileuploadfield").click();
  });
  $("#uploadbrowsebutton").click(function (e) {
    e.preventDefault();
    $("#fileuploadfield").click();
  });
  $("#fileuploadfield").change(function () {
    // Get the file name from the full path
    var fileName = $(this).val().split("\\").pop(); // Split the path and get the last part (file name)

    // Set the file name as the value of the upload text field
    $("#uploadtextfield").val(fileName);
  });
})(jQuery); /*End document ready*/
