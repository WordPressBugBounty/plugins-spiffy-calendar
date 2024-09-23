/*
**	Spiffy Calendar utility scripts on admin pages
**
**  Version 1.9
**
**  Note update version in both Spiffy Calendar and Bonus Add Ons
*/

jQuery(document).ready(function($){
	/*
	** Warn if leaving form without saving
	*/
	spiffycal_form_modified = 0;
    $('form.spiffy-form *').change(function(){
        spiffycal_form_modified = 1;
    });
	$('.spiffycal-custom-fields-sortable-list').bind('DOMSubtreeModified', function () {
        spiffycal_form_modified = 1;	
	});
	window.onbeforeunload = function(){

        if (spiffycal_form_modified == 1) {
            return object_name.areyousure;
        }
    }
    $("form.spiffy-form input[type='submit']").click(function() {
        spiffycal_form_modified = 0;
    });

	// WP colorpicker
	if( typeof $.wp === 'object' && typeof $.wp.wpColorPicker === 'function' ) {
		$('.spiffy-color-field').wpColorPicker();
	}
	
	// Begin/End date check
    //$("#event_begin, #event_end").calendricalDateRange();

	$(".spiffy-date-field").datepicker({
		dateFormat : "yy-mm-dd",
		showButtonPanel: false,
		beforeShowDay: function (date) {
			var date1 = $.datepicker.parseDate('yy-mm-dd', $("#event_begin").val());
			var date2 = $.datepicker.parseDate('yy-mm-dd', $("#event_end").val());
			return [true, date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2)) ? "dp-highlight" : ""];
		},
		onSelect: function(dateText, inst) {
			var olddate1 = $.datepicker.parseDate('yy-mm-dd', $("#event_begin").val());
			var olddate2 = $.datepicker.parseDate('yy-mm-dd', $("#event_end").val());
			$(this).val(dateText);
			var date1 = $.datepicker.parseDate('yy-mm-dd', $("#event_begin").val());
			var date2 = $.datepicker.parseDate('yy-mm-dd', $("#event_end").val());
			var selectedDate = $.datepicker.parseDate('yy-mm-dd', dateText);

			if (date2 < date1) {
				if (dateText == $("#event_begin").val()) {
					$("#event_end").val( $("#event_begin").val() );
				} else {
					$("#event_begin").val( $("#event_end").val() );					
				}
			}
		}
	});	

	togglerecur ();
	$("#spiffy-event-recur").change(function () {
        togglerecur();
    });
	
	toggleCatColor ();
	$("#spiffy_category_bg_color").change(function () {
		toggleCatColor();
	});

	// Watch the bulk actions dropdown, looking for custom bulk actions
  	$("#bulk-action-selector-top, #bulk-action-selector-bottom").on('change', function(e){
  		var $this = $(this);

  		if ( $this.val() == 'set-category' ) {
  			$(".spiffy-category-selector").show();
  		} else {
  			$(".spiffy-category-selector").hide();
  		}
  		if ( $this.val() == 'set-status' ) {
  			$(".spiffy-status-selector").show();
  		} else {
  			$(".spiffy-status-selector").hide();
  		}
	}); 

});

/*
** Toggle custom days multiplier depending on current recur choice
*/
function togglerecur() {
    if (jQuery("#spiffy-event-recur").val() == 'D') {
		jQuery("#spiffy-custom-days-input").attr({ "max" : 199, "min" : 2 });
 		if (jQuery("#spiffy-custom-days-input").val() == 1)	jQuery("#spiffy-custom-days-input").val(2);
        jQuery("#spiffy-custom-days").show();
   } else {
        jQuery("#spiffy-custom-days").hide();
		jQuery("#spiffy-custom-days-input").attr({ "max" : 1, "min" : 1 });
		jQuery("#spiffy-custom-days-input").val(1);
	}
}

/*
** Toggle category color input visibility depending on current category background choice
*/
function toggleCatColor() {
	if (document.getElementById('spiffy_category_bg_color') !== null) {
		if (document.getElementById("spiffy_category_bg_color").checked) {
			jQuery("#spiffy-category-text").show();
		} else {
			jQuery("#spiffy-category-text").hide();
		}
	}
}

/*
** Media uploader for images
*/
var file_frame;

jQuery('.spiffy-fe-submit').on('click', '.spiffy-image-button', function( event ){

    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( file_frame ) {
      file_frame.open();
      return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: jQuery( this ).data( 'uploader_title' ),
      button: {
        text: jQuery( this ).data( 'uploader_button_text' ),
      },
	  library: {
            type: [ 'image' ]
	  },
      multiple: false  // Set to true to allow multiple files to be selected
    });

    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
      // We set multiple to false so only get one image from the uploader
      attachment = file_frame.state().get('selection').first().toJSON();

      // Do something with attachment.id and/or attachment.url here
	  jQuery('.spiffy-image-input').val(attachment.id);
	  jQuery('.spiffy-image-view').attr('src',attachment.url);
    });

    // Finally, open the modal
    file_frame.open();
  });