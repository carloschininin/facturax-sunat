<?php

declare(strict_types=1);

namespace CarlosChininin\FacturaxSunat\Tests\Unit;

use CarlosChininin\FacturaxSunat\SunatCatalogs;
use CarlosChininin\FacturaxSunat\SunatProductCodeGate;
use PHPUnit\Framework\TestCase;

/**
 * Verifica que la validación de códigos de producto quede inerte sin datos oficiales.
 */
final class SunatProductCodeGateTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'sunat-gate-') ?: throw new \RuntimeException('No se pudo crear archivo temporal');

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
                            'published' => true,
                            'items' => [
                                '10101500' => ['code' => '10101500', 'description' => 'ANIMALES VIVOS'],
                            ],
                        ],
                        '25.2' => [
                            'number' => '25.2',
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

    public function testGateIsDisabledByDefault(): void
    {
        $gate = new SunatProductCodeGate(new SunatCatalogs($this->tmpFile));

        self::assertFalse($gate->isEnabled());
        self::assertFalse($gate->shouldValidate('25.1'));
        self::assertTrue($gate->isValid('25.1', 'CUALQUIER-COSA'));
    }

    public function testEnabledGateStaysInertWhileAnnexIsEmpty(): void
    {
        $gate = new SunatProductCodeGate(new SunatCatalogs($this->tmpFile), true);

        self::assertTrue($gate->isEnabled());
        self::assertFalse($gate->shouldValidate('25.2'));
        self::assertTrue($gate->isValid('25.2', 'CUALQUIER-COSA'));
        self::assertSame([], $gate->allowedCodes('25.2'));
    }

    public function testEnabledGateValidatesOnlyPopulatedAnnexes(): void
    {
        $gate = new SunatProductCodeGate(new SunatCatalogs($this->tmpFile), true);

        self::assertTrue($gate->shouldValidate('25.1'));
        self::assertSame(['10101500'], $gate->allowedCodes('25.1'));
        self::assertTrue($gate->isValid('25.1', '10101500'));
        self::assertFalse($gate->isValid('25.1', '010101500'));
        self::assertFalse($gate->isValid('25.1', '99999999'));
    }
}
