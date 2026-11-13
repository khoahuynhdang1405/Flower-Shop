<?php

return [

    'payment_methods' => [
        'cod' => 'COD',
        'vnpay' => 'VNPAY',
        'momo' => 'MoMo',
        'bank_transfer' => 'Bank transfer',
    ],

    'payment_statuses' => [
        'pending' => 'Unpaid',
        'paid' => 'Paid',
        'failed' => 'Payment failed',
        'refunded' => 'Refunded',
    ],

    'status_logs' => [
        'received' => 'Your order has been received',
        'received_description' => 'Lá has received your order and will start picking your flowers soon.',
    ],

    'errors' => [
        'empty_cart' => 'Your flower basket is empty. Please pick some flowers first.',
        'time_slot_required' => 'Please choose a delivery time slot.',
        'time_slot_passed' => 'This time slot has already started today. Please choose another one.',
    ],

    'confirmation' => [
        'title' => 'We have received your love',
        'breadcrumb' => 'Order confirmation',
        'intro' => 'Thank you, :name, for letting Lá & Hoa deliver such a gentle gift for you.',
        'success' => 'Order placed · :code',
        'success_note' => 'We will take care of every stem.',
        'badge' => [
            'paid' => 'Paid via :method',
            'cod' => 'Pay on delivery',
            'pending' => 'Unpaid',
            'failed' => 'Payment unsuccessful',
        ],
        'payment' => [
            'pending' => 'Your order is waiting for payment via :method. We are holding it for you, scan the QR code to finish.',
            'failed' => 'The :method payment did not go through. Your order is still held, please try again.',
            'gateway_error' => ':method is a little busy right now. Your order is still held, please try again in a few minutes.',
            'retry' => 'Pay via :method',
        ],
        'schedule' => [
            'title' => 'A lovely bouquet on its way',
            'month' => 'Month :month',
            'slot' => ':slot · Hand-delivered to the recipient',
        ],
        'receiver' => 'Recipient',
        'sender' => 'Sender',
        'notes' => 'Note: :notes',
        'card' => [
            'title' => 'A card just for :name',
            'meta' => 'From: :sender · To: :receiver',
        ],
        'next' => [
            'title' => 'Next, we will prepare your flowers',
            'body' => 'You can follow your bouquet with code :code and the sender\'s phone number. We will message you when it is on its way.',
        ],
        'track' => 'Track your order',
        'home' => 'Back to home',
        'help' => [
            'title' => 'Anything else to tell us?',
            'body' => 'Call :hotline and mention code :code. If you need to change the schedule or message, let us know before the flowers are arranged.',
        ],
    ],

    'mail' => [
        'subject' => 'Lá & Hoa · Order placed :code',
        'greeting' => 'Hi :name,',
        'intro' => 'Thank you for letting Lá & Hoa deliver such a gentle gift for you. We have received order :code and will care for every stem.',
        'payment' => 'Payment: :status',
        'items' => [
            'item' => 'Item',
            'quantity' => 'Qty',
            'total' => 'Amount',
        ],
        'subtotal' => 'Subtotal',
        'discount' => 'Promotion :code',
        'shipping' => 'Delivery fee',
        'free' => 'Free',
        'total' => 'Total',
        'schedule' => 'Delivery',
        'receiver' => 'Receiver',
        'address' => 'Address',
        'card' => 'Card message',
        'visit' => 'Visit Lá & Hoa',
        'signature' => 'With love,',
    ],

];
