<?php

return [
    /*
    | Gate identity type and Sabira authorization role are separate domains.
    | Never persist a Gate type directly into users.role without this mapping.
    */
    'canonical_types' => ['student', 'teacher', 'parent', 'staff', 'admin'],
    'canonical_statuses' => ['active', 'suspended', 'pending'],

    'type_to_local_role_map' => [
        'student' => 'siswa',
        'teacher' => 'guru',
        'parent' => 'wali',
        'staff' => 'karyawan',
        'admin' => 'admin',
    ],

    /* Application roles are configured per Gate client and may be null. */
    'application_role_map' => [
        'super_admin' => 'super_admin',
        'superadmin' => 'super_admin',
        'admin' => 'admin',
        'guru' => 'guru',
        'teacher' => 'guru',
        'karyawan' => 'karyawan',
        'staff' => 'karyawan',
        'employee' => 'karyawan',
        'organisasi' => 'organisasi',
        'asrama' => 'organisasi',
        'siswa' => 'siswa',
        'student' => 'siswa',
        'santri' => 'siswa',
        'wali' => 'wali',
        'parent' => 'wali',
        'guardian' => 'wali',
    ],

    'status_map' => [
        'active' => 'aktif',
        'suspended' => 'suspended',
        'pending' => 'suspended',
    ],

    'allowed_local_roles' => [
        'super_admin',
        'admin',
        'guru',
        'karyawan',
        'organisasi',
        'siswa',
        'wali',
    ],

    'profile_defaults' => [
        // Gate does not expose the formal/muadalah teaching program yet.
        'teacher_kind' => 'formal',
    ],
];
