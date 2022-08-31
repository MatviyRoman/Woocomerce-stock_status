<?php
/**
 * Single Product stock.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/stock.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if (!\defined('ABSPATH')) {
    exit;
}
global $wpdb, $product;

$post        = get_the_ID();
$product     = wc_get_product($post);
$product_id  = $product->get_id();

if ($product->is_type('simple')) {
    $sql           = "SELECT meta_key,meta_value FROM {$wpdb->prefix}postmeta as p WHERE post_id = {$product_id} AND meta_key = '_stock_status'";
    // echo $sql;
    $stock_status = $wpdb->get_var($sql);

    echo showStatus($product_id, $stock_status, true); ?>

<!-- <p style="display: none"
   class="stock <?php //echo esc_attr($class);?>"><?php echo wp_kses_post($availability); ?>
</p> -->
<?php
}
