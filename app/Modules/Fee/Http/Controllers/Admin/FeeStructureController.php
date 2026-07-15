<?php

namespace App\Modules\Fee\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Fee\Services\FeeCalculatorService;
use App\Modules\Fee\Services\FeeModerationService;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    public function __construct(
        private FeeCalculatorService $calculator,
        private FeeModerationService $moderation,
    ) {}

    public function index(Request $request): View
    {
        $query = FeeStructure::with(['institute:id,name,slug', 'feeType:id,name'])
            ->when($request->institute_id, fn ($q, $v) => $q->where('institute_id', $v))
            ->when($request->moderation_status, fn ($q, $v) => $q->where('moderation_status', $v))
            ->when($request->fee_type_id, fn ($q, $v) => $q->where('fee_type_id', $v))
            ->when($request->academic_session, fn ($q, $v) => $q->where('academic_session', $v));

        $fees = $query->latest()->paginate(30);

        return view('admin.fees.index', [
            'fees' => $fees,
            'types' => FeeType::orderBy('name')->get(),
            'institutes' => Institute::orderBy('name')->get(['id', 'name', 'slug']),
            'pendingCount' => $this->moderation->pendingCount(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.fees.form', [
            'fee' => new FeeStructure,
            'types' => FeeType::where('is_active', true)->orderBy('name')->get(),
            'institutes' => Institute::orderBy('name')->get(['id', 'name']),
            'selectedInstitute' => $request->institute_id ? Institute::find($request->institute_id) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'academic_session' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'currency' => 'string|size:3',
            'frequency' => 'required|string|in:one_time,monthly,quarterly,half_yearly,yearly,per_unit',
            'unit_label' => 'nullable|string|max:100',
            'is_negotiable' => 'boolean',
            'grade_range_start' => 'nullable|string|max:50',
            'grade_range_end' => 'nullable|string|max:50',
            'moderation_status' => 'string|in:pending_review,approved',
        ]);

        FeeStructure::create([
            'uuid' => Str::uuid(),
            ...$data,
            'grade_range_start' => $data['grade_range_start'] ?? 'all',
            'grade_range_end' => $data['grade_range_end'] ?? 'all',
            'currency' => $data['currency'] ?? 'BDT',
            'moderation_status' => $data['moderation_status'] ?? 'approved',
            'is_published' => ($data['moderation_status'] ?? 'approved') === 'approved',
            'published_at' => ($data['moderation_status'] ?? 'approved') === 'approved' ? now() : null,
        ]);

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee record created.');
    }

    public function edit(FeeStructure $fee): View
    {
        return view('admin.fees.form', [
            'fee' => $fee->load('institute'),
            'types' => FeeType::where('is_active', true)->orderBy('name')->get(),
            'institutes' => Institute::orderBy('name')->get(['id', 'name']),
            'selectedInstitute' => $fee->institute,
        ]);
    }

    public function update(Request $request, FeeStructure $fee): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'academic_session' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'currency' => 'string|size:3',
            'frequency' => 'required|string|in:one_time,monthly,quarterly,half_yearly,yearly,per_unit',
            'unit_label' => 'nullable|string|max:100',
            'is_negotiable' => 'boolean',
            'grade_range_start' => 'nullable|string|max:50',
            'grade_range_end' => 'nullable|string|max:50',
        ]);

        $fee->update($data);

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee record updated.');
    }

    public function destroy(FeeStructure $fee): RedirectResponse
    {
        $fee->delete();

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee record deleted.');
    }

    public function moderate(Request $request, FeeStructure $fee): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|string|in:approve,reject,request_revision',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        match ($data['action']) {
            'approve' => $this->moderation->approve($fee, $user),
            'reject' => $this->moderation->reject($fee, $user, $data['reason']),
            'request_revision' => $this->moderation->requestRevision($fee, $user, $data['reason']),
        };

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee '.$data['action'].'d.');
    }

    public function history(FeeStructure $fee): View
    {
        $fee->load(['histories' => fn ($q) => $q->latest()->limit(50), 'institute', 'feeType']);

        return view('admin.fees.history', compact('fee'));
    }
}
