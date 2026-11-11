<?php

return [

    'page' => [
        'title' => 'Send flowers, send love',
        'breadcrumb' => 'Checkout',
        'intro' => 'Tell us a little. We will take care of the gentle part.',
        'back' => 'Back to cart',
    ],

    'sender' => [
        'title' => 'Your details',
        'name' => 'Full name',
        'name_placeholder' => 'Your full name',
        'phone' => 'Phone number',
        'email' => 'Email for confirmation',
        'email_placeholder' => 'you@email.com',
        'recipient_type' => 'Who are these flowers for',
        'gift' => 'A gift for someone dear',
        'self' => 'I am the recipient',
    ],

    'receiver' => [
        'title' => 'Who will receive the flowers?',
        'description' => 'We only contact the recipient if we need help with delivery.',
        'name' => 'Recipient’s full name',
        'phone' => 'Recipient’s phone number',
        'address' => 'Delivery address',
        'address_placeholder' => 'House number, street, ward',
        'city' => 'Province / City',
        'city_hint' => 'We currently deliver within Ho Chi Minh City only.',
        'district' => 'Delivery area',
        'district_placeholder' => 'Choose a district',
        'notes' => 'Delivery notes (optional)',
        'notes_placeholder' => 'Call the recipient 15 minutes before arriving.',
    ],

    'schedule' => [
        'title' => 'Pick a lovely day',
        'date' => 'Delivery date',
        'date_placeholder' => 'Choose a delivery date',
        'date_required' => 'Please choose a delivery date for us.',
        'time_slot' => 'Delivery time',
        'past_slots_note' => 'Today\'s windows that have already begun are closed.',
        'note' => 'We deliver within your chosen window; arrival may shift slightly with traffic.',
    ],

    'card' => [
        'title' => 'Words you want to send',
        'description' => 'The handwritten card is free; we will copy your words with care.',
        'sender' => 'From',
        'receiver' => 'To',
        'message' => 'Message',
        'message_placeholder' => 'Wishing you a gentle day...',
        'note' => 'Your message stays private and is tucked inside the bouquet.',
    ],

    'payment' => [
        'title' => 'Payment method',
        'methods' => [
            'cod' => ['label' => 'COD', 'hint' => 'Pay on delivery', 'note' => 'Pay in cash when the flowers reach the recipient.'],
            'vnpay' => ['label' => 'VNPAY', 'hint' => 'Card / bank QR', 'note' => 'After placing the order, you will pay securely via VNPAY.'],
            'momo' => ['label' => 'MoMo', 'hint' => 'E-wallet', 'note' => 'After placing the order, you will pay securely via MoMo.'],
        ],
    ],

    'summary' => [
        'title' => 'Your flower order',
        'flower_meta' => ':size size · Qty :quantity',
        'flower_meta_no_size' => 'Qty :quantity',
        'addon' => ':name · :quantity',
        'free' => 'Free',
        'subtotal' => 'Subtotal',
        'discount' => 'Promo :code',
        'shipping' => 'Delivery · :city',
        'shipping_district' => 'Delivery · :district, :city',
        'total' => 'Total',
        'total_note' => 'VAT and delivery included.',
        'submit' => 'Place order · :amount',
        'terms' => 'By placing your order, you agree to Lá & Hoa’s terms of purchase and policies.',
    ],

    'appointment' => [
        'title' => 'Your delivery appointment',
        'empty' => 'Choose a date and time so we can save the slot for you.',
    ],

    'help' => [
        'title' => 'Need a hand?',
        'body' => 'Call :hotline (:hours). We are always happy to help you prepare the gift.',
    ],

];
