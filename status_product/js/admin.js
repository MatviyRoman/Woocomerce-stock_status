$ = jQuery;

//! stock status
// $(function () {
// 	$('#_stock_status').on('change', function () {
// 		var selected = $('#_stock_status').find(':selected').val();

// 		var isChecked = $('#_manage_stock').is(':checked');

// 		switch (selected) {
// 			case 'instock':
// 				isChecked = $('#_manage_stock').is(':checked');

// 				if (!isChecked) {
// 					$('#_manage_stock').trigger('click');
// 				}

// 				setTimeout(() => {
// 					var stock = $('#_stock').val();
// 					if (stock == 0) {
// 						$('#_stock').val(100);
// 					}
// 				}, 100);

// 				break;

// 			case 'outofstock':
// 				isChecked = $('#_manage_stock').is(':checked');

// 				if (isChecked) {
// 					$('#_manage_stock').trigger('click');
// 				}
// 				break;

// 			// case 'onbackorder':
// 			case 'waiting 7-14 days':
// 			case 'waiting 15-25 days':
// 				isChecked = $('#_manage_stock').is(':checked');

// 				if (!isChecked) {
// 					$('#_manage_stock').trigger('click');
// 				}
// 				setTimeout(() => {
// 					var stock = $('#_stock').val();
// 					if (stock == 0) {
// 						$('#_stock').val(100);
// 					}
// 				}, 100);

// 				break;

// 			default:
// 				break;
// 		}

// 		// console.log(selected);
// 	});
// });
