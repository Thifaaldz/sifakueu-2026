<?php

return [
    'sections' => [
        ['code' => 'FRONT_MATTER', 'name' => 'Halaman Awal', 'sequence' => 10, 'required' => true, 'template_type' => 'front_matter'],
        ['code' => 'BAB_1', 'name' => 'Bab 1', 'sequence' => 20, 'required' => true, 'template_type' => 'chapter'],
        ['code' => 'BAB_2', 'name' => 'Bab 2', 'sequence' => 30, 'required' => true, 'template_type' => 'chapter'],
        ['code' => 'BAB_3', 'name' => 'Bab 3', 'sequence' => 40, 'required' => true, 'template_type' => 'chapter'],
        ['code' => 'BAB_4', 'name' => 'Bab 4', 'sequence' => 50, 'required' => true, 'template_type' => 'chapter'],
        ['code' => 'BAB_5', 'name' => 'Bab 5', 'sequence' => 60, 'required' => true, 'template_type' => 'chapter'],
        ['code' => 'DAFTAR_PUSTAKA', 'name' => 'Daftar Pustaka', 'sequence' => 70, 'required' => true, 'template_type' => 'bibliography'],
        ['code' => 'LAMPIRAN', 'name' => 'Lampiran', 'sequence' => 80, 'required' => false, 'template_type' => 'appendix'],
    ],
    'upload' => [
        'extensions' => ['pdf', 'docx', 'xlsx', 'csv', 'png', 'jpg', 'jpeg', 'zip'],
        'max_kb' => 20480,
    ],
    'repository' => [
        'default_access_level' => 'internal',
    ],
];
