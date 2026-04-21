<?php

namespace App\Http\Controllers;

use App\Services\AdminObjectCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminObjectCheckController extends Controller
{
    public function index(AdminObjectCheckService $service): View
    {
        $this->ensureAdministrator();

        // Cache the report for 10 minutes to avoid expensive recalculations
        $report = cache()->remember('admin_check_objects_report', 600, fn() => $service->buildReport());

        return view('object-admin.check', ['report' => $report]);
    }

    public function destroyOrphanObjectNames(AdminObjectCheckService $service): RedirectResponse
    {
        $this->ensureAdministrator();

        $deleted = $service->deleteOrphanObjectNames();
        cache()->forget('admin_check_objects_report');

        return redirect()
            ->route('admin.objects.check')
            ->with('success', trans_choice('{0} No orphan object names were removed.|{1} Removed 1 orphan object name.|[2,*] Removed :count orphan object names.', $deleted, ['count' => $deleted]));
    }

    public function repairConstellations(AdminObjectCheckService $service): RedirectResponse
    {
        $this->ensureAdministrator();

        $updated = $service->repairConstellationMismatches();
        cache()->forget('admin_check_objects_report');

        return redirect()
            ->route('admin.objects.check')
            ->with('success', trans_choice('{0} No constellation mismatches were corrected.|{1} Corrected 1 constellation mismatch.|[2,*] Corrected :count constellation mismatches.', $updated, ['count' => $updated]));
    }

    public function repairObservationObjectNames(AdminObjectCheckService $service): RedirectResponse
    {
        $this->ensureAdministrator();

        $updated = $service->repairObservationObjectNamesToPrimary();
        cache()->forget('admin_check_objects_report');

        return redirect()
            ->route('admin.objects.check')
            ->with('success', trans_choice('{0} No observations were updated.|{1} Updated 1 observation to its primary object name.|[2,*] Updated :count observations to their primary object names.', $updated, ['count' => $updated]));
    }

    public function exportConstellationMismatches(AdminObjectCheckService $service)
    {
        $this->ensureAdministrator();

        $csv = $service->exportConstellationMismatches();

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="constellation-mismatches-' . now()->format('Y-m-d-His') . '.csv"');
    }

    public function exportOrphanObjectNames(AdminObjectCheckService $service)
    {
        $this->ensureAdministrator();

        $csv = $service->exportOrphanObjectNames();

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="orphan-objectnames-' . now()->format('Y-m-d-His') . '.csv"');
    }

    public function exportAliasFixableObservationMappings(AdminObjectCheckService $service)
    {
        $this->ensureAdministrator();

        $csv = $service->exportAliasFixableObservationMappings();

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="observation-alias-mappings-' . now()->format('Y-m-d-His') . '.csv"');
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdministrator(), 403);
    }
}