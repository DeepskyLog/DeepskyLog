<?php

namespace App\Services;

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ObservingListFileImportService
{
    /**
     * The number of objects imported.
     */
    protected int $importedCount = 0;

    /**
     * The number of objects that were skipped (duplicates or not found).
     */
    protected int $skippedCount = 0;

    /**
     * Errors encountered during import.
     */
    protected array $errors = [];

    /**
     * Import objects from a file into an observing list.
     *
     * @param  ObservingList  $list  The observing list to import into
     * @param  UploadedFile  $file  The file to import from
     * @param  User  $user  The user performing the import
     * @return array  Array with keys: success (bool), imported (int), skipped (int), errors (array)
     */
    public function importFromFile(ObservingList $list, UploadedFile $file, User $user): array
    {
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->errors = [];

        try {
            // Determine file type and parse it
            $extension = strtolower($file->getClientOriginalExtension());
            $objectNames = [];

            switch ($extension) {
                case 'txt':
                    $objectNames = $this->parseArgoNavisOrTxt($file);
                    break;
                case 'argo':
                    $objectNames = $this->parseArgoNavisOrTxt($file);
                    break;
                case 'skylist':
                    $objectNames = $this->parseSkySafari($file);
                    break;
                case 'apd':
                    $objectNames = $this->parseAstroPlanner($file);
                    break;
                case 'csv':
                    $objectNames = $this->parseCsv($file);
                    break;
                default:
                    $this->errors[] = __('Unsupported file format: :format', ['format' => $extension]);
                    return [
                        'success' => false,
                        'imported' => 0,
                        'skipped' => 0,
                        'errors' => $this->errors,
                    ];
            }

            // Import the object names
            $this->importObjectNames($list, $objectNames, $user);

            return [
                'success' => true,
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
            ];
        } catch (\Exception $e) {
            Log::error('ObservingListFileImportService error', [
                'list_id' => $list->id,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            $this->errors[] = __('An error occurred while importing the file: :error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
            ];
        }
    }

    /**
     * Parse Argo Navis or .txt format (simple newline-separated list of names).
     * Argo Navis format (.argo): name|ra|dec|type|mag|size;CR ...
     * .txt format (SkyTools): simple newline-separated list
     *
     * @param  UploadedFile  $file
     * @return array  Array of object names
     */
    protected function parseArgoNavisOrTxt(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $objectNames = [];

        foreach ($lines as $line) {
            // Check if it's Argo Navis format (pipe-separated)
            if (strpos($line, '|') !== false) {
                $parts = explode('|', $line);
                $name = trim($parts[0] ?? '');
                // Remove "DSL " prefix if present
                $name = preg_replace('/^DSL\s+/i', '', $name);
            } else {
                // Simple text format
                $name = $line;
            }

            $name = trim($name);
            if (!empty($name)) {
                $objectNames[] = $name;
            }
        }

        return $objectNames;
    }

    /**
     * Parse SkySafari .skylist format.
     * Format: key=value pairs with SkyObject blocks
     *
     * @param  UploadedFile  $file
     * @return array  Array of object names
     */
    protected function parseSkySafari(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $objectNames = [];

        // Remove version line and split by objects
        $content = preg_replace('/SkySafariObservingListVersion=[\d.]+\s*/i', '', $content);
        $blocks = preg_split('/SkyObject\s*=\s*BeginObject|EndObject\s*=\s*SkyObject/i', $content);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            // Extract CatalogNumber
            if (preg_match('/CatalogNumber\s*=\s*(.+?)(?:\n|$)/i', $block, $matches)) {
                $name = trim($matches[1]);
                // Unescape newlines
                $name = str_replace('\\n', "\n", $name);
                $name = trim($name);
                if (!empty($name)) {
                    $objectNames[] = $name;
                }
            }
        }

        return $objectNames;
    }

    /**
     * Parse AstroPlanner .apd format (SQLite database).
     *
     * @param  UploadedFile  $file
     * @return array  Array of object names
     */
    protected function parseAstroPlanner(UploadedFile $file): array
    {
        $objectNames = [];

        try {
            $filePath = $file->getRealPath();

            // Verify the file exists and is readable
            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new \RuntimeException('File does not exist or is not readable');
            }

            $db = new \PDO('sqlite:' . $filePath);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check if Objects table exists
            $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Objects'");
            if (!$tableCheck->fetch()) {
                throw new \RuntimeException('Objects table not found in APD file');
            }

            // Query the Objects table for all names
            $stmt = $db->query('SELECT DISTINCT Name FROM Objects WHERE Name IS NOT NULL AND Name != "" ORDER BY Name');
            if (!$stmt) {
                throw new \RuntimeException('Failed to query Objects table');
            }

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $name = trim($row['Name'] ?? '');
                if (!empty($name)) {
                    $objectNames[] = $name;
                }
            }

            $db = null;
        } catch (\PDOException $e) {
            Log::warning('Failed to parse AstroPlanner APD file - PDO Error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            $this->errors[] = __('Failed to read AstroPlanner file: :error', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::warning('Failed to parse AstroPlanner APD file', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            $this->errors[] = __('Failed to read AstroPlanner file: :error', ['error' => $e->getMessage()]);
        }

        return $objectNames;
    }

    /**
     * Parse CSV format.
     * Expected format: object name in first column, additional columns ignored.
     *
     * @param  UploadedFile  $file
     * @return array  Array of object names
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $objectNames = [];
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(array_map('trim', explode("\n", $content)));

        foreach ($lines as $line) {
            // Parse CSV line
            $values = str_getcsv($line);
            $name = trim($values[0] ?? '');

            if (!empty($name)) {
                $objectNames[] = $name;
            }
        }

        return $objectNames;
    }

    /**
     * Import an array of object names into the observing list.
     *
     * @param  ObservingList  $list
     * @param  array  $objectNames
     * @param  User  $user
     */
    protected function importObjectNames(ObservingList $list, array $objectNames, User $user): void
    {
        foreach ($objectNames as $name) {
            $name = trim((string) $name);

            if (empty($name)) {
                continue;
            }

            try {
                // Try to create or get the item
                $item = ObservingListItem::firstOrCreate(
                    [
                        'observing_list_id' => $list->id,
                        'object_name' => $name,
                    ],
                    [
                        'source_mode' => 'manual',
                        'added_by_user_id' => $user->id,
                    ]
                );

                if ($item->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->skippedCount++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to import object', [
                    'object_name' => $name,
                    'list_id' => $list->id,
                    'error' => $e->getMessage(),
                ]);

                // Continue with next object instead of failing
                $this->skippedCount++;
            }
        }
    }

    /**
     * Get the number of objects imported.
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get the number of objects skipped.
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Get any errors that occurred.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
