<?php

return [
    'krs' => [
        'allowed_student_statuses' => ['active'],
        'default_max_sks' => 21,
        'max_sks_by_ips' => [
            ['min' => 3.00, 'max_sks' => 24],
            ['min' => 2.50, 'max_sks' => 21],
            ['min' => 2.00, 'max_sks' => 18],
            ['min' => 0.00, 'max_sks' => 15],
        ],
    ],
    'schedule' => [
        'online_requires_room' => false,
        'hybrid_requires_room' => true,
        'active_statuses' => ['draft', 'final', 'rescheduled', 'published', 'planned'],
    ],
];
