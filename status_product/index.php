<?php

function dd($dump, $exit = false)
{
    echo '<pre>' . print_r($dump) . '</pre>';

    if ($exit) {
        exit;
    }
}

// add_filter('get_translatable_documents_all', function ($types) {
//     if (! isset($types['product'])) {
//         $types['product'] = get_post_type_object('product');
//     }

//     return array_filter($types);
// });

// require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/styleway/status_product/ajaxVariationProductQty.php';

//* add style stock status to front
function wpdocs_scripts_method()
{
    wp_enqueue_style('stock_status', get_template_directory_uri() . '/status_product/css/stock_status.css', false, false, 'all');
}
add_action('wp_enqueue_scripts', 'wpdocs_scripts_method');

//* add js code to admin
function my_enqueue($hook)
{
    if ('post.php' !== $hook) {
        return;
    }
    wp_enqueue_script('admin', get_template_directory_uri() . '/status_product/js/admin.js');
}
add_action('admin_enqueue_scripts', 'my_enqueue');

//* function post class custom (add first, last class)
function post_class_custom($class = '', $post_id = null, $countCall = null)
{
    // Separates classes with a single space, collates classes for post DIV.

    $setClass = str_replace(['first', 'last'], '', get_post_class($class, $post_id));

    switch ($countCall) {
        case $countCall == 1:
        case $countCall == 5:
        case $countCall == 9:
            $customClass = 'first';
            break;

        case $countCall == 4:
        case $countCall == 8:
        case $countCall == 12:
            $customClass = 'last';
            break;

        default:
            $customClass = '';
            break;
    }
    echo 'class="' . esc_attr(implode(' ', $setClass)) . ' ' . $customClass . '"';
}

//* count
function countStatic()
{
    static $number = 0;
    ++$number;
    return $number;
}

//* Display Extra Fields on General Tab Section
add_action('woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields');
function woo_add_custom_general_fields()
{
    global $post;

    // Get the selected value  <== <== (updated)
    $value = get_post_meta($post->ID, '_stock_status', true);

    if (empty($value)) {
        $value = '';
    }

    echo '<div class="options_group">';

    $options = [
        ''                           => __('Select a value', 'woocommerce'),
        'instock'                    => __('In stock', 'woocommerce'),
        'outofstock'                 => __('Delivery in 7-21 days', 'woocommerce'),
        'onbackorder'                => __('Delivery in 15-25 days', 'woocommerce'),
    ];

    woocommerce_wp_select([
        'id'      => '_stock_status',
        'label'   => __('Stock status', 'woocommerce'),
        'options' => $options, //this is where I am having trouble
        'value'   => $value,
    ]);
    do_action('woocommerce_product_options_stock_status');

    echo '</div>';
}

//* ======= *//

//! Save Fields
add_action('woocommerce_process_product_meta', 'woo_add_custom_general_fields_save');

function woo_add_custom_general_fields_save($post_id)
{
    // Select
    $woocommerce_select = $_POST['_stock_status'];
    if (!empty($woocommerce_select)) {
        update_post_meta($post_id, '_stock_status', esc_attr($woocommerce_select));
    } else {
        update_post_meta($post_id, '_stock_status', '');
    }
}

//* good down

//! get Status product
function getStatusProduct($product_id)
{
    $status = mb_strtolower(get_post_meta($product_id, '_stock_status', true));
    return $status;
}

