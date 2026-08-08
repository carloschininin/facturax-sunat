<?php

declare(strict_types=1);

/*
 * This file is part of the PIDIA.
 * (c) Carlos Chininin <cio@pidia.pe>
 */

namespace CarlosChininin\FacturaxSunat;

/**
 * Códigos del ANEXO (Descripción de código) del "Manual de Consulta Integrada de Comprobante
 * de Pago por Servicio Web" de SUNAT — endpoint validarcomprobante. No forman parte del Anexo
 * N.° 8 (catálogos numerados de SunatCatalogs), así que se mantienen aparte.
 */
final class ValidarComprobanteCatalogs
{
    /** Estado del comprobante (estadoCp). */
    private const array ESTADO_CP = [
        '0' => 'No existe (comprobante no informado)',
        '1' => 'Aceptado',
        '2' => 'Anulado (comunicado en una baja)',
        '3' => 'Autorizado (con autorización de imprenta)',
        '4' => 'No autorizado (no autorizado por imprenta)',
    ];

    /** Estados de estadoCp con validez tributaria plena. */
    private const array ESTADO_CP_VALIDOS = ['1', '3'];

    /** Estado del contribuyente (estadoRuc). */
    private const array ESTADO_RUC = [
        '00' => 'Activo',
        '01' => 'Baja provisional',
        '02' => 'Baja prov. por oficio',
        '03' => 'Suspensión temporal',
        '10' => 'Baja definitiva',
        '11' => 'Baja de oficio',
        '22' => 'Inhabilitado - vent. única',
    ];

    /** Condición de domicilio del contribuyente (condDomiRuc). */
    private const array COND_DOMI_RUC = [
        '00' => 'Habido',
        '09' => 'Pendiente',
        '11' => 'Por verificar',
        '12' => 'No habido',
        '20' => 'No hallado',
    ];

    public function estadoCp(string $code): ?string
    {
        return self::ESTADO_CP[$code] ?? null;
    }

    /** @return array<string, string> */
    public function estadosCp(): array
    {
        return self::ESTADO_CP;
    }

    public function esComprobanteValido(string $estadoCp): bool
    {
        return \in_array($estadoCp, self::ESTADO_CP_VALIDOS, true);
    }

    public function estadoRuc(string $code): ?string
    {
        return self::ESTADO_RUC[$code] ?? null;
    }

    /** @return array<string, string> */
    public function estadosRuc(): array
    {
        return self::ESTADO_RUC;
    }

    public function condDomiRuc(string $code): ?string
    {
        return self::COND_DOMI_RUC[$code] ?? null;
    }

    /** @return array<string, string> */
    public function condicionesDomiRuc(): array
    {
        return self::COND_DOMI_RUC;
    }
}
