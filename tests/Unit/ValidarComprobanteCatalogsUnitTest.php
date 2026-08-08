<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Unit;

use CarlosChininin\FacturaxSunat\ValidarComprobanteCatalogs;
use PHPUnit\Framework\TestCase;

/**
 * Verifica la resolución de códigos del ANEXO del validador de comprobantes de SUNAT.
 */
final class ValidarComprobanteCatalogsUnitTest extends TestCase
{
    private ValidarComprobanteCatalogs $catalogs;

    protected function setUp(): void
    {
        $this->catalogs = new ValidarComprobanteCatalogs();
    }

    public function testResolvesEstadoCp(): void
    {
        self::assertSame('Aceptado', $this->catalogs->estadoCp('1'));
        self::assertSame('Anulado (comunicado en una baja)', $this->catalogs->estadoCp('2'));
        self::assertNull($this->catalogs->estadoCp('99'));
    }

    public function testEsComprobanteValidoOnlyForAceptadoOrAutorizado(): void
    {
        self::assertTrue($this->catalogs->esComprobanteValido('1'));
        self::assertTrue($this->catalogs->esComprobanteValido('3'));
        self::assertFalse($this->catalogs->esComprobanteValido('0'));
        self::assertFalse($this->catalogs->esComprobanteValido('2'));
        self::assertFalse($this->catalogs->esComprobanteValido('4'));
    }

    public function testResolvesEstadoRuc(): void
    {
        self::assertSame('Activo', $this->catalogs->estadoRuc('00'));
        self::assertSame('Baja definitiva', $this->catalogs->estadoRuc('10'));
        self::assertNull($this->catalogs->estadoRuc('99'));
    }

    public function testResolvesCondDomiRuc(): void
    {
        self::assertSame('Habido', $this->catalogs->condDomiRuc('00'));
        self::assertSame('No hallado', $this->catalogs->condDomiRuc('20'));
        self::assertNull($this->catalogs->condDomiRuc('99'));
    }

    public function testResolvesCodComp(): void
    {
        self::assertSame('Factura', $this->catalogs->codComp('01'));
        self::assertSame('Recibo por honorarios', $this->catalogs->codComp('R1'));
        self::assertNull($this->catalogs->codComp('99'));
        self::assertCount(7, $this->catalogs->tiposComprobante());
    }
}