//! showStatus on productF
function showStatus($product_id = null, $stock_status = false, $single_product = false)
{
    global $wpdb, $product,$posts;

    // if ($product_id == 104862) {
    //     //    dd($product);

    //     // echo $product->get_permalink();

    //     echo $product->get_id();
    //     echo '<br>';
    // }

    // if ($single) {
    // }

    $product    = wc_get_product($product_id);

    if ($product->is_type('variable')) {
        $sql = "
        SELECT SUM(pm.meta_value)
        FROM {$wpdb->prefix}posts as p
        JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product_variation'
        AND p.post_status = 'publish'
        AND p.post_parent = '$product_id'
        AND pm.meta_key = '_stock'
        AND pm.meta_value IS NOT NULL
        ";

        // echo $sql;

        $stockVariationAllQuantity = $wpdb->get_var($sql);
        $stockVariationAllQuantity = $stockVariationAllQuantity ? $stockVariationAllQuantity : 0;

        if (!$stockVariationAllQuantity) {
            // if (!$product->get_stock_quantity()) {
            $stock_status = 'outofstock';
        } else {
            $stock_status = 'instock';
        }
    } else {
        if (!$product->get_stock_quantity()) {
            $stock_status = 'outofstock';
        } else {
            $stock_status = 'instock';
        }
    }

    //! check
    // if (!$product->is_type('variation') && $product->get_stock_quantity()) {
    //     $stock_status = 'instock';

    // // var_dump($product->is_type('variation'));
    //     // var_dump($product->has_child());
    // } else {
    //     if (!$product->is_type('variation') && $product->get_stock_quantity() == 0) {
    //         $stock_status = 'outofstock';
    //     }
    // }

    //! debug start
    // echo 'product_id: ' . $product_id;
    // echo '<br>';
    // echo 'QTY single: ' . $product->get_stock_quantity();
    // echo '<br>';
    // echo 'QTY variable: ' . $stockVariationAllQuantity;
    //! debug end

    // echo $product->get_stock_status();

    // dd($product);

    //! other get id product
    // $sql = "
    //     SELECT SUM(pm.meta_value)
    //     FROM {$wpdb->prefix}posts as p
    //     JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
    //     WHERE p.post_id = '$product_id'
    //     AND pm.meta_key = '_stock'
    //     AND pm.meta_value IS NOT NULL
    //     ";

    //     echo $sql;
    //     echo $stockProductQTY = $wpdb->get_var($sql);
    //! other get id product

    //! $stock_status = get_post_meta($product_id, '_stock_status', true);
    switch ($stock_status) {
        case 'instock':
        case 'в наличии':
        case 'kohe saadaval':
            $status = __('In stock', 'woocommerce');
            break;

        case 'outofstock':
        case 'ожидание 7-21 дней':
        case 'ooteaeg 7-21 päeva':
        case 'delivery in 7-21 days':
            $status = __('Delivery in 7-21 days', 'woocommerce');
            break;

        case 'onbackorder':
        case 'ожидание 15-25 дней':
        case 'ooteaeg 15-25 päeva':
        case 'delivery in 15-25 days':
            $status = __('Delivery in 15-25 days', 'woocommerce');
            break;

        default:
            $status = '';
            break;
    }

    //! onbackorder
    // if ($stock_status == 'onbackorder') {
    //     return true;
    // }

    //! show in products thumbnail
    if (!$single_product && $stock_status != 'outofstock' && $stock_status != 'ожидание 7-21 дней' && $stock_status != 'ooteaeg 7-21 päeva') {
        $stock_status = htmlspecialchars(str_replace(' ', '', $stock_status));
        echo '<div class="status_wrapper"><span class="status ' . $stock_status . '">' . $status . '</span></div>';
    }

    if (!$single_product && $stock_status != 'instock' && $stock_status != 'в наличии' && $stock_status != 'kohe saadaval') {
        $stock_status = htmlspecialchars(str_replace(' ', '', $stock_status));
        echo '<div class="status_wrapper"><span class="status ' . $stock_status . '">' . $status . '</span></div>';
    }

    //! show in single product

    // var_dump($product);

    if ($single_product) {
        ?>

<!-- <div class="woocommerce-variation-availability">
    <p class="stock in-stock"><?= $status ?>
</p>
</div> -->
<!-- <div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div> -->
<?php
    }
    // echo $single_product;
    if ($single_product && $stock_status != 'instock' && $stock_status != 'в наличии' && $stock_status != 'kohe saadaval') {
        //echo '<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>';

        // echo $status;

        echo '<div class="woocommerce-variation-availability"><p class="stock in-stock">' . $status . '</p></div>';
    } elseif ($single_product) {?>
<div class="woocommerce-variation-availability">
    <p class="stock in-stock"><?= $status ?>
    </p>
</div>
<!-- <div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div> -->
<?php
    }
}
//!

