/*jslint white: true, browser: true, devel: true */
/*global $, jQuery, alert, ajax_o */
(function( $ ) {
	'use strict';

	$(function() {
		var generateBucketPostsBtn = $(".luc-generate-pages"),
			saveSettingsBtn = $(".luc-save-settings");

		function placeNotice(msg) {
			$("#auto-notices").empty();
			var autoNoticeDiv = $("<div>");
			autoNoticeDiv.addClass("notice notice-success is-dismissible");
			autoNoticeDiv.prepend("<p>");
			autoNoticeDiv.find("p").first().text(msg);
			$("#auto-notices").prepend(autoNoticeDiv);
		}

		/**********************************************************************/

		generateBucketPostsBtn.on("click", function(e) {
			var postData,
				nonce;
			e.preventDefault();
			$(".generate-bucket-pages-spinner").addClass("is-active");
			nonce = $('#_lucnonce').val();
			postData = {
				action: 'create_buckets_for_all_users',
				"_lucnonce": nonce
			};
			$.ajax({
				type: "POST",
				data: postData,
				dataType: "json",
				url: ajax_o.ajax_url,
				success: function (response) {
					if(response.msg) {
						placeNotice(response.msg);
					}
				}
			}).fail( function (response) {
				console.log(response);
			});
			/* Add a little extra spin to the spinner so the user knows the
			 * button did something. */
			setTimeout(
				function() {
					$(".generate-bucket-pages-spinner").removeClass("is-active");
				},
				400
			);
		});

		/**********************************************************************/

		saveSettingsBtn.on("click", function(e) {
			var createBucketsForNewUsers,
				disableLoveFunctionality,
				limitLikes,
				titleFormatString,
				postData,
				nonce;
			e.preventDefault();
			createBucketsForNewUsers = $('#create-buckets-for-new-users').is(":checked") ? 1 : 0;
			disableLoveFunctionality = $('#disable-love-functionality').is(":checked") ? 1 : 0;
			limitLikes = $.trim($('#limit-likes').val());
			titleFormatString = $.trim($('#title-format-string').val());
			nonce = $('#_lucnonce').val();
			postData = {
				action: 'save_settings',
				create_buckets_for_new_users: createBucketsForNewUsers,
				disable_love_functionality: disableLoveFunctionality,
				limit_likes: limitLikes,
				title_format_string: titleFormatString,
				"_lucnonce": nonce
			};
			$.ajax({
				type: "POST",
				data: postData,
				dataType: "json",
				url: ajax_o.ajax_url,
				success: function (response) {
					if(response.msg) {
						placeNotice(response.msg);
					}
				}
			}).fail( function (response) {
				console.log(response);
			});
		});
	}); // DOM is ready!

}( jQuery ));
