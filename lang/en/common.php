<?php

return [

    'language' => 'Language',

    // resources/views/components/form/date-picker.blade.php (weeks start on Monday).
    'date_picker' => [
        'weekdays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'weekdays_short' => ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
        'month' => ':month/:year',
        'previous_month' => 'Previous month',
        'next_month' => 'Next month',
    ],

    // App\Enums\FlowerColor
    'flower_colors' => [
        'pink' => 'Pink',
        'peach' => 'Peach',
        'yellow' => 'Yellow',
        'white' => 'White',
        'red' => 'Red',
        'green' => 'Green',
    ],

];