/* add custom stock status */
function woocommerce_add_custom_stock_status()
{
    ?>
<script type="text/javascript">
    jQuery(function() {
        jQuery('._stock_status_field').not('.custom-stock-status').remove();
    });
</script>
<?php
    /* update custom status if backorder if varations updated */
    $real_stock_status = get_post_meta($_REQUEST['post'], '_stock_status', true);
    if ($real_stock_status == 'onbackorder') {
        $stock_status = get_post_meta($_REQUEST['post'], '_custom_stock_status', true); //get status from custom meta
        update_post_meta($_REQUEST['post'], '_stock_status', wc_clean($stock_status));
    }

    //! select status
    woocommerce_wp_select(['id' => '_stock_status', 'wrapper_class' => 'custom-stock-status', 'label' => __('Stock status', 'woocommerce'), 'options' => [
        ''                           => __('Select a value', 'woocommerce'),
        'instock'                    => __('In stock', 'woocommerce'),
        'outofstock'                 => __('Delivery in 7-21 days', 'woocommerce'),
        'onbackorder'                => __('Delivery in 15-25 days', 'woocommerce'),
    ], 'desc_tip' => true, 'description' => __('Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce')]);

    //     woocommerce_wp_select(
    //     [
    //         'id'            => '_stock_status',
    //         'value'         => $product_object->get_stock_status('edit'),
    //         'wrapper_class' => 'stock_status_field hide_if_variable hide_if_external hide_if_grouped',
    //         'label'         => __('Stock status', 'woocommerce'),
    //         'options'       => wc_get_product_stock_status_options(),
    //         'desc_tip'      => true,
    //         'description'   => __('Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce'),
    //     ]
    // );
}
add_action('woocommerce_product_options_stock_status', 'woocommerce_add_custom_stock_status');

/* save custom stock status */
function woocommerce_save_custom_stock_status($product_id)
{
    update_post_meta($product_id, '_stock_status', wc_clean($_POST['_stock_status']));
    update_post_meta($product_id, '_custom_stock_status', wc_clean($_POST['_stock_status'])); //save another custom meta since '_stock_status' will be overridden
}
add_action('woocommerce_process_product_meta', 'woocommerce_save_custom_stock_status', 99, 1);

/* get custom stock status */
function get_custom_stock_status($data, $product)
{
    switch ($product->stock_status) {
        case 'instock':
            $data = ['availability' => __('In stock', 'woocommerce'), 'class' => 'in-stock'];
        break;
        case 'outofstock':
        case 'delivery in 7-21 days':
            $data = ['availability' => __('Delivery in 7-21 days', 'woocommerce'), 'class' => 'out-of-stock'];
        break;
        case 'onbackorder':
        case 'delivery in 15-25 days':
            $data = ['availability' => __('Delivery in 15-25 days', 'woocommerce'), 'class' => 'on-backorder'];
        break;
    }
    return $data;
}
add_action('woocommerce_get_availability', 'get_custom_stock_status', 10, 2);

/* change custom stock status after order completion */
function woocommerce_order_change_custom_stock_status($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    $items = $order->get_items();
    foreach ($items as $item) {
        $product_id = $item->get_product_id();

        $real_stock_status = get_post_meta($product_id, '_stock_status', true);
        if ($real_stock_status == 'onbackorder') {
            $stock_status = get_post_meta($product_id, '_custom_stock_status', true); //get status from custom meta
            update_post_meta($product_id, '_stock_status', wc_clean($stock_status));
        }
    }
}
add_action('woocommerce_thankyou', 'woocommerce_order_change_custom_stock_status', 10, 1);

