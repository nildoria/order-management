(function ($) {
  ("use strict");

  // Artwork Scripts Start

  $(".pre-approval-btn").magnificPopup({
    items: {
      src: "#mockup-approval-modal",
      type: "inline",
    },
    callbacks: {
      beforeOpen: function () {
        jQuery("body").css("overflow", "hidden");
      },
      beforeClose: function () {
        jQuery("body").css("overflow", "auto");
      },
      open: function () {
        $("body").addClass("mfp-hide-scroll");
      },
      close: function () {
        $("body").removeClass("mfp-hide-scroll");
      },
    },
  });

  $(document).on(
    "click",
    "#mockup-approval-modal .request-changes-trigger",
    function (e) {
      e.preventDefault();
      $.magnificPopup.close();
    }
  );

  // Function to trigger click event on closest .mfp-close when clicked on .close-modal button
  $(".close-modal").on("click", function () {
    // trigger click to its closest .mfp-close
    $(this).closest(".mfp-content").find(".mfp-close").trigger("click");
  });

  // Function to handle the click event of the approval button
  $(".approval-btn").on("click", function () {
    var approvalBtn = $(this);
    var post_id = approvalBtn.data("post-id");

    approvalBtn.addClass("ml_loading");

    $.ajax({
      url: allaround_vars.ajax_url, // WordPress AJAX URL
      type: "POST",
      data: {
        action: "mockup_proof_approve",
        post_id: post_id,
        approve: true, // Set approve to true when the button is clicked
      },
      success: function (response) {
        if (response.success) {
          $(".revision--product-artwork-buttons").empty();
          $.magnificPopup.close();
          location.reload();
        } else {
          console.log(response.data);
        }
      },
      error: function (xhr, status, error) {
        console.log(error);
      },
      complete: function () {
        approvalBtn.removeClass("ml_loading");
      },
    });
  });

  // Revision form submission
  $("#custom-comment-form").on("submit", function (e) {
    e.preventDefault();

    var $self = $(this);
    var commentText = $("#custom-comment-text").val();
    var postId = $(this).data("post-id");
    var formData = new FormData();

    if (commentText.trim() === "") {
      $(".artwork-revision-upload-new").addClass("error");
      return;
    }

    // Append comment text and file data to FormData object
    formData.append("action", "submit_custom_comment");
    formData.append("comment_text", commentText);
    formData.append("post_id", postId);
    formData.append("fileuploadfield", $("#fileuploadfield")[0].files[0]);

    $self.find(".mockup-submit-feedback").addClass("ml_loading");
    $self
      .find("#custom-comment-text")
      .css("opacity", "0.5")
      .prop("disabled", true);
    $self
      .find(".mockup-submit-feedback")
      .css("opacity", "0.5")
      .prop("disabled", true);

    $self.find(".alarnd--progress-bar").slideDown();

    var uploadStartTime = new Date().getTime();

    // AJAX request to submit the comment
    $.ajax({
      type: "POST",
      url: allaround_vars.ajax_url,
      data: formData,
      processData: false,
      contentType: false,

      success: function (response) {
        if (response.success) {
          var options = {
            year: "numeric",
            month: "long",
            day: "2-digit",
            hour: "numeric",
            minute: "numeric",
            hour12: true,
          };
          var commentTime = new Date(uploadStartTime).toLocaleDateString(
            undefined,
            options
          );
          $self.closest("form").find("#succes").addClass("animate-success");

          // Update UI with the new comment
          var newCommentHTML = `
              <div class="revision-activity customer-message">
                <div class="revision-activity-avatar">
                  <span>
                    ${response.data.customerName.substring(0, 2)}
                  </span>
                </div>
                <div class="revision-activity-content">
                  <div class="revision-activity-title">
                    <h5>${response.data.customerName}</h5>
                    <span>${commentTime}</span>
                  </div>
                  <div class="revision-activity-description">
                    <span class="revision-comment-title">Rejected with  comment:</span>
                      ${
                        !Array.isArray(response.data.uploadedFileURL)
                          ? `
                            <div class="artwork-new-file">
                              <img src="${response.data.uploadedFileURL}" alt="Artwork Image">
                            </div>
                          `
                          : ""
                      }
                    <div>
                      ${response.data.commentText}
                    </div>
                  </div>
                </div>
              </div>
            `;

          setTimeout(function () {
            $("#mockup-comment-submission-modal").fadeIn();
            $("#custom-comment-text, #fileuploadfield, #uploadtextfield").val(
              ""
            );

            $("#custom-comment-form").empty();
            $self.find(".mockup-submit-feedback").removeClass("ml_loading");
          }, 1000);

          $(".artwork-revision-upload-new").removeClass("error");
          $self.find(".alarnd--progress-bar").slideUp();
          $(".revision-activities-all").prepend(newCommentHTML);
          console.log(response.data);
        } else {
          console.log(response.data);
        }
      },
      error: function (xhr, status, error) {
        $self.prop("disabled", false).css("opacity", "1");

        $self
          .find(".alarnd--progress-bar")
          .html("<span>Something went wrong!</span>");
        console.error(error);
      },
    });
  });

  // Show form and hide buttons when clicking on request-changes-trigger
  $(".request-changes-trigger").click(function (e) {
    e.preventDefault();
    $(".artwork-revisionChangeForm").show();
    $(".revision--product-artwork-buttons").hide();
  });

  // Hide form and show buttons when clicking on cancel-feedback-request
  $(".cancel-feedback-request").click(function (e) {
    e.preventDefault();
    $(".revision--product-artwork-buttons").show();
    $(".artwork-revisionChangeForm").hide();
    $(".artwork-revision-upload-new").removeClass("error");
    $("#custom-comment-text, #fileuploadfield, #uploadtextfield").val("");
  });

  // Function to disable the form and buttons
  function disableFormAndButtons() {
    $("#custom-comment-form")
      .find("input, textarea, button")
      .prop("disabled", true);
    $(".revision--product-artwork-buttons").empty(); // Remove inner HTML
  }

  // Check if the first .revision-activity has .customer-message class
  if (
    $(".revision-activities-all .revision-activity:first-child").hasClass(
      "customer-message"
    )
  ) {
    disableFormAndButtons();
    $("#mockup-comment-submission-modal").css("display", "block");
  } else {
    $(".revision--product-artwork-buttons").css("display", "block");
  }

  $(".zoom").magnify({
    speed: 200,
  });

  var hBar = $("#up-bar");
  var hPercent = $("#up-percent");
  var hFile = $("#fileuploadfield");
  var revSubmit = $(".mockup-submit-feedback");

  hPercent.hide();

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

    uploadFile();
  });

  function updateProgressBar(percent) {
    hBar.width(percent + "%");
    hPercent.text(percent + "%");
    if (percent == 100) {
      hFile.prop("disabled", false);
      revSubmit.prop("disabled", false).css("opacity", "1");
    }
  }

  async function uploadFile() {
    hFile.prop("disabled", true);
    revSubmit.prop("disabled", true).css("opacity", "0.5");
    hPercent.show();
    var file = hFile[0].files[0];

    // Dummy upload demo
    await new Promise((resolve) => setTimeout(resolve, 500));
    updateProgressBar(25);
    await new Promise((resolve) => setTimeout(resolve, 500));
    updateProgressBar(50);
    await new Promise((resolve) => setTimeout(resolve, 500));
    updateProgressBar(75);
    await new Promise((resolve) => setTimeout(resolve, 500));
    updateProgressBar(100);
  }
})(jQuery); /*End document ready*/
