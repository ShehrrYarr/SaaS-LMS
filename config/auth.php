<?php

use App\Models\User;
use App\Models\Superadmin;
use App\Models\Patient;
use App\Models\Branch;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'superadmin' => [
            'driver' => 'session',
            'provider' => 'superadmins',
        ],
        'patient' => [
            'driver' => 'session',
            'provider' => 'patients',
        ],
        'branch' => [
            'driver' => 'session',
            'provider' => 'branches',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
        'superadmins' => [
            'driver' => 'eloquent',
            'model' => Superadmin::class,
        ],
        'patients' => [
            'driver' => 'eloquent',
            'model' => Patient::class,
        ],
        'branches' => [
            'driver' => 'eloquent',
            'model' => Branch::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'patients' => [
            'provider' => 'patients',
            'table' => 'patient_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
