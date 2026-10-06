<?php

return [
    'base_domain' => env('SIFAK_BASE_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
    'default_tenant_slug' => env('SIFAK_DEFAULT_TENANT_SLUG', 'fasilkom'),
    'reserved_subdomains' => [
        'www',
        'admin',
        'api',
        'mail',
        'app',
        'assets',
        'static',
        'support',
        'status',
        'super-admin',
    ],
    'modules' => [
        'M1' => 'Sidang Sempro & TA',
        'M2' => 'Surat Menyurat',
        'M3' => 'Monitoring & Alert',
        'M4' => 'KRS & Penjadwalan',
        'M5' => 'Profiling Dosen & Rekomendasi Pengajaran',
        'M6' => 'Profiling Mahasiswa & Rekomendasi Profil Lulusan',
        'M7' => 'Manajemen Dokumen TA & Repositori Digital',
    ],
];
