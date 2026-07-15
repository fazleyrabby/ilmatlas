<?php

namespace App\Modules\Admission\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Admission\Models\AdmissionCircular;
use App\Modules\Admission\Models\AdmissionSession;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdmissionCircularController extends Controller
{
    public function index(Request $request): View
    {
        $circulars = AdmissionCircular::with(['institute:id,name,slug', 'session:id,name'])
            ->when($request->institute_id, fn ($q, $v) => $q->where('institute_id', $v))
            ->when($request->admission_status, fn ($q, $v) => $q->where('admission_status', $v))
            ->latest()
            ->paginate(30);

        return view('admin.admissions.index', [
            'circulars' => $circulars,
            'institutes' => Institute::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.admissions.form', [
            'circular' => new AdmissionCircular,
            'institutes' => Institute::orderBy('name')->get(['id', 'name']),
            'sessions' => AdmissionSession::where('is_active', true)->orderByDesc('session_start')->get(),
            'selectedInstitute' => $request->institute_id ? Institute::find($request->institute_id) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'admission_session_id' => 'nullable|exists:admission_sessions,id',
            'title' => 'nullable|string|max:500',
            'admission_status' => 'required|string|in:upcoming,open,closing_soon,closed,waitlist',
            'application_start_date' => 'nullable|date',
            'application_end_date' => 'nullable|date|after_or_equal:application_start_date',
            'admission_test_required' => 'boolean',
            'admission_test_date' => 'nullable|date',
            'interview_required' => 'boolean',
            'online_application_available' => 'boolean',
            'application_url' => 'nullable|url|max:1000',
            'documents_required' => 'nullable|string',
            'eligibility_criteria' => 'nullable|string',
            'contact_info' => 'nullable|string|max:1000',
        ]);

        AdmissionCircular::create([
            'uuid' => Str::uuid(),
            ...$data,
            'is_published' => true,
            'published_at' => now(),
        ]);

        return redirect()->route('admin.admissions.index')
            ->with('success', 'Admission circular created.');
    }

    public function edit(AdmissionCircular $circular): View
    {
        return view('admin.admissions.form', [
            'circular' => $circular,
            'institutes' => Institute::orderBy('name')->get(['id', 'name']),
            'sessions' => AdmissionSession::orderByDesc('session_start')->get(),
            'selectedInstitute' => $circular->institute,
        ]);
    }

    public function update(Request $request, AdmissionCircular $circular): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'admission_session_id' => 'nullable|exists:admission_sessions,id',
            'title' => 'nullable|string|max:500',
            'admission_status' => 'required|string|in:upcoming,open,closing_soon,closed,waitlist',
            'application_start_date' => 'nullable|date',
            'application_end_date' => 'nullable|date|after_or_equal:application_start_date',
            'admission_test_required' => 'boolean',
            'admission_test_date' => 'nullable|date',
            'interview_required' => 'boolean',
            'online_application_available' => 'boolean',
            'application_url' => 'nullable|url|max:1000',
            'documents_required' => 'nullable|string',
            'eligibility_criteria' => 'nullable|string',
            'contact_info' => 'nullable|string|max:1000',
        ]);

        $circular->update($data);

        return redirect()->route('admin.admissions.index')
            ->with('success', 'Admission circular updated.');
    }

    public function destroy(AdmissionCircular $circular): RedirectResponse
    {
        $circular->delete();

        return redirect()->route('admin.admissions.index')
            ->with('success', 'Admission circular deleted.');
    }
}
