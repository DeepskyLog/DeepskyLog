<?php

namespace App\Services\Import;

/**
 * Holds the aggregated result of an observation import run.
 */
class ImportResult
{
    public int $imported = 0;

    public int $skipped = 0;

    public int $errors = 0;

    /** @var array<int, array{type: string, message: string, context: string}> */
    public array $log = [];

    /** @var array<string, int> name => location_id of newly created locations */
    public array $newLocations = [];

    /** @var array<string, int> name => instrument_id of newly created instruments */
    public array $newInstruments = [];

    /** @var array<string, int> name => eyepiece_id of newly created eyepieces */
    public array $newEyepieces = [];

    /** @var array<string, int> name => filter_id of newly created filters */
    public array $newFilters = [];

    /** @var array<string, int> name => lens_id of newly created lenses */
    public array $newLenses = [];

    /**
     * Brief summaries of successfully imported observations.
     * Each entry: ['row' => int, 'object' => string, 'date' => string]
     *
     * @var array<int, array{row: int, object: string, date: string}>
     */
    public array $importedObservations = [];

    public function addInfo(string $message, string $context = '', int $row = 0): void
    {
        $this->log[] = ['type' => 'info', 'message' => $message, 'context' => $context, 'row' => $row];
    }

    public function addWarning(string $message, string $context = '', int $row = 0): void
    {
        $this->log[] = ['type' => 'warning', 'message' => $message, 'context' => $context, 'row' => $row];
    }

    public function addError(string $message, string $context = '', int $row = 0): void
    {
        $this->log[] = ['type' => 'error', 'message' => $message, 'context' => $context, 'row' => $row];
        $this->errors++;
    }

    public function addSuccess(string $message, string $context = '', int $row = 0): void
    {
        $this->log[] = ['type' => 'success', 'message' => $message, 'context' => $context, 'row' => $row];
    }

    public function hasErrors(): bool
    {
        return $this->errors > 0;
    }
}
