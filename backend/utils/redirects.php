<?php

/**
 * Role → dashboard URL mapping (single source of truth).
 */

function getRoleRedirect(string $role): string
{
    $base = '/shifaa_dizad/frontend/pages';

    return match ($role) {
        'admin'            => $base . '/admin/dashboard.html',
        'pharmacist'       => $base . '/professional/pharmacy-dashboard.html',
        'med_rep'          => $base . '/professional/medrep-dashboard.html',
        'lab'              => $base . '/professional/laboratory-dashboard.html',
        'medical_services' => $base . '/professional/medical-services-dashboard.html',
        default            => '/shifaa_dizad/frontend/index.html',
    };
}

/** Map subscription role_type (DB) → users.role */
function roleTypeToUserRole(string $roleType): string
{
    return match ($roleType) {
        'pharmacy'         => 'pharmacist',
        'med_rep'          => 'med_rep',
        'lab'              => 'lab',
        'medical_services' => 'medical_services',
        default            => 'patient',
    };
}

/** Map users.role → subscriptions.role_type */
function userRoleToRoleType(string $userRole): string
{
    return match ($userRole) {
        'pharmacist'       => 'pharmacy',
        'med_rep'          => 'med_rep',
        'lab'              => 'lab',
        'medical_services' => 'medical_services',
        default            => 'pharmacy',
    };
}