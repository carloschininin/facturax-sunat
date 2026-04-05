<?php

declare(strict_types=1);

/*
 * This file is part of the PIDIA.
 * (c) Carlos Chininin <cio@pidia.pe>
 */

namespace CarlosChininin\FacturaxSunat;

final class SunatCatalogs
{
    private const array ALIASES = [
        'tipo_documento' => '01',
        'documento' => '01',
        'moneda' => '02',
        'unidad_medida' => '03',
        'pais' => '04',
        'tributo' => '05',
        'documento_identidad' => '06',
        'tipo_documento_identidad' => '06',
        'afectacion_igv' => '07',
        'sistema_isc' => '08',
        'nota_credito' => '09',
        'nota_debito' => '10',
        'resumen_valor_venta' => '11',
        'documento_relacionado_tributario' => '12',
        'ubigeo' => '13',
        'otros_conceptos_tributarios' => '14',
        'leyenda' => '15',
        'tipo_precio' => '16',
        'tipo_operacion' => '17',
        'modalidad_traslado' => '18',
        'estado_item_resumen' => '19',
        'motivo_traslado' => '20',
        'documento_relacionado_guia' => '21',
        'regimen_percepcion' => '22',
        'regimen_retencion' => '23',
        'servicio_publico' => '24',
        'producto_sunat' => '25',
        'tipo_factura' => '51',
        'codigo_leyenda' => '52',
        'cargo_descuento' => '53',
        'detraccion' => '54',
        'identificacion_item' => '55',
        'tipo_servicio_publico' => '56',
        'tipo_servicio_publico_telecom' => '57',
        'tipo_medidor' => '58',
    ];

    private array $data;

    public function __construct(
        private readonly string $dataFile = __DIR__ . '/../resources/catalogs.json',
    ) {
        $this->data = $this->loadData($this->dataFile);
    }

    public function aliases(): array
    {
        return self::ALIASES;
    }

    public function source(): array
    {
        return $this->data['source'] ?? [];
    }

    public function all(): array
    {
        return $this->data['catalogs'] ?? [];
    }

    public function catalog(string|int $catalog): ?array
    {
        $number = $this->catalogNumber($catalog);

        if (null === $number) {
            return null;
        }

        return $this->data['catalogs'][$number] ?? null;
    }

    public function catalogNumber(string|int $catalog): ?string
    {
        if (is_int($catalog)) {
            return sprintf('%02d', $catalog);
        }

        $value = trim($catalog);

        if ('' === $value) {
            return null;
        }

        $alias = strtolower($value);
        if (isset(self::ALIASES[$alias])) {
            return self::ALIASES[$alias];
        }

        if (ctype_digit($value)) {
            return sprintf('%02d', (int) $value);
        }

        return null;
    }

    public function hasCatalog(string|int $catalog): bool
    {
        return null !== $this->catalog($catalog);
    }

    public function items(string|int $catalog): array
    {
        return $this->catalog($catalog)['items'] ?? [];
    }

    public function item(string|int $catalog, string|int $code): ?array
    {
        $items = $this->items($catalog);
        $needle = trim((string) $code);

        if ('' === $needle) {
            return null;
        }

        if (isset($items[$needle])) {
            return $items[$needle];
        }

        $upperNeedle = strtoupper($needle);
        if (isset($items[$upperNeedle])) {
            return $items[$upperNeedle];
        }

        foreach ($items as $itemCode => $item) {
            if ($this->codesMatch((string) $itemCode, $needle)) {
                return $item;
            }
        }

        return null;
    }

    public function description(string|int $catalog, string|int $code): ?string
    {
        return $this->item($catalog, $code)['description'] ?? null;
    }

    public function resolve(string|int $catalog, string|int $code, ?string $default = null): ?string
    {
        return $this->description($catalog, $code) ?? $default;
    }

    public function toJson(string|int|null $catalog = null, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        $payload = null === $catalog
            ? $this->data
            : $this->catalog($catalog);

        if (null === $payload) {
            throw new \RuntimeException('Catálogo SUNAT no encontrado.');
        }

        return json_encode($payload, $flags | JSON_THROW_ON_ERROR);
    }

    private function loadData(string $dataFile): array
    {
        if (!is_file($dataFile)) {
            throw new \RuntimeException(sprintf('No se encontró el archivo de datos SUNAT: %s', $dataFile));
        }

        $content = file_get_contents($dataFile);
        if (false === $content) {
            throw new \RuntimeException(sprintf('No se pudo leer el archivo de datos SUNAT: %s', $dataFile));
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }

    private function codesMatch(string $left, string $right): bool
    {
        if (strtoupper($left) === strtoupper($right)) {
            return true;
        }

        if (!ctype_digit($left) || !ctype_digit($right)) {
            return false;
        }

        return $this->normalizeNumericCode($left) === $this->normalizeNumericCode($right);
    }

    private function normalizeNumericCode(string $code): string
    {
        return ltrim($code, '0') ?: '0';
    }
}
