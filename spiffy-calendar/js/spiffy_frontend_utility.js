/*
**	Spiffy Calendar utility scripts on front end
**
**  Version 2.0
*/

jQuery(document).ready(function($){
	// Maintain scroll position
	if (sessionStorage.scrollTop != "undefined") {
		$(window).scrollTop(sessionStorage.scrollTop);
		sessionStorage.scrollTop = 0;
	}
	

	
	// Toggle grid/list on full calendar display
	$( ".calendar-toggle-button" ).on( "click", function () {
		if ($(".calendar-toggle-button").text() == $(".calendar-toggle-button").attr('data-list')) {
			//
			// Toggling to list mode
			//
			$(".calendar-toggle-button").text($(".calendar-toggle-button").attr('data-grid'));
			$(".spiffy.bigcal").addClass("spiffy-listed");
			$("<span class='spiffy-month-name'></span>" ).insertBefore( ".spiffy.bigcal .day-number" );
			$(".spiffy-month-name").text($(".calendar-toggle-button").attr('data-month'));
			$("[name='grid-list-toggle']").val('list');
			$("a.spiffy-calendar-arrow").attr('href', function(i,a){ 
				return a.replace( /(grid-list-toggle=)[a-z]+/ig, '$1'+'list' );	
			});
		} else {
			//
			// Toggling to grid mode
			//
			$(".calendar-toggle-button").text($(".calendar-toggle-button").attr('data-list'));
			$(".spiffy-month-name").remove();
			$(".spiffy.bigcal").removeClass("spiffy-listed");			
			$("[name='grid-list-toggle']").val('grid');
			$("a.spiffy-calendar-arrow").attr('href', function(i,a){ 
				return a.replace( /(grid-list-toggle=)[a-z]+/ig, '$1'+'grid' );	
			});
		}
	});
	
	// Category filter
	$(".spiffy-category-filter-button").on( "click", function () {
		$the_cat = $(this).attr('data-category');
		if ($(this).hasClass("spiffy-active")) {
			// Turn off the category filter
			$(".spiffy-category-filter-button").removeClass("spiffy-active");
			$(".spiffy-category-filter-button").removeClass("spiffy-inactive");		
			$('[class^="calnk category_"]').removeClass("spiffy-inactive");
			$(".category_"+$the_cat).removeClass("spiffy-active");
			$(".spiffy.bigcal").removeClass("spiffy-filtered");			
			
		} else {
			// Turn on the category filter
			$(".spiffy-category-filter-button").addClass("spiffy-inactive");			
			$(this).removeClass("spiffy-inactive");			
			$(this).addClass("spiffy-active");			
			$('[class^="calnk category_"]').removeClass("spiffy-active").addClass("spiffy-inactive");
			$(".category_"+$the_cat).removeClass("spiffy-inactive").addClass("spiffy-active");
			$(".spiffy.bigcal").addClass("spiffy-filtered");			
		}
		
		// Mark date so it can be hidden on this listed calendar style if all events below are inactive
		$(".day-with-date").each(function() {
			if ($(this).find(".spiffy-active").length !== 0) {
				$(this).removeClass("spiffy-inactive");		// filter is on and this date has active events
			} else if ($(this).find(".spiffy-inactive").length !== 0) {
				$(this).addClass("spiffy-inactive");		// filter is on and this date has no active events			
			} else {
				$(this).removeClass("spiffy-inactive");		// filter is off
			}
		});
	});
	
	// Show custom field headings if followed by field data
	$('[class*="spiffy-custom-field-view-"]').prev().css( "display", "block" );
	
});


// Support hover/click on iOS
jQuery( document.body ).on( 'touchstart', function( e ) {
});