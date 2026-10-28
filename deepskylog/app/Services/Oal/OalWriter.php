<?php

namespace App\Services\Oal;

use Illuminate\Support\Str;

/**
 * The parts of an OAL 2.1 document the observation exports and the observing
 * list export write alike: the root element, text nodes and the targets.
 */
class OalWriter
{
    public const NS = 'http://groups.google.com/group/openastronomylog';
    public const XSI_NS = 'http://www.w3.org/2001/XMLSchema-instance';
    public const SCHEMA_LOCATION = 'http://groups.google.com/group/openastronomylog oal21.xsd';
    public const TARGET_METADATA_PREFIX = 'DeepskyLog:';

    /**
     * A new document with its oal:observations root element.
     *
     * @return array{\DOMDocument, \DOMElement}
     */
    public static function document(): array
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElementNS(self::NS, 'oal:observations');
        $root->setAttribute('version', '2.1');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::XSI_NS);
        $root->setAttributeNS(self::XSI_NS, 'xsi:schemaLocation', self::SCHEMA_LOCATION);
        $document->appendChild($root);

        return [$document, $root];
    }

    /**
     * Remove the characters XML 1.0 does not allow (control characters other
     * than tab, newline and carriage return, which some legacy observation
     * descriptions contain); the OAL file is otherwise not well-formed.
     */
    public static function xmlSafeText(string $text): string
    {
        $clean = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);

        // preg_replace() returns null on invalid UTF-8: repair the encoding first.
        return $clean ?? (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', mb_convert_encoding($text, 'UTF-8', 'UTF-8'));
    }

    public static function appendTextNode(\DOMDocument $document, \DOMElement $parent, string $name, mixed $value, array $attributes = []): void
    {
        if ($value === null) {
            return;
        }

        $text = trim(self::xmlSafeText((string) $value));
        if ($text === '') {
            return;
        }

        $node = $document->createElement($name);
        foreach ($attributes as $attribute => $attributeValue) {
            $node->setAttribute($attribute, self::xmlSafeText((string) $attributeValue));
        }

        $node->appendChild($document->createTextNode($text));
        $parent->appendChild($node);
    }

    /**
     * One <target> element. The metadata of a deep-sky object holds its
     * columns of `objects`: ra (hours), decl, mag, diam1 and diam2 (arcsec),
     * pa, con and type_code.
     *
     * @param  array{catalog: string, target_table: string, target_type: string, target_key: string, metadata?: array<string, mixed>}  $targetData
     */
    public static function target(\DOMDocument $document, string $id, array $targetData): \DOMElement
    {
        $metadata = $targetData['metadata'] ?? [];

        $targetNode = $document->createElement('target');
        $targetNode->setAttribute('id', $id);
        $targetType = self::targetXsiType($targetData);
        if ($targetType !== null) {
            $targetNode->setAttributeNS(self::XSI_NS, 'xsi:type', $targetType);
        }
        self::appendTextNode($document, $targetNode, 'datasource', 'DeepskyLog');
        self::appendTextNode($document, $targetNode, 'name', $targetData['catalog']);
        self::appendTargetPosition($document, $targetNode, $metadata);
        self::appendTextNode($document, $targetNode, 'constellation', $metadata['con'] ?? null);
        self::appendTextNode($document, $targetNode, 'notes', self::targetMetadata($targetData));
        self::appendDeepSkyTargetDetails($document, $targetNode, $targetType, $metadata);

        return $targetNode;
    }

    /**
     * @param  array{catalog: string, target_table: string, target_type: string, target_key: string, metadata?: array<string, mixed>}  $targetData
     */
    public static function targetXsiType(array $targetData): ?string
    {
        $targetTable = $targetData['target_table'];
        $catalog = strtolower(trim($targetData['catalog']));
        $typeCode = Str::upper(trim((string) ($targetData['metadata']['type_code'] ?? '')));

        return match (true) {
            $targetTable === 'cometobjects' => 'oal:CometTargetType',
            $targetTable === 'asteroids' => 'oal:MinorPlanetTargetType',
            $targetTable === 'moons' => 'oal:MoonTargetType',
            $targetTable === 'planets' && $catalog === 'sun' => 'oal:SunTargetType',
            $targetTable === 'planets' => 'oal:PlanetTargetType',
            $targetTable === 'objects' && in_array($typeCode, ['AA1STAR', 'DS'], true) => 'oal:deepSkyDS',
            $targetTable === 'objects' && $typeCode === 'ASTER' => 'oal:deepSkyAS',
            $targetTable === 'objects' && $typeCode === 'GALCL' => 'oal:deepSkyCG',
            $targetTable === 'objects' && $typeCode === 'DRKNB' => 'oal:deepSkyDN',
            $targetTable === 'objects' && $typeCode === 'GLOCL' => 'oal:deepSkyGC',
            $targetTable === 'objects' && $typeCode === 'PLNNB' => 'oal:deepSkyPN',
            $targetTable === 'objects' && in_array($typeCode, ['BRTNB', 'EMINB', 'GXADN', 'HII', 'SNREM'], true) => 'oal:deepSkyGN',
            $targetTable === 'objects' && $typeCode === 'GALXY' => 'oal:deepSkyGX',
            $targetTable === 'objects' && $typeCode === 'OPNCL' => 'oal:deepSkyOC',
            $targetTable === 'objects' && $typeCode === 'QUASR' => 'oal:deepSkyQS',
            $targetTable === 'objects' && in_array($typeCode, ['STARC'], true) => 'oal:deepSkySC',
            $targetTable === 'objects' => 'oal:deepSkyNA',
            default => null,
        };
    }

    /**
     * @param  array{catalog: string, target_table: string, target_type: string, target_key: string}  $targetData
     */
    public static function targetMetadata(array $targetData): ?string
    {
        if ($targetData['target_table'] !== 'lunar_features') {
            return null;
        }

        return sprintf(
            '%starget_table=%s;target_key=%s;target_type=%s',
            self::TARGET_METADATA_PREFIX,
            $targetData['target_table'],
            $targetData['target_key'],
            $targetData['target_type'] !== '' ? $targetData['target_type'] : 'lunar'
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function appendTargetPosition(\DOMDocument $document, \DOMElement $targetNode, array $metadata): void
    {
        if (!is_numeric($metadata['ra'] ?? null) || !is_numeric($metadata['decl'] ?? null)) {
            return;
        }

        $positionNode = $document->createElement('position');
        // The tables store RA in hours, OAL wants an angle.
        self::appendTextNode($document, $positionNode, 'ra', fmod((float) $metadata['ra'] * 15.0, 360.0), ['unit' => 'deg']);
        self::appendTextNode($document, $positionNode, 'dec', $metadata['decl'], ['unit' => 'deg']);
        $targetNode->appendChild($positionNode);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function appendDeepSkyTargetDetails(\DOMDocument $document, \DOMElement $targetNode, ?string $targetType, array $metadata): void
    {
        if ($targetType === null || !str_starts_with($targetType, 'oal:deepSky')) {
            return;
        }

        // diam1 is the major axis, diam2 the minor one.
        if (is_numeric($metadata['diam2'] ?? null) && (float) $metadata['diam2'] > 0) {
            self::appendTextNode($document, $targetNode, 'smallDiameter', $metadata['diam2'], ['unit' => 'arcsec']);
        }

        if (is_numeric($metadata['diam1'] ?? null) && (float) $metadata['diam1'] > 0) {
            self::appendTextNode($document, $targetNode, 'largeDiameter', $metadata['diam1'], ['unit' => 'arcsec']);
        }

        if (is_numeric($metadata['mag'] ?? null)) {
            self::appendTextNode($document, $targetNode, 'visMag', $metadata['mag']);
        }

        if ($targetType === 'oal:deepSkyGX' && is_numeric($metadata['pa'] ?? null)) {
            self::appendTextNode($document, $targetNode, 'pa', $metadata['pa']);
        }
    }
}