//! add variations on frontend
add_action('woocommerce_after_variations_table', 'get_selected_variation_stock', 11, 0);
function get_selected_variation_stock()
{
    global $product, $wpdb, $wp;

    $current_url = home_url(add_query_arg([], $wp->request));
    $product_id  = $product->get_id();

    // Get the visible product variations stock quantity
    $variations_data = [];
    $child_ids       = $product->get_visible_children();
    $child_ids       = implode(',', $child_ids);

    $sql = "
    SELECT p.ID, pm.meta_value as stock_qty
    FROM {$wpdb->prefix}posts as p
    INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
    WHERE p.post_type LIKE 'product_variation'
    AND p.ID IN ($child_ids) AND pm.meta_key LIKE '_stock'
";

    // echo $sql;
    $results         = $wpdb->get_results($sql);

    foreach ($results as $result) {
        // Set in an indexed array for each variation ID the corresponding stock qty
        $variations_data[$result->ID] = $result->stock_qty;
    }

    echo '<div class="product_info">';

    //! variation quantity
    echo '<ul id="variation_product">';
    foreach ($variations_data as $key => $variation) {?>
<li data-id-variation="<?= $key ?>"
    data-qty="<?= $variation ?>"><?= $key ?>: <?= $variation ?>
</li>
<?php
    }
    echo '</ul>'; ?>
<!-- //! product id -->
<p id="product_id_custom"><?= $product_id; ?>
</p>
<p id="current_url"><?= $current_url; ?>
</p>

</div><!-- product_info -->

<div id="product_info_show" class="single_variation_wrap product_info_show"></div>


<?php
 $sql = "
 SELECT SUM(pm.meta_value)
 FROM {$wpdb->prefix}posts as p
 JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
 WHERE p.post_type = 'product_variation'
 AND p.post_status = 'publish'
 AND p.post_parent = '$product_id'
 AND pm.meta_key = '_stock'
 AND pm.meta_value IS NOT NULL
 ";

    $stockVariationAllQuantity = $wpdb->get_var($sql);

    if (!$stockVariationAllQuantity) {
        $stock_status = 'outofstock';
    } else {
        $stock_status = 'instock';
    } ?>
<script>
    jQuery(document).ready(function($) {
        var vData = <?php echo json_encode($variations_data); ?> ,
            stock = '.woocommerce-variation-availability > .stock';

        $('select option').find(':selected').trigger('click');

        var product_id = $('#product_id_custom').text();

        $('select').on('change', function(e) {
            // e.preventDefault();
            var optionSelect = $(this).find(':selected').val();
            var optionId = $(this).find(':selected').data('id');
            var attributeName = $(this).data('attribute_name');
            var data;

            optionId = ++optionId;

            var option = $(`#variation_product li:nth-child(${optionId})`).text();
            var optionQty = $(`#variation_product li:nth-child(${optionId})`).data('qty');

            // console.log('product_id: ' + product_id);
            // console.log('optionId: ' + optionId);
            // console.log('option: ' + option);
            // console.log('qty: ' + optionQty);

            var stock_status;
            var getAllVariationsQty =
                '<?= $stockVariationAllQuantity ?>';

            if (optionQty) {
                stock_status =
                    '<?= __('In stock', 'woocommerce'); ?>';
            } else {
                stock_status =
                    '<?= __('Delivery in 7-21 days', 'woocommerce'); ?>';
            }

            if (getAllVariationsQty == 0) {
                stock_status =
                    '<?= __('Delivery in 7-21 days', 'woocommerce'); ?>';
            }

            setTimeout(() => {
                $('.single_variation_wrap:not(.product_info_show) p.stock').text(stock_status);

                setTimeout(() => {
                    $('#product_info_show .woocommerce-variation.single_variation')
                        .remove();
                    var originalBlock = $('.woocommerce-variation.single_variation')
                        .clone();

                    $('#product_info_show').html(originalBlock);
                }, 200);
            }, 100);




            if (attributeName == 'attribute_pa_suurus') {
                data = {
                    // action: 'addItemAJAX_callback',
                    'attribute_pa_suurus': optionSelect,
                    product_id: product_id,
                };
            } else if (attributeName == 'attribute_pa_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80') {
                data = {
                    // action: 'addItemAJAX_callback',
                    'attribute_pa_%d1%80%d0%b0%d0%b7%d0%bc%d0%b5%d1%80': optionSelect,
                    product_id: product_id,
                };
            } else if (attributeName == 'attribute_pa_size') {
                data = {
                    pa_size: optionSelect,
                    product_id: product_id,
                };
            } else if (attributeName == 'attribute_pa_set') {
                data = {
                    pa_set: optionSelect,
                    product_id: product_id,
                };
            } else {
                data = {
                    pa_size: optionSelect,
                    product_id: product_id,
                };
            }

            //! ajax
            var urlFirst = '/?wc-ajax=get_variation';
            var secondUrl = $('#current_url').val();
            var res;

            $.ajax({
                type: 'POST',
                url: urlFirst,
                data: data,
                dataType: 'JSON',
                success: function(res) {
                    console.log(res);

                    if (res == false) {
                        // var url = 'admin-ajax.php';

                        $.ajax({
                            type: 'POST',
                            // url: url,
                            url: secondUrl,
                            data: data,
                            dataType: 'JSON',
                            success: function(response) {
                                console.log(response);
                            },
                        });
                    } else {
                        // if (!res.is_in_stock) {
                        //     var textStatus =
                        //         '<?= __('Delivery in 7-21 days', 'woocommerce'); ?>';

                        //     $('#product_info_show .stock').text(textStatus)
                        //         .addClass('out-off-stock');
                        // } else {
                        //     var textStatus =
                        //         '<?= __('In stock', 'woocommerce'); ?>';

                        //     $('#product_info_show .stock').text(textStatus);
                        // }
                    }
                },
            });

        });
    });
</script>
<?php
}

