<?php

declare(strict_types=1);

/*
 * This file is part of the PIDIA.
 * (c) Carlos Chininin <cio@pidia.pe>
 */

namespace CarlosChininin\FacturaxSunat;

/**
 * Compuerta de validación de códigos de producto SUNAT (catálogo 25).
 *
 * Doble condición para validar:
 *  1. La bandera debe estar habilitada de forma explícita (por defecto está apagada).
 *  2. El anexo debe tener códigos oficiales cargados.
 *
 * Mientras SUNAT no publique los anexos, el anexo está vacío y la compuerta
 * acepta cualquier código: nunca puede romper la emisión de comprobantes.
 */
final class SunatProductCodeGate
{
    public function __construct(
        private readonly SunatCatalogs $catalogs,
        private readonly bool $enabled = false,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Indica si corresponde validar códigos contra el anexo indicado.
     */
    public function shouldValidate(string|int $annex): bool
    {
        return $this->enabled && $this->catalogs->isAnnexPopulated($annex);
    }

    /**
     * Códigos admitidos por el anexo. Vacío mientras no exista data oficial.
     *
     * @return list<string>
     */
    public function allowedCodes(string|int $annex): array
    {
        return array_map(strval(...), array_keys($this->catalogs->annexItems($annex)));
    }

    /**
     * Valida un código de producto. Si la compuerta no aplica, acepta siempre.
     */
    public function isValid(string|int $annex, string|int $code): bool
    {
        if (!$this->shouldValidate($annex)) {
            return true;
        }

        return null !== $this->catalogs->annexItem($annex, $code);
    }
}
