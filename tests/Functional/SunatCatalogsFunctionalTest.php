<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Functional;

use CarlosChininin\FacturaxSunat\SunatCatalogs;
use PHPUnit\Framework\TestCase;

final class SunatCatalogsFunctionalTest extends TestCase
{
    private SunatCatalogs $catalogs;

    protected function setUp(): void
    {
        $this->catalogs = new SunatCatalogs();
    }

    public function testLoadsOfficialSourceMetadata(): void
    {
        $source = $this->catalogs->source();

        self::assertArrayHasKey('title', $source);
        self::assertSame('Anexo N.° 8 – Catálogo de códigos', $source['title']);
    }

    public function testCatalog00ExtendedDocumentTypesIsAvailable(): void
    {
        $catalog00 = $this->catalogs->catalog('00');

        self::assertNotNull($catalog00);
        self::assertSame('Tipos de documento extendido', $catalog00['title']);
        self::assertCount(77, $catalog00['items']);
        self::assertSame('SALIDA DE CAJA', $catalog00['items']['SC']['description']);
    }

    public function testCanResolveDescriptionsFromExistingCatalogs(): void
    {
        self::assertSame('BOLETA DE VENTA', $this->catalogs->description('01', '03'));
        self::assertSame('DOC. NACIONAL DE IDENTIDAD', $this->catalogs->resolve('06', '001'));
        self::assertSame('DEFAULT', $this->catalogs->resolve('01', '999', 'DEFAULT'));
    }

    public function testDetractionTransportCatalogsAreAvailable(): void
    {
        self::assertSame('Servicio de transporte de bienes realizado por vía terrestre', $this->catalogs->description('54', '027'));
        self::assertSame('Depósito en cuenta', $this->catalogs->description('59', '001'));

        $expectedAttributes = [
            '3006' => 'Numero Registro MTC',
            '3007' => 'Configuración Vehicular',
            '3008' => 'Punto de Origen',
            '3009' => 'Punto Destino',
            '3010' => 'Valor Referencial Preliminar por Viaje',
            '3011' => 'Valor Referencial Preliminar por Vehículo',
            '3012' => 'Valor Referencial Preliminar por TM en Viaje',
            '3013' => 'Carga Efectiva en TM por Vehículo',
            '3014' => 'Carga Útil en TM del Vehículo en Viaje',
        ];

        foreach ($expectedAttributes as $code => $descriptionFragment) {
            self::assertStringContainsString(
                $descriptionFragment,
                $this->catalogs->description('55', $code) ?? '',
            );
        }
    }
}
