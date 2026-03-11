<?php

return [
    'panel' => [
        'group' => 'Pengaturan',
        'label' => 'Kelola Fitur',
        'title' => 'Kelola Fitur & Segmen',
    ],

    'form' => [
        'feature' => 'Fitur',
        'scope' => 'Cakupan',
        'status' => 'Status',
        'activate' => 'Aktifkan',
        'deactivate' => 'Nonaktifkan',
        'unique_validation' => 'Segmentasi fitur sudah ada. Setiap cakupan fitur hanya dapat memiliki satu segmen yang diaktifkan dan satu segmen yang dinonaktifkan. Untuk melanjutkan, silakan ubah segmen yang ada, atau hapus dan buat yang baru.',
    ],

    'table' => [
        'title' => 'Judul',
        'segment' => 'Segmen',
        'status' => 'Status',
        'activated' => 'AKTIF',
        'deactivated' => 'NONAKTIF',
    ],

    'actions' => [
        'create_segment' => 'Buat Segmen Fitur',
        'segment_feature' => 'Segmentasi Fitur',
        'activate' => 'Aktifkan',
        'activate_description' => 'Tindakan ini akan mengaktifkan fitur yang dipilih untuk pengguna.',
        'deactivate' => 'Nonaktifkan',
        'deactivate_for_all' => 'Nonaktifkan untuk Semua',
        'deactivate_description' => 'Tindakan ini akan menonaktifkan fitur ini untuk pengguna.',
        'purge' => 'Hapus',
        'purge_description' => 'Tindakan ini akan menghapus fitur yang telah diselesaikan dari penyimpanan.',
        'all_features' => 'Semua Fitur',
        'modify' => 'Ubah',
        'modify_heading' => 'Ubah Segmen Fitur',
        'remove' => 'Hapus',
        'remove_heading' => 'Menghapus segmen fitur ini tidak dapat dibatalkan!',
    ],

    'notifications' => [
        'done' => 'Selesai!',
        'activated_for_all' => ':feature diaktifkan untuk pengguna.',
        'deactivated_for_all' => ':feature dinonaktifkan untuk pengguna.',
        'all_features_purged' => 'Semua fitur berhasil dihapus dari penyimpanan.',
        'feature_purged' => 'Fitur :feature berhasil dihapus dari penyimpanan.',
    ],
];
