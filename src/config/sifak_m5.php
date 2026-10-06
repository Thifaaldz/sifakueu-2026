<?php

return [
    'matching' => [
        'weights' => [
            'rumpun' => 0.35,
            'history' => 0.25,
            'publication' => 0.20,
            'certification' => 0.10,
            'preference' => 0.10,
        ],
        'threshold' => 70,
    ],
    'workload' => [
        'high_threshold' => 16,
        'overload_threshold' => 20,
        'penalties' => [
            'low' => 0,
            'normal' => 0,
            'high' => 10,
            'overload' => 25,
        ],
    ],
    'location' => [
        'expires_minutes' => 180,
    ],
];
