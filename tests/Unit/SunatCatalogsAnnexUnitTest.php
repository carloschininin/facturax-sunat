<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Unit;

use CarlosChininin\FacturaxSunat\SunatCatalogs;
use PHPUnit\Framework\TestCase;

/**
 * Verifica el andamiaje de anexos del catálogo 25 con datos controlados.
 */
final class SunatCatalogsAnnexUnitTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'sunat-annexes-') ?: throw new \RuntimeException('No se pudo crear archivo temporal');

        $payload = [
            'source' => ['title' => 'Fuente de prueba'],
            'catalogs' => [
                '25' => [
                    'number' => '25',
                    'title' => 'Código Producto SUNAT',
                    'embedded' => false,
                    'items' => [],
                    'annexes' => [
                        '25.1' => [
                            'number' => '25.1',
                            'title' => 'Anexo 25.1',
                            'published' => true,
                            'items' => [
                                '0101' => ['code' => '0101', 'description' => 'ORO'],
                                '10101500' => ['code' => '10101500', 'description' => 'ANIMALES VIVOS'],
                            ],
                        ],
                        '25.2' => [
                            'number' => '25.2',
                            'title' => 'Anexo 25.2',
                            'published' => false,
                            'items' => [],
                        ],
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

    public function testAnnexNumberResolvesAliasesAndPlainNotations(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertSame('25.1', $catalogs->annexNumber('producto_sunat_25_1'));
        self::assertSame('25.2', $catalogs->annexNumber('PRODUCTO_SUNAT_25_2'));
        self::assertSame('25.3', $catalogs->annexNumber('producto_sunat_25_3'));
        self::assertSame('25.1', $catalogs->annexNumber('25.1'));
        self::assertSame('25.1', $catalogs->annexNumber('25_1'));
        self::assertNull($catalogs->annexNumber('no_existe'));
        self::assertNull($catalogs->annexNumber(''));
    }

    public function testAnnexLookupUsesExactCodeComparison(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertSame('ORO', $catalogs->annexDescription('25.1', '0101'));

        // La comparación laxa de catálogos (ltrim de ceros) no debe aplicar a los anexos.
        self::assertNull($catalogs->annexItem('25.1', '101'));
        self::assertNull($catalogs->annexItem('25.1', '00101'));
        self::assertNull($catalogs->annexItem('25.1', '010101500'));
        self::assertNull($catalogs->annexItem('25.1', '0010101500'));
        self::assertSame('ANIMALES VIVOS', $catalogs->annexDescription('25.1', '10101500'));
    }

    public function testAnnexPopulationReflectsRealItems(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertTrue($catalogs->hasAnnex('25.1'));
        self::assertTrue($catalogs->isAnnexPopulated('25.1'));
        self::assertFalse($catalogs->isAnnexPopulated('25.2'));
        self::assertFalse($catalogs->isAnnexPopulated('25.3'));
        self::assertFalse($catalogs->hasAnnex('25.9'));
    }

    public function testAnnexAccessorsAreNullSafeForUnknownAnnexes(): void
    {
        $catalogs = new SunatCatalogs($this->tmpFile);

        self::assertNull($catalogs->annex('25.9'));
        self::assertSame([], $catalogs->annexItems('25.9'));
        self::assertNull($catalogs->annexItem('25.9', '0101'));
        self::assertNull($catalogs->annexDescription('25.9', '0101'));
        self::assertNull($catalogs->annexItem('25.1', ''));
    }
}
