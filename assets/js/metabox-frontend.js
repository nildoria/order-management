(function ($) {
("use strict");

$(document).on('click', '.upload-image-button', function(e) {
    e.preventDefault();

    const button = $(this),
          group_item = button.closest('.form-group'),
          target = group_item.find('input[type="text"]'),
          preview = group_item.find('img');

    var image = wp.media({ 
        title: 'Upload Image',
        multiple: false
    }).open()
    .on('select', function(e){
        var uploaded_image = image.state().get('selection').first();
        var image_url = uploaded_image.toJSON().url;
            target.val(image_url);
            preview.attr('src', image_url).show();
            button.nextAll('.remove-image-button').show();
    });
});

$(document).on('click', '.remove-image-button', function(e) {
    e.preventDefault();

    const button = $(this),
        group_item = button.closest('.form-group'),
        target = group_item.find('input[type="text"]'),
        preview = group_item.find('img');

        target.val('');
        preview.hide();
        button.hide();

    return false;
});


$("#order_type-form").on("submit", function (event) {
    event.preventDefault();


    var $self = $(this),
        getData = $self.serializeArray(),
        order_id = $('#order_id').val();

        getData.push(
          {
            name: "action",
            value: 'update_order_type',
          },
          {
            name: "nonce",
            value: all_around_clients_vars.nonce,
          }
        );

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: all_around_clients_vars.ajax_url,
        data: getData,
        success: function(response) {
            if( response.success === true ) {
                alert( response.data.message );
                location.reload();
            } else {
                alert( response.data.message );
            }
        }
    });

    return false;
});

$("#addClientForm").on("submit", function (event) {
    event.preventDefault();

    if (!$(this).valid()) return false;

    var $self = $(this),
        type = $self.data("type"),
        getData = $self.serializeArray();

        let action = "create_client";

        if (type == "edit") {
            action = "update_client";
        }

        getData.push(
          {
            name: "action",
            value: action,
          },
          {
            name: "nonce",
            value: all_around_clients_vars.nonce,
          }
        );

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: all_around_clients_vars.ajax_url,
        data: getData,
        success: function(response) {
            if( response.success === true ) {
                alert( response.data.message );
                location.reload();
            } else {
                alert( response.data.message );
            }
        }
    });

    return false;
});

$("#order_type").on("change", function () {
  // if option has value then show om_order_type_submit otherwise hide it
  if ($(this).val()) {
    $(".om_order_type_submit").show();
  } else {
    $(".om_order_type_submit").hide();
  }
});

// if client_type selected is "company" then show company details fields by class ".om_hidden_details" with slideToggle

$(document).on("change", "#client_type", function () {
  if ($(this).val() == "company") {
    $(".om_hidden_details").fadeIn();
  } else {
    $(".om_hidden_details").fadeOut();
  }
});


if ($("#addClientForm").length > 0) {
    $("#addClientForm").validate();
}

})(jQuery); /*End document ready*/
  