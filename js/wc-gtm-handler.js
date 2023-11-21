jQuery(document).ready(function($) {
    console.log('loaded!')
    $(document.body).on('item_remove_clicked', function(event, fragments, cart_hash, $button) {
        console.log('removing!')

        // Get the product data
        var removedProduct = wc_gtm_vars.removedProduct;
        console.log(removedProduct)


        if (removedProduct) {
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'remove_from_cart',
                'item': {
                    'id': removedProduct.id,
                    'name': removedProduct.name,
                    'category': removedProduct.category,
                    'quantity': removedProduct.quantity,
                    'price': removedProduct.price
                }
            });
        }
    });
});
