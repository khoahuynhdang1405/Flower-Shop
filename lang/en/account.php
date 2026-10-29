<?php

return [

    'title' => 'My account',
    'greeting' => 'Hi :name, so lovely to see you again.',
    'subtitle' => 'A little corner for your details and every gift of love you’ve sent with Lá.',
    'member_since' => 'With Lá since :date',
    'help' => 'Need a hand? Call :hotline — the shop is always happy to listen.',

    'groups' => [
        'new' => 'New friend',
        'loyal' => 'Regular',
        'best_friend' => 'Lá’s close friend',
    ],

    'nav' => [
        'label' => 'Account sections',
        'profile' => 'Account details',
        'orders' => 'My orders',
        'password' => 'Change password',
        'logout' => 'Sign out',
    ],

    'profile' => [
        'title' => 'Account details',
        'verified' => 'Verified',
        'description' => 'Lá uses these details to reach you and deliver flowers right on time.',
        'name' => 'Full name',
        'phone' => 'Phone number',
        'email' => 'Email address',
        'email_hint' => 'Your email is used to sign in. Message Lá if you need to change it.',
        'birthday' => 'Date of birth',
        'birthday_invalid' => 'That date of birth doesn’t look right. Could you check it?',
        'saved_at' => 'Details saved at :time',
        'saved' => 'Lá has saved your new details.',
        'submit' => 'Save details',
        'name_required' => 'Please tell Lá your full name.',
        'phone_required' => 'Please leave a phone number so the shop can reach you on delivery.',
    ],

    'address' => [
        'title' => 'Default delivery address',
        'description' => 'Lá will fill in this address whenever you order. You can still change it at checkout.',
        'street' => 'House number, street, ward',
        'street_placeholder' => 'e.g. 18 Vo Van Tan, Ward 6',
        'district' => 'District',
        'district_placeholder' => 'Choose a district',
        'empty' => 'You haven’t saved a default delivery address yet.',
        'saved' => 'Lá will remember your delivery address.',
        'submit' => 'Save address',
        'street_required' => 'Please enter your house number and street.',
        'district_required' => 'Please choose the district to deliver to.',
    ],

    'orders' => [
        'title' => 'Gifts of love you’ve sent',
        'summary' => ':total orders · :completed completed · :preparing being prepared',
        'filter_label' => 'Filter orders',
        'filter_all' => 'All (:count)',
        'filter_processing' => 'In progress (:count)',
        'filter_completed' => 'Completed (:count)',
        'meta' => ':code · :date · :count item(s)',
        'view' => 'View details',
        'reorder' => 'Order again',
        'deliver_today' => 'Delivering today, :time',
        'empty' => 'No orders here yet.',
        'latest_note' => 'Order :code is paid · Lá is wrapping your flowers and writing the card.',
        'status' => [
            'preparing' => 'Being prepared',
            'completed' => 'Completed',
        ],
    ],

    'password' => [
        'title' => 'Change password',
        'description' => 'Keep your little corner safe. Never share your password with anyone.',
        'google_description' => 'You’re signed in with Google. Set a password so you can also sign in with your email.',
        'current' => 'Current password',
        'new' => 'New password',
        'confirm' => 'Confirm new password',
        'hint' => 'At least 8 characters, with uppercase, lowercase and a number.',
        'matched' => 'Passwords match.',
        'not_matched' => 'Passwords don’t match yet.',
        'ready' => 'New password looks good · Ready to update',
        'not_ready' => 'Fill in every field to update your password.',
        'submit' => 'Update password',
        'saved' => 'Your password has been changed. Other devices will need to sign in again.',
        'current_required' => 'Please enter your current password.',
        'current_wrong' => 'Your current password isn’t right.',
        'new_required' => 'Please enter a new password.',
        'same_as_current' => 'Your new password needs to be different from the current one.',
    ],

];
