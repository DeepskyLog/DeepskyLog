<?php

namespace App\Livewire;

use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ObservationSearchTable extends PowerGridComponent
{
    use WithExport;

    private const IMPORT_EXPORT_HEADERS = [
        'Object name',
        'Observer',
        'Date',
        'UT Time',
        'Location',
        'Instrument',
        'Eyepiece',
        'Filter',
        'Lens / Barlow',
        'Seeing',
        'Lim. magnitude',
        'SQM',
        'Visibility',
        'Language',
        'Description',
    ];

    public string $tableName = 'observation-search-table';

    public string $sortField = 'observations.observed_on';
    public string $sortDirection = 'desc';

    /** Object name filter passed in from the page (bound via :objectName="$q"). */
    public string $objectName = '';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),

            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::detail()
                ->view('livewire.observation-search-row-detail')
                ->showCollapseIcon(),
        ];
    }

    public function datasource(): Builder
    {
        $query = Observation::query()
            ->leftJoin('objects', 'observations.target_name', '=', 'objects.name')
            ->leftJoin('constellations', 'objects.con', '=', 'constellations.id')
            ->leftJoin('instruments', 'observations.instrument_id', '=', 'instruments.id')
            ->leftJoin('users', 'observations.observer_username', '=', 'users.username')
            ->select([
                'observations.id',
                'observations.target_name',
                'observations.observer_username',
                'observations.observed_on',
                'observations.legacy_date_int',
                'observations.has_drawing',
                'observations.drawing_filename',
                'observations.legacy_source',
                'observations.legacy_id',
                DB::raw('objects.con as constellation_abbr'),
                DB::raw('constellations.name as constellation'),
                DB::raw('COALESCE(users.name, observations.observer_username) as observer_display'),
                DB::raw('users.slug as observer_slug'),
                DB::raw('instruments.aperture_mm as aperture_mm'),
            ]);

        if ($this->objectName !== '') {
            $query->where('observations.target_name', 'LIKE', '%' . $this->objectName . '%');
        }

        return $query;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('target_name')
            ->add('target_name_link', function (object $row) {
                $name = $row->target_name ?? '';
                if (empty($name)) {
                    return '-';
                }
                // Build observation detail URL: /observation/{observer}/{date}/{object}
                $observer = $row->observer_slug ?: $row->observer_username;
                $date = null;
                if (!empty($row->observed_on)) {
                    $date = Carbon::parse($row->observed_on)->format('Y-m-d');
                } elseif (!empty($row->legacy_date_int)) {
                    try {
                        $date = Carbon::createFromFormat('Ymd', (string) $row->legacy_date_int)->format('Y-m-d');
                    } catch (\Throwable $_) {
                        $date = null;
                    }
                }
                if ($observer && $date) {
                    $objectSlug = Str::slug($name, '-');
                    $url = route('observations.detail', [$observer, $date, $objectSlug]);
                    return sprintf(
                        '<a href="%s" class="font-bold hover:underline text-blue-400">%s</a>',
                        e($url),
                        e($name)
                    );
                }
                return e($name);
            })
            ->add('constellation', fn(object $row) => $row->constellation ?? ($row->constellation_abbr ?? '-'))
            ->add('has_drawing')
            ->add('drawing_indicator', function (object $row) {
                if (empty($row->has_drawing)) {
                    return '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-600" aria-label="' . e(__('No drawing')) . '"></span>';
                }

                // Resolve drawing URL using the same logic as the detail view
                $filename = $row->drawing_filename ?? null;
                if (!empty($filename)) {
                    try {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
                        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

                        $drawingUrl = $publicDisk->exists($filename)
                            ? $publicDisk->url($filename)
                            : '/images/' . $filename;
                    } catch (\Throwable $_) {
                        $drawingUrl = '/images/' . $filename;
                    }
                } else {
                    $subfolder = ($row->legacy_source === 'comet') ? 'cometdrawings' : 'drawings';
                    $drawingUrl = '/images/' . $subfolder . '/' . $row->legacy_id . '.jpg';
                }

                $title = e(__('View drawing'));
                $drawingUrl = e($drawingUrl);
                return '<button type="button"'
                    . ' onclick="(function(){'
                    . 'var m=document.getElementById(\'obs-drawing-modal\');'
                    . 'var img=document.getElementById(\'obs-drawing-img\');'
                    . 'if(img){img.src=\'' . $drawingUrl . '\';}'
                    . 'if(m){m.classList.remove(\'hidden\');}'
                    . '})()"'
                    . ' title="' . $title . '"'
                    . ' class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 cursor-pointer hover:bg-amber-400 focus:outline-none" aria-label="' . $title . '">'
                    . '<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">'
                    . '<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>'
                    . '</svg></button>';
            })
            ->add('observer_display')
            ->add('observer_name_link', function (object $row) {
                $display = $row->observer_display ?? $row->observer_username ?? '';
                $slug = $row->observer_slug ?? null;
                if ($slug) {
                    return sprintf(
                        '<a href="%s" class="hover:underline">%s</a>',
                        e(route('observer.show', $slug)),
                        e($display)
                    );
                }
                return e($display);
            })
            ->add('aperture_mm', fn(object $row) => $row->aperture_mm !== null ? (int) $row->aperture_mm : '-')
            ->add('observed_on')
            ->add('date_formatted', function (object $row) {
                if (!empty($row->observed_on)) {
                    return Carbon::parse($row->observed_on)->format('d M Y');
                }
                if (!empty($row->legacy_date_int)) {
                    try {
                        return Carbon::createFromFormat('Ymd', (string) $row->legacy_date_int)->format('d M Y');
                    } catch (\Throwable $_) {
                        return (string) $row->legacy_date_int;
                    }
                }
                return '-';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('Drawing'), 'drawing_indicator', 'observations.has_drawing')
                ->visibleInExport(false)
                ->sortable(),

            Column::make(__('Object'), 'target_name_link', 'observations.target_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Constellation'), 'constellation', 'constellations.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Observer'), 'observer_name_link', 'observations.observer_username')
                ->sortable()
                ->searchable(),

            Column::make(__('Instrument diameter (mm)'), 'aperture_mm', 'instruments.aperture_mm')
                ->sortable(),

            Column::make(__('Date'), 'date_formatted', 'observations.observed_on')
                ->sortable(),
        ];
    }

    public function exportToCsv(bool $selected = false): BinaryFileResponse|bool
    {
        if ($selected && count($this->checkboxValues) === 0) {
            return false;
        }

        $rows = $this->buildImportCompatibleRows($selected);
        $path = $this->makeExportPath('csv');

        $writer = new CsvWriter(new CsvOptions(';', '"'));
        $writer->openToFile($path);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()
            ->download($path, $this->downloadName('csv'))
            ->deleteFileAfterSend(true);
    }

    public function exportToXLS(bool $selected = false): BinaryFileResponse|bool
    {
        if ($selected && count($this->checkboxValues) === 0) {
            return false;
        }

        $rows = $this->buildImportCompatibleRows($selected);
        $path = $this->makeExportPath('xlsx');

        $writer = new XlsxWriter();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::IMPORT_EXPORT_HEADERS));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()
            ->download($path, $this->downloadName('xlsx'))
            ->deleteFileAfterSend(true);
    }

    private function buildImportCompatibleRows(bool $selected): array
    {
        $ids = collect($this->prepareToExport($selected))
            ->pluck('id')
            ->filter(fn(mixed $id) => is_numeric($id))
            ->map(fn(mixed $id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $observations = Observation::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $users = User::query()
            ->whereIn('username', $observations->pluck('observer_username')->filter()->unique())
            ->get(['username', 'name'])
            ->keyBy('username');

        $locations = Location::query()
            ->whereIn('id', $observations->pluck('location_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $instruments = Instrument::query()
            ->whereIn('id', $observations->pluck('instrument_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $eyepieces = \App\Models\Eyepiece::query()
            ->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $filters = Filter::query()
            ->whereIn('id', $observations->pluck('filter_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $lenses = Lens::query()
            ->whereIn('id', $observations->pluck('lens_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $visibilities = DB::table('deepsky_observation_details')
            ->whereIn('observation_id', $ids)
            ->pluck('visibility', 'observation_id');

        return $ids
            ->map(function (int $id) use ($observations, $users, $locations, $instruments, $eyepieces, $filters, $lenses, $visibilities): ?array {
                /** @var Observation|null $observation */
                $observation = $observations->get($id);

                if ($observation === null) {
                    return null;
                }

                $observer = trim((string) ($users->get($observation->observer_username)?->name ?: $observation->observer_username ?: ''));

                return [
                    (string) ($observation->target_name ?? ''),
                    $observer,
                    $this->formatImportDate($observation),
                    $this->formatImportTime($observation),
                    (string) ($locations->get($observation->location_id)?->name ?? ''),
                    (string) ($instruments->get($observation->instrument_id)?->name ?? ''),
                    (string) ($eyepieces->get($observation->eyepiece_id)?->name ?? ''),
                    (string) ($filters->get($observation->filter_id)?->name ?? ''),
                    (string) ($lenses->get($observation->lens_id)?->name ?? ''),
                    $this->nullableInteger($observation->seeing),
                    $this->decimalOrZero($observation->limiting_magnitude),
                    $this->decimalOrZero($observation->sqm),
                    $this->nullableInteger($visibilities->get($observation->id)),
                    $this->exportLanguage($observation->language),
                    $this->exportDescription($observation->description),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function formatImportDate(Observation $observation): string
    {
        if (!empty($observation->observed_on)) {
            return Carbon::parse($observation->observed_on)->format('d-m-Y');
        }

        if (!empty($observation->legacy_date_int)) {
            try {
                return Carbon::createFromFormat('Ymd', (string) $observation->legacy_date_int)->format('d-m-Y');
            } catch (\Throwable $_) {
            }
        }

        return '';
    }

    private function formatImportTime(Observation $observation): string
    {
        $observedTime = trim((string) ($observation->observed_time ?? ''));

        if ($observedTime !== '') {
            if (preg_match('/^(\d{1,2}):(\d{2})/', $observedTime, $matches)) {
                return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
            }
        }

        $legacyTime = preg_replace('/\D+/', '', (string) ($observation->legacy_time_int ?? ''));
        if (!empty($legacyTime)) {
            $legacyTime = str_pad(substr($legacyTime, 0, 4), 4, '0', STR_PAD_LEFT);

            return substr($legacyTime, 0, 2) . ':' . substr($legacyTime, 2, 2);
        }

        return '';
    }

    private function nullableInteger(mixed $value): string
    {
        return is_numeric($value) ? (string) (int) $value : '';
    }

    private function decimalOrZero(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return rtrim(rtrim((string) $value, '0'), '.');
    }

    private function exportLanguage(mixed $language): string
    {
        $value = trim((string) $language);

        return $value !== '' ? strtolower(substr($value, 0, 10)) : 'en';
    }

    private function exportDescription(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<br\s*\/?>/iu', '<br />', $text) ?? $text;
        $text = str_replace(["\r\n", "\r", "\n"], '<br />', $text);
        $text = preg_replace('/\s*<br \/>\s*/u', '<br />', $text) ?? $text;
        $text = preg_replace('/[\t ]+/u', ' ', $text) ?? $text;

        return $text;
    }

    private function makeExportPath(string $extension): string
    {
        return storage_path('app/' . Str::uuid() . '.' . $extension);
    }

    private function downloadName(string $extension): string
    {
        $baseName = strval(data_get($this->setUp, 'exportable.fileName', 'export'));

        return Str::slug($baseName) !== '' ? Str::slug($baseName) . '.' . $extension : 'export.' . $extension;
    }
}
