<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Star extends Model
{
    protected $table = 'stars';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Survey catalogues this star table names its entries after.
     *
     * Every row carries one of these designations, Polaris included, so a
     * chart that prints the name column labels the Pole Star "SOA308".
     */
    private const CATALOGUE_PREFIXES = ['SOA', 'SAO', 'TYC', 'HD', 'HIP', 'PPM', 'GSC', 'BD', 'CD', 'CPD'];

    /**
     * Name to label this star with on a chart, or null for none.
     *
     * The Bayer and Flamsteed designations come first: they are what an
     * observer reads off a chart. A bare catalogue number is not a name at
     * all, so a star that has nothing else stays unlabelled rather than
     * cluttering the chart with a survey index.
     */
    public function displayName(): ?string
    {
        $parts = array_filter([
            $this->flamsteed,
            $this->bayer,
            $this->bayer_suffix,
            $this->constellation_code,
        ], fn($value) => $value !== null && $value !== '');

        if ($parts !== []) {
            return implode(' ', $parts);
        }

        $name = trim((string) $this->name);

        return ($name === '' || $this->isCatalogueDesignation($name)) ? null : $name;
    }

    private function isCatalogueDesignation(string $name): bool
    {
        foreach (self::CATALOGUE_PREFIXES as $prefix) {
            if (stripos($name, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}