//! add stock qty and status to variation dropdown
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'variations_options_html_callback', 10, 2);

function variations_options_html_callback($html, $args)
{
    $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), [
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => __('Choose an option', 'woocommerce'),
    ]);

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
    $id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
    $class                 = $args['class'];
    $show_option_none      = $args['show_option_none'] ? true : false;
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce'); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

    if (empty($options) && !empty($product) && !empty($attribute)) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[$attribute];
    }

    //! variation start
    global $product, $wpdb;
    // Get the visible product variations stock quantity
    $variations_data = [];
    $child_ids       = $product->get_visible_children();
    $child_ids       = implode(',', $child_ids);
    $results         = $wpdb->get_results("
        SELECT p.ID, pm.meta_value as stock_qty
        FROM {$wpdb->prefix}posts as p
        INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
        WHERE p.post_type LIKE 'product_variation'
        AND p.ID IN ($child_ids) AND pm.meta_key LIKE '_stock'
    ");

    foreach ($results as $result) {
        // Set in an indexed array for each variation ID the corresponding stock qty
        $variations_data[$result->ID] = $result->stock_qty;
    }
    //!variation end

    $html = '<select id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
    $html .= '<option value="">' . esc_html($show_option_none_text) . '</option>';

    if (!empty($options)) {
        if ($product && taxonomy_exists($attribute)) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms($product->get_id(), $attribute, ['fields' => 'all']);

            foreach ($terms as $key => $term) {
                if (\in_array($term->slug, $options)) {
                    $stock_status = get_variation_stock_status($product, $name, $term->slug);

                    static $count = 0;

                    $html .= '<option data-id="' . $key . '" data-qty="' . $variations_data[$count]->stock_quantity . '" value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name) . $stock_status) . '</option>';
                    ++$count;
                }
            }
        } else {
            foreach ($options as $option) {
                // This handles <select 2.4.0 bw compatibility where text attributes were not sanitized.
                $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                $html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
            }
        }
    }

    $html .= '</select>';

    return $html;
}

function get_variation_stock_status($product, $name, $term_slug)
{
    $stock_qty = '';
    foreach ($product->get_available_variations() as $variation) {
        if ($variation['attributes'][$name] == $term_slug) {
            $variation_obj = wc_get_product($variation['variation_id']);
            $stock_qty     = $variation_obj->get_stock_quantity();

            $stock_qty;
            break;
        }
    }

    // return $stock_qty == 0 ? ' - (Out Of Stock)' : ' - ' . $stock_qty . ' In Stock';
}
