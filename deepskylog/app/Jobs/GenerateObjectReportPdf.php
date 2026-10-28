<?php

namespace App\Jobs;

use App\Models\DeepskyObject;
use App\Models\User;
use App\Services\ObjectReportDataBuilder;
use App\Services\ObjectReportPdfService;
use App\Services\ObjectSetDataBuilder;
use App\Services\ReportLayoutCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Renders an object report in the background: the status goes to the cache
 * under object_report_pdf:{token} and the PDF to
 * storage/app/object-reports/{token}.pdf.
 *
 * Parameters: the layout (as ReportLayoutCatalog::normalize returns it), the
 * object names in report order, the title, whether to group the objects by
 * constellation, the date of the ephemerides and the observing list the
 * objects come from, if any, for its descriptions.
 */
class GenerateObjectReportPdf extends QueuedPdfJob
{
    /** The ephemerides through the year take a while for thousands of objects. */
    public int $timeout = 3600;

    protected static function prefix(): string
    {
        return 'object_report';
    }

    protected static function directory(): string
    {
        return 'object-reports';
    }

    public static function kindLabel(?string $locale = null): string
    {
        return __('Object report', [], $locale);
    }

    protected static function downloadRoute(): ?string
    {
        return 'object-report.download';
    }

    protected function renderPdf(User $user, callable $progress): string
    {
        $layout = ReportLayoutCatalog::normalize($this->parameters['layout'] ?? []);
        $date = Carbon::parse($this->parameters['date'] ?? now()->toDateString());
        $objects = $this->objects(collect($this->parameters['objects'] ?? []));

        $fields = collect($layout['fields'])->filter(fn (array $f) => $f['width'] > 0)->keys()->all();
        if (($this->parameters['sort'] ?? false) && ! in_array('objectconstellationfull', $fields, true)) {
            // The constellation bars need the constellation names.
            $fields[] = 'objectconstellationfull';
        }

        $rows = app(ObjectReportDataBuilder::class)->rows(
            $objects,
            $user,
            $fields,
            $date,
            isset($this->parameters['list_id']) ? (int) $this->parameters['list_id'] : null,
            fn (int $done, int $total) => $progress(['phase' => 'building', 'done' => $done, 'total' => $total])
        );

        $progress(['phase' => 'rendering', 'done' => count($rows), 'total' => count($rows)]);

        return app(ObjectReportPdfService::class)->render($layout, $rows, [
            'title' => (string) ($this->parameters['title'] ?? ''),
            'sort' => (bool) ($this->parameters['sort'] ?? false),
            'date' => now()->isoFormat('L'),
            'prepared_for' => $this->preparedFor($user, $date),
            'ephemerides' => $this->ephemerides($user, $date),
        ]);
    }

    /**
     * @param  Collection<int, string>  $names
     * @return Collection<int, DeepskyObject>
     */
    private function objects(Collection $names): Collection
    {
        $byName = $names->chunk(500)
            ->flatMap(fn (Collection $chunk) => DeepskyObject::whereIn('name', $chunk->all())->get())
            ->keyBy('name');

        return $names->map(fn ($name) => $byName->get($name))->filter()->values();
    }

    private function preparedFor(User $user, Carbon $date): ?string
    {
        $instrument = $user->standardInstrument;
        $location = $user->standardLocation;
        if ($instrument === null || $location === null) {
            return null;
        }

        return __('Prepared for :observer with :instrument at :location on :date', [
            'observer' => $user->name,
            'instrument' => $instrument->fullName(),
            'location' => $location->name,
            'date' => $date->isoFormat('D MMMM YYYY'),
        ]);
    }

    private function ephemerides(User $user, Carbon $date): ?string
    {
        $night = app(ObjectSetDataBuilder::class)->nightConditions($user, $date);
        if ($night === null) {
            return null;
        }

        return implode(' - ', [
            __('Sun down: :from to :to', ['from' => $night['sunset'], 'to' => $night['sunrise']]),
            __('Nautical night: :from to :to', ['from' => $night['nautical_end'], 'to' => $night['nautical_begin']]),
            __('Astronomical night: :from to :to', ['from' => $night['astronomical_end'], 'to' => $night['astronomical_begin']]),
            __('Moon up: :from to :to', ['from' => $night['moon_rise'], 'to' => $night['moon_set']]),
        ]);
    }
}
