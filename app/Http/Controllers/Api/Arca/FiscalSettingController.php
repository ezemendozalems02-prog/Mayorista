<?php

namespace App\Http\Controllers\Api\Arca;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arca\StoreFiscalSettingRequest;
use App\Services\Arca\FiscalSettingService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FiscalSettingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FiscalSettingService $service) {}

    /**
     * POST /api/arca/fiscal-settings
     *
     * Create or update fiscal settings for the authenticated user's organization.
     */
    public function store(StoreFiscalSettingRequest $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $setting = $this->service->createOrUpdate($organizationId, $request->validated());

        $wasCreated = $setting->wasRecentlyCreated;

        return $this->success(
            $setting,
            $wasCreated ? 'Configuración fiscal creada correctamente.' : 'Configuración fiscal actualizada correctamente.',
            $wasCreated ? 201 : 200,
        );
    }

    /**
     * GET /api/arca/fiscal-settings
     *
     * Retrieve fiscal settings for the authenticated user's organization.
     */
    public function show(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        try {
            $setting = $this->service->getByOrganization($organizationId);
        } catch (ModelNotFoundException) {
            return $this->notFound('No hay configuración fiscal registrada para esta organización.');
        }

        return $this->success($setting);
    }
}
