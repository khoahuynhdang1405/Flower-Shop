<?php

return [

    'quantity_invalid' => 'Please choose a quantity of at least 1.',
    'product_unavailable' => 'Lá isn’t taking orders for this bouquet right now — please pick another one.',
    'size_unavailable' => 'This size is no longer available — please choose another size.',
    'addon_unavailable' => 'This gift is out of stock right now — please pick another one.',
    'item_missing' => 'This item is no longer in your cart.',
    'free_addon_single' => 'Free gifts come one per order.',

    'attributes' => [
        'quantity' => 'quantity',
        'code' => 'promo code',
    ],

    'page' => [
        'title' => 'Your flower cart',
        'breadcrumb' => 'Cart',
        'intro' => 'One bouquet, one kind word. Let’s wrap it up with care together.',
    ],

    'steps' => [
        'label' => 'Ordering steps',
        'cart' => 'Cart',
        'checkout' => 'Checkout',
        'done' => 'Done',
    ],

    'table' => [
        'caption' => 'Items in your cart',
        'product' => 'Product',
        'quantity' => 'Quantity',
        'total' => 'Total',
    ],

    'line' => [
        'size' => ':size size',
        'unit_price' => 'Unit price :price',
        'edit' => 'Edit choice',
        'edit_label' => 'Edit your choice for “:name”',
        'remove' => 'Remove',
        'remove_label' => 'Remove “:name” from the cart',
        'quantity_label' => 'Quantity of “:name”: :quantity',
        'decrease' => 'One less “:name”',
        'increase' => 'One more “:name”',
    ],

    'free_gift' => [
        'title' => 'A :name, sent from the shop with your flowers',
        'body' => 'We’ve added 1 free :name. You can write your own message at checkout.',
        'price' => 'Free',
        'remove_label' => 'Remove “:name” from the cart',
    ],

    'continue' => 'Keep choosing flowers',

    'promotion' => [
        'label' => 'Promo code',
        'placeholder' => 'Enter your code',
        'apply' => 'Apply',
        'applied' => 'Applied',
        'remove' => 'Remove code',
        'remove_label' => 'Remove code :code',
        'applied_note' => ':amount off your flower order.',
        'below_minimum' => 'Code :code works on orders from :amount — add :missing more to use it.',
        'unavailable' => 'Code :code can’t be used anymore — please try another one.',
        'invalid' => 'Lá couldn’t find this code — please check it again.',
    ],

    'summary' => [
        'title' => 'Wrapping up your order',
        'subtotal' => 'Subtotal (:count item)|Subtotal (:count items)',
        'discount' => 'Promo :code',
        'shipping' => 'Delivery',
        'shipping_pending' => 'Calculated at checkout',
        'shipping_note_free' => 'Orders from :amount get free inner-city delivery.',
        'shipping_note_pending' => 'Add :missing more for free inner-city delivery. The delivery fee depends on your area and is calculated at checkout.',
        'total' => 'Estimated total',
        'checkout' => 'Proceed to checkout',
        'fresh_note' => 'Flowers are arranged fresh for each order. The shop will confirm the delivery time before preparing.',
    ],

    'empty' => [
        'title' => 'Your flower cart is empty',
        'body' => 'Take a look at the bouquets the shop arranged this morning and pick one you love.',
        'cta' => 'Choose flowers',
    ],

    'flash' => [
        'added' => 'Lá has added the flowers to your cart.',
        'removed' => 'This item has been removed from your cart.',
    ],

];
