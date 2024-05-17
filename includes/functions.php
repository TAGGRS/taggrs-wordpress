<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_hash_email($email)
{
    return hash('sha256', strtolower(trim($email)));
}

function tggr_format_cart_items($cart){
    $items = array();

    foreach($cart->cart_contents as $cart_item){

        //var_dump($cart_item);

        $product_id = $cart_item['product_id'];
        $product_quantity = $cart_item['quantity'];
        $item = tggr_format_item($product_id, $product_quantity);
        $items[] = $item;
    }
    
    return $items;
}

function tggr_format_item($product_id, $product_quantity = 1){
    $product = wc_get_product( $product_id );

    $product_name = $product->get_name();
    $product_price = $product->get_price();

    $item = array(
        'item_id' => $product_id,
        'item_name' => $product_name,
        'quantity' => $product_quantity,
        'price' => $product_price
    );

    $categories = wp_get_post_terms($product_id, 'product_cat');
    $category_names = array();
    foreach ($categories as $category) {
        $category_names[] = $category->name;
    }

    if (!empty($categories)) {
        for ($i = 0; $i < count($categories); $i++) {
            $category_number = '';
            if ($i > 0) $category_number = $i + 1;

            $category_name = $categories[$i]->name;
            $item['item_category' . $category_number] = $category_name;
        }
    }

    return $item;    
}