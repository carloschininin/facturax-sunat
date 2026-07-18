<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Functional;

use CarlosChininin\FacturaxSunat\SunatCatalogs;
use CarlosChininin\FacturaxSunat\SunatProductCodeGate;
use PHPUnit\Framework\TestCase;

/**
 * Verifica el andamiaje real de los anexos 25.1, 25.2 y 25.3 sobre resources/catalogs.json.
 */
final class SunatCatalog25AnnexFunctionalTest extends TestCase
{
    private SunatCatalogs $catalogs;

    protected function setUp(): void
    {
        $this->catalogs = new SunatCatalogs();
    }

    public function testCatalog25KeepsItsCurrentPublicBehavior(): void
    {
        $catalog = $this->catalogs->catalog(25);

        self::assertNotNull($catalog);
        self::assertSame('25', $catalog['number']);
        self::assertSame('Código Producto SUNAT', $catalog['title']);
        self::assertFalse($catalog['embedded']);
        self::assertSame([], $catalog['items']);
        self::assertSame([], $this->catalogs->items('producto_sunat'));
        self::assertSame('25', $this->catalogs->catalogNumber('producto_sunat'));
        self::assertNull($this->catalogs->description('producto_sunat', '10101500'));
    }

    public function testAnnexScaffoldIsDeclaredForTheThreeAnnexes(): void
    {
        $annexes = $this->catalogs->annexes(25);

        self::assertSame(['25.1', '25.2', '25.3'], array_keys($annexes));

        foreach ($annexes as $number => $annex) {
            self::assertSame($number, $annex['number']);
            self::assertArrayHasKey('title', $annex);
            self::assertArrayHasKey('scope', $annex);
            self::assertFalse($annex['published']);
            self::assertNull($annex['resolution']);
            self::assertSame([], $annex['items']);
        }
    }

    public function testAllAnnexesAreUnpopulatedToday(): void
    {
        foreach (['25.1', '25.2', '25.3'] as $number) {
            self::assertTrue($this->catalogs->hasAnnex($number));
            self::assertFalse($this->catalogs->isAnnexPopulated($number));
            self::assertSame([], $this->catalogs->annexItems($number));
            self::assertNull($this->catalogs->annexItem($number, '10101500'));
            self::assertNull($this->catalogs->annexDescription($number, '10101500'));
        }
    }

    public function testAnnexAliasesResolveAgainstTheRealData(): void
    {
        self::assertSame('25.1', $this->catalogs->annexNumber('producto_sunat_25_1'));
        self::assertSame('25.2', $this->catalogs->annexNumber('producto_sunat_25_2'));
        self::assertSame('25.3', $this->catalogs->annexNumber('producto_sunat_25_3'));
        self::assertNotNull($this->catalogs->annex('producto_sunat_25_1'));
    }

    public function testGateNeverRejectsWhileAnnexesAreEmpty(): void
    {
        $gate = new SunatProductCodeGate($this->catalogs, true);

        foreach (['25.1', '25.2', '25.3'] as $number) {
            self::assertFalse($gate->shouldValidate($number));
            self::assertTrue($gate->isValid($number, '10101500'));
            self::assertTrue($gate->isValid($number, 'CODIGO-INVENTADO'));
        }
    }
}
