<?php

namespace App\Services\Arca;

use App\Models\FiscalSetting;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FiscalSettingService
{
    /**
     * Create or update fiscal settings for the authenticated user's organization.
     * The organization_id is injected by the HasOrganization trait on create;
     * on update we explicitly set it so updateOrCreate targets the right record.
     */
    public function createOrUpdate(int $organizationId, array $data): FiscalSetting
    {
        // Normalize CUIT: strip dashes and spaces before persisting
        if (isset($data['cuit'])) {
            $data['cuit'] = preg_replace('/[\s\-]/', '', $data['cuit']);
        }

        // Normalize ambiente to canonical DB values (TESTING / PRODUCTION)
        if (isset($data['ambiente'])) {
            $data['ambiente'] = match (strtoupper(trim($data['ambiente']))) {
                'TESTING', 'TEST', 'HOMOLOGACION', 'HOMOLOGACIÓN', 'HOMOLOGACION_PRUEBAS' => 'TESTING',
                'PRODUCTION', 'PRODUCCION', 'PRODUCCIÓN', 'REAL'                          => 'PRODUCTION',
                default                                                                    => $data['ambiente'],
            };
        }

        return FiscalSetting::updateOrCreate(
            ['organization_id' => $organizationId],
            array_merge($data, ['organization_id' => $organizationId]),
        );
    }

    /**
     * Retrieve fiscal settings for a given organization.
     *
     * @throws ModelNotFoundException
     */
    public function getByOrganization(int $organizationId): FiscalSetting
    {
        return FiscalSetting::where('organization_id', $organizationId)->firstOrFail();
    }
}
