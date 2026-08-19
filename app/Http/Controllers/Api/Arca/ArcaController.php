<?php

namespace App\Http\Controllers\Api\Arca;

use App\Http\Controllers\Controller;
use App\Services\Arca\ArcaConnectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArcaController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ArcaConnectionService $service) {}

    /**
     * POST /api/arca/test-connection
     *
     * Validates configuration and authenticates against ARCA WSAA.
     * Returns environment + expiration on success.
     * Token and sign are NEVER forwarded to the response.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $result = $this->service->testConnection($organizationId);

        $httpStatus = $result['success'] ? 200 : 422;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'],
        ], $httpStatus);
    }
}
