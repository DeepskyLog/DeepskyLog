<?php

namespace App\Http\Controllers;

use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Services\Observations\ObservationPdfDataBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ObservationSearchExportController extends Controller
{
    public function pdf(Request $request, ObservationPdfDataBuilder $builder)
    {
        try {
            $query = trim((string) $request->query('q', ''));

            $component = new ObservationSearchTable();
            if (method_exists($component, 'boot')) {
                try {
                    $component->boot();
                } catch (\Throwable $_) {
                }
            }

            $component->objectName = $query;

            $rows = $component->datasource()->get();
            $observationIds = $rows
                ->pluck('id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->values();

            $observations = $observationIds->isEmpty()
                ? collect()
                : Observation::query()
                    ->whereIn('id', $observationIds->all())
                    ->get()
                    ->keyBy('id');

            $entries = $observationIds
                ->map(function (int $id) use ($observations, $builder): ?array {
                    $observation = $observations->get($id);

                    return $observation ? $builder->build($observation) : null;
                })
                ->filter()
                ->values();

            $title = $query !== ''
                ? __('Observations matching ":q"', ['q' => $query])
                : __('Observations');

            $html = view('pdf.observation_search', [
                'entries' => $entries,
                'title' => $title,
                'query' => $query,
            ])->render();

            return $this->renderPdf(
                $html,
                'observations_' . now()->format('Ymd_His') . '.pdf',
                'portrait',
            );
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::pdf failed', [
                'query' => (string) $request->query('q', ''),
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate observations PDF'));
        }
    }

    private function renderPdf(string $html, string $filename, string $orientation)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', $orientation);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();
            $output = $dompdf->output();

            return response()->streamDownload(function () use ($output) {
                echo $output;
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        return redirect()
            ->back()
            ->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
    }
}