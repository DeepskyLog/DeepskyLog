<?php
use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\SearchResultsTable;
use App\Models\SearchIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    foreach (['M 31' => 'GALXY', 'NGC 7000' => 'EMINB'] as $name => $type) {
        DB::table('objects')->insert([
            'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
            'ra' => 0.7, 'decl' => 41, 'mag' => 4.0, 'subr' => 13, 'pa' => 100,
            'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 600, 'diam2' => 300,
            'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
            'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
            'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
            'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
        ]);
        DB::table('search_index')->insert([
            'name' => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => 'objects', 'source_pk' => Str::slug($name),
            'display_name' => $name, 'source_type' => $type, 'ra' => 0.7, 'decl' => 41, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    SearchIndex::markUniqueNames();
});

// The CSV/XLSX export of the object tables (App\Support\Exports): each
// download in a file of its own under storage/app/exports, deleted once sent,
// never in the storage/ root the web server may not write to.

foreach (['exportToCsv' => 'export.csv', 'exportToXLS' => 'export.xlsx'] as $method => $file) {
    it("exports the advanced object search with $method", function () use ($method, $file) {
        $this->actingAs(\App\Models\User::factory()->create());

        Livewire::withoutLazyLoading()
            ->test(AdvancedObjectSearchTable::class, ['filters' => ['constellations' => ['AND']]])
            ->call('toggleColumn', 'constellation')
            ->call($method)
            ->assertFileDownloaded($file);

        expect(file_exists(storage_path($file)))->toBeFalse()
            ->and(glob(storage_path('app/exports/*')))->toBe([]);
    });

    it("exports the search results with $method for a guest", function () use ($method, $file) {
        Livewire::withoutLazyLoading()
            ->test(SearchResultsTable::class, ['q' => 'M 31'])
            ->call($method)
            ->assertFileDownloaded($file);

        expect(glob(storage_path('app/exports/*')))->toBe([]);
    });
}

it('writes the names of the objects in the csv export', function () {
    $download = Livewire::withoutLazyLoading()
        ->test(AdvancedObjectSearchTable::class, ['filters' => ['constellations' => ['AND']]])
        ->call('exportToCsv')
        ->effects['download'];

    expect(base64_decode($download['content']))->toContain('M 31')->toContain('NGC 7000');
});

// Turbine's export ordered on the sorted column's field as such: a table
// sorted on Name (name_link, with sortUsing()) gave "Unknown column
// 'name_link'" and a 500 Server Error (ExportSorting).
it('exports a table sorted on the name in the order of the table', function () {
    $download = Livewire::withoutLazyLoading()
        ->test(AdvancedObjectSearchTable::class, ['filters' => ['constellations' => ['AND']]])
        ->call('sortBy', 'name_link')
        ->call('sortBy', 'name_link')
        ->assertSet('sortDirection', 'desc')
        ->call('exportToCsv')
        ->effects['download'];

    $csv = base64_decode($download['content']);
    expect(strpos($csv, 'NGC 7000'))->toBeLessThan(strpos($csv, 'M 31'));
});

foreach ([AdvancedObjectSearchTable::class => ['filters' => ['constellations' => ['AND']]], SearchResultsTable::class => ['q' => 'M 31']] as $table => $params) {
    it('exports '.class_basename($table).' sorted on every sortable column', function () use ($table, $params) {
        $this->actingAs(\App\Models\User::factory()->create());
        $component = Livewire::withoutLazyLoading()->test($table, $params);

        $fields = collect($component->instance()->columns())
            ->filter(fn ($column) => data_get($column, 'sortable'))
            ->map(fn ($column) => data_get($column, 'field'))
            ->filter()
            ->values();
        expect($fields)->not->toBeEmpty();

        foreach ($fields as $field) {
            $component->call('sortBy', $field)->call('exportToCsv')->assertFileDownloaded('export.csv');
        }
    });
}
