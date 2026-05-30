<?php

namespace App\Http\Controllers;

use App\Actions\Certificates\IssueCertificateAction;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateService $certificateService)
    {
    }

    public function index(Request $request)
    {
        $certificates = $this->certificateService->getUserCertificates((int) $request->user()->id, $request->only('course_id'));

        return response()->json([
            'data' => $certificates->map(fn (Certificate $certificate): array => $this->serializePrivate($certificate))->values(),
        ]);
    }

    public function show(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $this->serializePrivate($certificate->load(['course.instructor']))]);
    }

    public function generate(Request $request, IssueCertificateAction $issueCertificateAction)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $eligibility = $issueCertificateAction->eligibility($request->user(), $course);

        if (! $eligibility['eligible'] && $eligibility['reason'] !== 'certificate_already_exists') {
            return response()->json([
                'message' => 'Certificate eligibility requirements are not met.',
                'eligibility' => $eligibility,
            ], 422);
        }

        $certificate = $issueCertificateAction->execute($request->user(), $course);

        return response()->json([
            'message' => 'Certificate generated successfully.',
            'data' => $this->serializePrivate($certificate->load(['course.instructor'])),
        ], 201);
    }

    public function checkEligibility(Request $request, IssueCertificateAction $issueCertificateAction)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        return response()->json([
            'data' => $issueCertificateAction->eligibility($request->user(), Course::findOrFail($validated['course_id'])),
        ]);
    }

    public function download(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $certificate->certificate_url || ! Storage::disk('public')->exists($certificate->certificate_url)) {
            return response()->json(['message' => 'Certificate file not found'], 404);
        }

        return response()->download(
            Storage::disk('public')->path($certificate->certificate_url),
            $certificate->certificate_number . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function verifyPublic(string $certificateNumber)
    {
        $certificate = $this->certificateService->verifyCertificate($certificateNumber);

        if (! $certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'data' => $this->serializePublic($certificate),
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'certificate_number' => ['required', 'string'],
        ]);

        return $this->verifyPublic($validated['certificate_number']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Manual certificate creation is disabled.'], 405);
    }

    public function update(Request $request, Certificate $certificate)
    {
        return response()->json(['message' => 'Certificate updates are disabled.'], 405);
    }

    public function destroy(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($certificate->certificate_url && Storage::disk('public')->exists($certificate->certificate_url)) {
            Storage::disk('public')->delete($certificate->certificate_url);
        }

        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully.']);
    }

    private function serializePrivate(Certificate $certificate): array
    {
        $certificate->loadMissing(['course.instructor']);

        return [
            'id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'certificate_url' => $certificate->certificate_url,
            'download_url' => url('/api/certificates/' . $certificate->id . '/download'),
            'verification_url' => rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/') . '/verify/' . $certificate->certificate_number,
            'issued_at' => $certificate->issued_at?->toISOString(),
            'course' => [
                'id' => $certificate->course->id,
                'title' => $certificate->course->title,
                'description' => $certificate->course->description,
                'instructor_name' => $certificate->course->instructor?->name,
            ],
        ];
    }

    private function serializePublic(Certificate $certificate): array
    {
        $certificate->loadMissing(['user', 'course.instructor']);

        return [
            'certificate_number' => $certificate->certificate_number,
            'issued_at' => $certificate->issued_at?->toISOString(),
            'student_name' => $certificate->user->name,
            'course' => [
                'id' => $certificate->course->id,
                'title' => $certificate->course->title,
                'description' => $certificate->course->description,
                'instructor_name' => $certificate->course->instructor?->name,
            ],
        ];
    }
}
