jQuery(document).ready(function ($) {
	console.log(222);
	$('select').on('change', function (e) {
		// e.preventDefault();
		var product_id = $('#product_id_custom');
		var optionSelect = $(this).find(':selected').val();
		var attributeName = $(this).data('attribute_name');
		var data;

		if (
			attributeName == 'attribute_pa_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80'
		) {
		} else {
			var data = {
				// action: 'addItemAJAX_callback',
				pa_size: optionSelect,
				product_id: product_id,
			};
		}

		var data;
		switch (attributeName) {
			case 'attribute_pa_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80':
				data = {
					// action: 'addItemAJAX_callback',
					'attribute_pa_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80':
						optionSelect,
					product_id: product_id,
				};
				break;

			case attributeName == 'attribute_pa_size':
				data = {
					// action: 'addItemAJAX_callback',
					pa_size: optionSelect,
					product_id: product_id,
				};
				break;

			case attributeName == 'attribute_pa_set':
				data = {
					// action: 'addItemAJAX_callback',
					pa_set: optionSelect,
					product_id: product_id,
				};
				break;

			default:
				break;
		}

		var url = '?wc-ajax=get_variation';
		var secondUrl = $('#current_url').val();
		var res;

		$.ajax({
			type: 'POST',
			url: url,
			data: data,
			dataType: 'JSON',
			success: function (res) {
				console.log(res);

				if (res == false) {
					// var url = 'admin-ajax.php';

					$.ajax({
						type: 'POST',
						// url: url,
						url: secondUrl,
						data: data,
						dataType: 'JSON',
						success: function (response) {
							console.log(response);
						},
					});
				}
			},
		});

		//https://dev.deesse.ee/en/?wc-ajax=get_variation
		// attribute_pa_set: bottom
		// attribute_pa_size: m
		// product_id: 95224
	});
});
