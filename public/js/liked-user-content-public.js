/*jslint white: true, browser: true, devel: true */
/*global $, jQuery, alert, ajax_o */
(function( $ ) {
	'use strict';

	function setButtonState(attId, bucketId, toggleState) {
		var buttonEl = $("[data-attid='" + attId + "'][data-bucket='" + bucketId + "']");
		if(toggleState === "active") {
			buttonEl.addClass("flipped");
		} else if(toggleState === "static") {
			buttonEl.removeClass("flipped");
		}
	}

	function onLucButtonClick() {
		var att_id,
			bucket_id,
			postData;
		att_id = $(this).attr("data-attid");
		bucket_id = $(this).attr("data-bucket");
		postData = {
			action: "add_remove_bucket_attachment",
			att_data: JSON.stringify({ "attid": att_id, "bucket": bucket_id })
		};
		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: ajax_o.ajax_url,
			success: function(response) {
				Array.from($(response.data)).map(function(item) {
					var button;
					if(item.hasOwnProperty('button_states')) {
						if(item.button_states.hasOwnProperty('bucket_a')) {
							button = item.button_states.bucket_a;
							setButtonState(button.attid, button.bucket, button.toggle_state);
						}
						if(item.button_states.hasOwnProperty('bucket_b')) {
							button = item.button_states.bucket_b;
							setButtonState(button.attid, button.bucket, button.toggle_state);
						}
					} else if(item.hasOwnProperty('exceeds_max_allowed_likes')) {
						window.alert('You have exceeded the number of allowed "liked" items. A limit was set by the administrator for all users.');
					}
				});
			}
		}).fail( function (response) {
			console.log(response);
		});
		/* Add a slight delay between toggles to prevent "flipping" both buttons
		 * at the same time by clicking "like" and "love" quickly in succession
		 * when both buttons are in the static state.
		 */
		$(".luc-btn-add").off("click");
		setTimeout( function() { $(".luc-btn-add").click(onLucButtonClick); }, 500);
	}

	function setStateOnLucButtons() {
		/* Get the data attribute data from all the LUC buttons on the page so
		 * it can be sent to the server via AJAX and the button "flip" state
		 * for each button can be set. */
		var lucButtons = $(".luc-btn-add"),
			postData;
		lucButtons = Array.from($(lucButtons)).map(function(button) {
			return {
				"attid": $(button).data("attid"),
				"bucket": $(button).data("bucket")
			};
		});
		postData = {
			action: "get_toggle_state_of_buttons",
			luc_buttons: JSON.stringify(lucButtons)
		};
		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: ajax_o.ajax_url,
			success: function(lucButtons) {
				if(!lucButtons) {
					return;
				}
				lucButtons.map(function(button) {
					var buttonEl = $("[data-attid='" + button.attid + "'][data-bucket='" + button.bucket + "']");
					if(button.toggle_state === "active") {
						buttonEl.addClass('flipped');
					} else if(button.toggle_state === "disable") {
						buttonEl.unbind("click");
						buttonEl.removeClass("luc-btn-add");
						buttonEl.addClass("disable");
					}
				});
			}
		}).fail( function (response) {
			console.log(response);
		});
	}

	$(window).load(function() {
		$(".luc-btn-add").click(onLucButtonClick);
		// Determine the inital state (static or active) for each LUC button on
		// the page.
		setStateOnLucButtons();
	});

}( jQuery ));
