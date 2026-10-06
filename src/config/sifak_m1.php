<?php

return [
    'examiner_recommendation_threshold' => 70,
    'duration_minutes' => [
        'SEMPRO' => 90,
        'SIDANG_TA' => 120,
    ],
    'score' => [
        'pass_threshold' => 70,
        'revision_threshold' => 60,
    ],
    'policy' => [
        'allow_supervisor_as_examiner' => false,
        'required_examiner_roles' => ['PENGUJI_1', 'PENGUJI_2'],
    ],
];
