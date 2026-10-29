<?php

namespace App\Support\Api;

/**
 * Checks a decoded JSON response against a schema of an OpenAPI description
 * (MeApiSpec): the subset it uses ($ref, allOf, oneOf, type, enum, items,
 * properties, required, additionalProperties). Used by
 * tests/Feature/MeApiSpecTest.php and scripts/me_api_spec_check.php.
 */
class OpenApiValidator
{
    /**
     * The problems of a value against a schema of the spec: missing or
     * undocumented fields, and values of another type.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $spec  the whole description, for $ref
     * @return list<string>
     */
    public static function problems(mixed $value, array $schema, array $spec, string $path = '$'): array
    {
        if (isset($schema['$ref'])) {
            return self::problems($value, $spec['components']['schemas'][basename($schema['$ref'])], $spec, $path);
        }
        if (isset($schema['allOf'])) {
            return array_merge(...array_map(fn ($part) => self::problems($value, $part, $spec, $path), $schema['allOf']));
        }
        if (isset($schema['oneOf'])) {
            $all = array_map(fn ($part) => self::problems($value, $part, $spec, $path), $schema['oneOf']);
            $fits = array_filter($all, fn ($problems) => $problems === []);

            return count($fits) === 1 ? [] : ["{$path}: fits ".count($fits).' of the oneOf schemas: '.json_encode($all)];
        }

        $types = (array) ($schema['type'] ?? []);
        $actual = match (true) {
            $value === null => 'null',
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'number',
            is_string($value) => 'string',
            is_array($value) && array_is_list($value) && ($value !== [] || ! in_array('object', $types, true)) => 'array',
            default => 'object',
        };
        if ($types !== [] && ! in_array($actual, $types, true) && ! ($actual === 'integer' && in_array('number', $types, true))) {
            return ["{$path}: ".json_encode($value).' is not '.implode('|', $types)];
        }
        if (isset($schema['enum']) && ! in_array($value, $schema['enum'], true)) {
            return ["{$path}: ".json_encode($value).' is not one of '.json_encode($schema['enum'])];
        }

        $problems = [];
        if ($actual === 'array' && isset($schema['items'])) {
            foreach ($value as $i => $item) {
                $problems = array_merge($problems, self::problems($item, (array) $schema['items'], $spec, "{$path}[{$i}]"));
            }
        }
        if ($actual === 'object' && isset($schema['properties'])) {
            foreach ($schema['required'] ?? [] as $required) {
                if (! array_key_exists($required, $value)) {
                    $problems[] = "{$path}.{$required}: missing";
                }
            }
            foreach ($value as $key => $item) {
                if (isset($schema['properties'][$key])) {
                    $problems = array_merge($problems, self::problems($item, $schema['properties'][$key], $spec, "{$path}.{$key}"));
                } elseif (($schema['additionalProperties'] ?? true) === false) {
                    $problems[] = "{$path}.{$key}: not documented";
                }
            }
        }

        return $problems;
    }
}
