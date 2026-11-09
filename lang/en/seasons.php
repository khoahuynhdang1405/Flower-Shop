<?php

return [

    'title' => 'Seasonal flowers',

    // The hero follows the current season (App\Enums\Season); :month_name is the current month.
    'hero' => [
        'eyebrow' => 'Seasonal flowers · :month_name',
        'image_alt' => 'A vase of dahlias, peach roses and autumn leaves on a stone table',
        'spring' => [
            'title_line_1' => 'Spring comes knocking,',
            'title_line_2' => 'and hearts bloom too.',
            'body' => 'A touch of white, a touch of soft pink, a touch of fresh. Lá picks the season’s loveliest flowers to send to the ones you love.',
            'collection' => 'Clear spring collection · From :price',
            'cta' => 'Choose a spring bouquet',
        ],
        'summer' => [
            'title_line_1' => 'Summer shines bright,',
            'title_line_2' => 'and love glows warm.',
            'body' => 'A touch of sunshine, a touch of tangerine, a touch of bright. Lá picks the season’s loveliest flowers to send to the ones you love.',
            'collection' => 'Bright summer collection · From :price',
            'cta' => 'Choose a summer bouquet',
        ],
        'autumn' => [
            'title_line_1' => 'Autumn drops by,',
            'title_line_2' => 'and love stays on.',
            'body' => 'A touch of blush, a touch of peach, a touch of calm. Lá picks the season’s loveliest flowers to send to the ones you love.',
            'collection' => 'Gentle autumn collection · From :price',
            'cta' => 'Choose an autumn bouquet',
        ],
        'winter' => [
            'title_line_1' => 'Winter slows down,',
            'title_line_2' => 'and keeps love warm.',
            'body' => 'A touch of cream, a touch of green, a touch of warmth. Lá picks the season’s loveliest flowers to send to the ones you love.',
            'collection' => 'Peaceful winter collection · From :price',
            'cta' => 'Choose a winter bouquet',
        ],
    ],

    'seasons' => [
        'eyebrow' => 'Every season, its flowers',
        'title' => 'Every season, a shade of love.',
        'aside' => 'Flowers are loveliest when they bloom in season.',
        'current' => 'In season now',
        'from' => 'From :price',
        'items' => [
            'spring' => ['name' => 'Clear spring', 'months' => 'February – April', 'body' => 'Tulips, ranunculus & new beginnings'],
            'summer' => ['name' => 'Bright summer', 'months' => 'May – July', 'body' => 'Sunflowers, yellow roses & sunshine'],
            'autumn' => ['name' => 'Gentle autumn', 'months' => 'August – October', 'body' => 'Chrysanthemums, peach roses & a calm afternoon'],
            'winter' => ['name' => 'Peaceful winter', 'months' => 'November – January', 'body' => 'Cream roses, green leaves & warm words'],
        ],
    ],

    'products' => [
        'eyebrow' => 'Picked by Lá this season',
        'title' => ':month_name’s flowers',
        'descriptions' => [
            'spring' => 'Fresh, clear shades — a little spring to place in the hands of someone you love.',
            'summer' => 'Sunny, vivid shades — a little summer to place in the hands of someone you love.',
            'autumn' => 'Soft, warm shades — a little autumn to place in the hands of someone you love.',
            'winter' => 'Creamy shades and green leaves — a little winter warmth to place in the hands of someone you love.',
        ],
        'count' => ':count designs for this season',
        'badge' => 'Most loved',
        'note' => 'Seasonal flowers may vary slightly with the garden and the weather. Lá always keeps the colours and spirit of the design, and checks with you before swapping any flowers.',
    ],

    'nature' => [
        'eyebrow' => 'In tune with nature',
        'title_line_1' => 'The right season,',
        'title_line_2' => 'the right moment.',
        'items' => [
            'fresh' => ['title' => 'Fresh from the garden', 'body' => 'Picked at their best bloom, so they’re stronger and last longer.'],
            'colors' => ['title' => 'Natural colours', 'body' => 'Each season has its own palette — simple, yet so poetic.'],
            'gentle' => ['title' => 'Gentle on the earth', 'body' => 'Local flowers first, for a shorter journey from garden to your hands.'],
        ],
    ],

    'cta' => [
        'title' => 'Want to wrap a little season of your own?',
        'body_line_1' => 'You pick the colours, Lá picks today’s loveliest stems.',
        'body_line_2' => 'Together we’ll make a bouquet meant for no one else.',
        'button' => 'Ask Lá for seasonal flowers',
    ],

];
