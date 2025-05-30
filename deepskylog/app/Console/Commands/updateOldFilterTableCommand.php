<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\FiltersOld;
use Illuminate\Console\Command;

class updateOldFilterTableCommand extends Command
{
    protected $signature = 'update:old-filter-table';

    protected $description = 'Updates the old filter table with the changes from the new version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating old Filter table...');

        // Get all filters from the new database
        $filters = Filter::all();

        // Check if the filter with the given id already exists in the old database
        // If not, create a new filter
        foreach ($filters as $filter) {
            $id = html_entity_decode($filter->id);

            $old_filter = FiltersOld::where('id', $id)->first();

            if (! $old_filter) {
                $this->info('Adding filter: '.$id);
                $old_filter = new FiltersOld;
                $old_filter->id = $id;
                $old_filter->name = html_entity_decode($filter->name);
                $old_filter->type = $filter->type;
                $old_filter->color = $filter->color;
                $old_filter->wratten = $filter->wratten;
                $old_filter->schott = $filter->schott;
                $old_filter->observer = $filter->observer;
                $old_filter->filteractive = $filter->filteractive;
                $old_filter->save();
            }
        }
    }
}
