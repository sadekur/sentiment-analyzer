jQuery(function ($) {
	$("#easycommerce-reset-settings").on("click", function (e) {
		e.preventDefault();
		easycommerce_modal();

		$.ajax({
			url: `${EASYCOMMERCE.rest_base}/option`,
			type: "DELETE",
			dataType: "JSON",
			data: {
				key: $(this).data("option_key"),
			},
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			success: (resp) => {
				easycommerce_toast(resp.data);
				setTimeout(function(){
					location.reload();
				}, 1000)
			},
			error: (err) => {
				easycommerce_modal(false);
			},
		});
	});

	$(".easycommerce-settings-form").submit(function (e) {
		e.preventDefault();
		easycommerce_modal();

		let formData = $(this).serializeArray();
		let data = {};

		// Convert serialized data array into an object
		$.each(formData, function () {
			if (data[this.name]) {
				if (!data[this.name].push) {
					data[this.name] = [data[this.name]];
				}
				data[this.name].push(this.value || "");
			} else {
				data[this.name] = this.value || "";
			}
		});

		$.ajax({
			url: `${EASYCOMMERCE.rest_base}/option`,
			type: "POST",
			dataType: "JSON",
			data: {
				key: $(this).data("option_key"),
				value: data,
			},
			headers: {
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			success: (resp) => {
				easycommerce_modal(false);
				easycommerce_toast(resp.data);

                if ( resp.success && $(this).data('reload') ) {
                    setTimeout(function(){
                        location.reload();
                    }, 1000)
                }
			},
			error: (err) => {
				easycommerce_modal(false);
			},
		});
	});

	$(document).on('click', '#easycommerce-save-settings', function (e) {
		e.preventDefault();
		$('.easycommerce-settings-form').trigger('submit');
	});

	$(".easycommerce-field-image").on("click", function (event) {
			event.preventDefault();
			var self = $(this);
			var file_frame = (wp.media.frames.file_frame = wp.media({
				title: self.data("title"),
				button: { text: self.data("select-text") },
				multiple: !1,
			}));
			file_frame.on("select", function () {
				attachment = file_frame
					.state()
					.get("selection")
					.first()
					.toJSON();
				self.attr("src", attachment.url);
				self.siblings(".easycommerce-field-image-value").val(attachment.id);
			});
			file_frame.open();
		}
	);

	$(".easycommerce-field-media").on("click", function (event) {
			event.preventDefault();
			var self = $(this);
			var file_frame = (wp.media.frames.file_frame = wp.media({
				title: self.data("title"),
				button: { text: self.data("select-text") },
				multiple: !1,
			}));
			file_frame.on("select", function () {
				attachment = file_frame
					.state()
					.get("selection")
					.first()
					.toJSON();
				self.siblings(".easycommerce-field-media-url").val(attachment.url);
				self.siblings(".easycommerce-field-media-value").val(attachment.id);
			});
			file_frame.open();
		}
	);


	$('.submenu-toggle').on('click', function(e) {
		e.preventDefault();

		var $parentLi 	= $(this).closest('li');
		var $submenu 	= $parentLi.children('.submenu-items');
		var $arrow 		= $(this).find('.arrow-icon');

		$submenu.slideToggle(200);
		$arrow.toggleClass('rotate-180');
	});

	$("#easycommerce-field-countries").select2();
	$("#easycommerce-field-event_types").select2();
});
