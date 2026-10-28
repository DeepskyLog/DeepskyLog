<?php

namespace App\Services\Import;

class OalSchemaValidator
{
    public function schemaPath(): string
    {
        return resource_path('schemas/oal21/oal21.xsd');
    }

    /**
     * Validate XML content against the pinned OAL 2.1 XSD bundle.
     *
     * @return array<int, string>
     */
    public function validate(string $xmlContent): array
    {
        $schemaPath = $this->schemaPath();
        if (!is_file($schemaPath)) {
            return ['OAL 2.1 schema file not found at ' . $schemaPath];
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $document = new \DOMDocument();
        $errors = [];

        if (!$document->loadXML($xmlContent, LIBXML_NONET)) {
            foreach (libxml_get_errors() as $error) {
                $errors[] = $this->formatError($error);
            }

            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return array_values(array_unique($errors));
        }

        libxml_clear_errors();

        if (!$document->schemaValidate($schemaPath)) {
            foreach (libxml_get_errors() as $error) {
                $errors[] = $this->formatError($error);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return array_values(array_unique($errors));
    }

    public function assertValid(string $xmlContent): void
    {
        $errors = $this->validate($xmlContent);
        if ($errors !== []) {
            throw new \UnexpectedValueException(implode("\n", $errors));
        }
    }

    private function formatError(\LibXMLError $error): string
    {
        $message = trim($error->message);
        $line = $error->line > 0 ? 'line ' . $error->line . ': ' : '';

        return $line . $message;
    }
}
