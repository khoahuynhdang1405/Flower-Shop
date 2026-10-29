<?php

return [

    'brand' => 'Lá & Hoa',
    'workspace' => 'Admin workspace',
    'quote' => ['line_1' => 'Tend the shop each day,', 'line_2' => 'and let the love keep blooming.'],
    'footer' => '© :year Lá & Hoa · Internal admin workspace',
    'skip_to_content' => 'Skip to main content',

    'nav' => [
        'main' => 'Admin navigation',
        'secondary' => 'More links',
        'open' => 'Open menu',
        'close' => 'Close menu',
        'orders_badge' => ':count new orders',
    ],

    'menu' => [
        'dashboard' => 'Overview',
        'orders' => 'Orders',
        'products' => 'Products',
        'collections' => 'Collections',
        'customers' => 'Customers',
        'posts' => 'Flower Stories',
        'promotions' => 'Promotions',
        'settings' => 'Shop settings',
        'help' => 'Help',
        'storefront' => 'Back to the shop',
    ],

    'header' => [
        'breadcrumb' => 'Breadcrumb',
        'root' => 'Admin',
        'notifications' => 'Notifications',
        'account' => ':name’s account',
    ],

    'roles' => [
        'admin' => 'Administrator',
        'staff' => 'Staff',
    ],

    'formats' => [
        'date_long' => 'D MMMM YYYY',
        'date_short' => 'MMM D',
    ],

    'dashboard' => [
        'title' => 'A lovely day at Lá & Hoa',
        'description' => ':weekday, :date · Let’s tend to the little things we love.',
        'today' => 'Today, :date',
    ],

    'list' => [
        'search' => 'Search',
        'edit' => 'Edit',
        'edit_item' => 'Edit :name',
        'view' => 'View details',
        'view_item' => 'View details of :name',
        'empty' => 'Nothing matches these filters.',
    ],

    'collections' => [
        'title' => 'The shop’s flower seasons',
        'description' => 'Arrange flowers by season and by the moments customers send love.',
        'create' => 'Add collection',
        'stats' => [
            'total' => 'Collections',
            'total_note' => ':published live · :drafts draft',
            'season' => 'Seasonal',
            'season_note' => 'Autumn is in the spotlight',
            'occasion' => 'Occasions',
            'occasion_note' => 'Birthdays, love, congratulations, everyday',
            'featured' => 'Featured products',
            'featured_note' => 'In :name',
        ],
        'search_placeholder' => 'Search collections...',
        'filter_type' => 'Filter by type',
        'filter_status' => 'Filter by status',
        'all_types' => 'All types',
        'all_statuses' => 'All statuses',
        'types' => [
            'season' => 'Seasonal',
            'occasion' => 'Occasion',
        ],
        'statuses' => [
            'published' => 'Live',
            'draft' => 'Draft',
        ],
        'products' => ':count products',
        'all_year' => 'All year',
        'planned' => 'Planned for :date',
        'cover_alt' => 'Cover image of the :name collection',
        'note' => 'A product can belong to several collections. Draft collections don’t appear in the shop yet; check the cover image and products before publishing.',
    ],

    'promotions' => [
        'title' => 'A little love, one more offer',
        'description' => 'Manage discount codes and programmes for the shop’s friends.',
        'create' => 'Add promotion',
        'stats' => [
            'active' => 'Active',
            'active_note' => ':count programmes accepting redemptions',
            'usage' => 'Redemptions',
            'usage_note' => 'Across active programmes',
            'upcoming' => 'Upcoming',
            'upcoming_note' => ':name · :date',
            'codes' => 'Promo codes',
            'codes_note' => ':ended ended · :paused paused',
        ],
        'table_title' => 'Discount codes & programmes',
        'table_description' => 'Customers can use 1 offer per order · Amounts in VND',
        'search_placeholder' => 'Search code or programme name...',
        'filter_type' => 'Filter by discount type',
        'filter_status' => 'Filter by status',
        'all_types' => 'All discount types',
        'all_statuses' => 'All statuses',
        'columns' => [
            'code' => 'Code / Programme',
            'value' => 'Type / Discount',
            'condition' => 'Conditions',
            'period' => 'Valid',
            'usage' => 'Used',
            'status' => 'Status',
            'actions' => 'Actions',
        ],
        'types' => [
            'percent' => 'Percentage',
            'fixed' => 'Fixed amount',
            'shipping' => 'Delivery fee',
        ],
        'max_discount' => 'Up to :amount',
        'direct_discount' => 'Direct discount',
        'free_inner_city' => 'Free inner-city delivery',
        'statuses' => [
            'active' => 'Active',
            'upcoming' => 'Upcoming',
            'ended' => 'Ended',
            'paused' => 'Paused',
        ],
        'showing_all' => 'Showing all :total programmes',
        'showing_some' => 'Showing :shown of :total programmes',
        'upcoming_chip' => 'Upcoming · :date',
        'upcoming_body' => 'Code :code takes :value off orders from :min, up to :max. Limited to :limit uses, ends on :end.',
        'edit_program' => 'Edit programme',
        'checklist_title' => 'Check before sending an offer',
        'checklist' => [
            'Each customer can use each code at most once.',
            'Codes can’t be combined on one order.',
            'The minimum order is counted before the delivery fee.',
            'Codes expire automatically at 23:59 on the end date.',
        ],
    ],

];
