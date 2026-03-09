<?php

namespace App\Http\Controllers;

use App\Http\Resources\CertificateResource;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateService $certificateService)
    {
    }

    public function issue(Request $request, int $courseId): JsonResponse
    {
        $certificate = $this->certificateService->issue((int) $request->user()->id, $courseId);

        return response()->json(['data' => CertificateResource::make($certificate)]);
    }
}
