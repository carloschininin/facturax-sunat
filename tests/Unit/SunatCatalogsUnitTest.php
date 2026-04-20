<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Unit;

use CarlosChininin\FacturaxSunat\SunatCatalogs;
use PHPUnit\Framework\TestCase;

final class SunatCatalogsUnitTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'sunat-catalogs-') ?: throw new \RuntimeException('No se pudo crear archivo temporal');

        $payload = [
            'source' => [
                'title' => 'Fuente de prueba',
            ],
            'catalogs' => [
                '01' => [
                    'number' => '01',
                    'title' => 'Tipo de documento',
                    'items' => [
                        '01' => ['code' => '01', 'description' => 'FACTURA'],
                        'A1' => ['code' => 'A1', 'description' => 'ALFA'],
                    ],
                ],
                '06' => [
                    'number' => '06',
                    'title' => 'Tipo de doc. identidad',
                    'items' => [
                        '1' => ['code' => '1', 'description' => 'DNI'],
                    ],
                ],
            ],
        ];

        file_put_contents($this->tmpFile, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testCatalogNumberResolvesAliasAndNumericValues(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertSame('01', $catalogs->catalogNumber('tipo_documento'));
        self::assertSame('06', $catalogs->catalogNumber('06'));
        self::assertSame('07', $catalogs->catalogNumber(7));
        self::assertNull($catalogs->catalogNumber('NO_EXISTE'));
    }

    public function testItemMatchesNumericCodesWithLeadingZeroes(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertSame('DNI', $catalogs->description('06', '001'));
    }

    public function testItemCanResolveCaseInsensitiveAlphanumericCode(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertSame('ALFA', $catalogs->description('01', 'a1'));
    }

    public function testToJsonThrowsWhenCatalogIsNotFound(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Catálogo SUNAT no encontrado.');

        $catalogs->toJson('99');
    }
}
