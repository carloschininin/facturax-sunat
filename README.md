# facturax-sunat

Librería PHP con los catálogos del **Anexo N.° 8 – Catálogo de códigos** de SUNAT (`anexoVII-117-2017.pdf`) para interpretar códigos recibidos desde APIs de comprobantes, resúmenes, notas, guías y documentos relacionados.

## Qué incluye

- Catálogos SUNAT embebidos en `resources/catalogs.json`
- Metadata del anexo original
- API simple para resolver descripciones por número de catálogo y código
- Soporte para catálogos por número (`01`, `54`) o alias (`tipo_documento`, `afectacion_igv`, `detraccion`)
- Catálogos externos del anexo (`02`, `03`, `04`, `13`, `25`) marcados como `embedded = false`, porque el PDF solo referencia estándares externos y no lista sus valores

## Instalación local desde la app Symfony

En `/code/www/2026/facturax/composer.json` agrega un repositorio `path`:

```json
{
  "repositories": [
    {
      "name": "facturax-sunat",
      "type": "path",
      "url": "../facturax-sunat",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

Luego instala el paquete:

```bash
composer require carloschininin/facturax-sunat:*@dev
```

## Uso

```php
use CarlosChininin\FacturaxSunat\SunatCatalogs;

$catalogs = new SunatCatalogs();

$catalogs->description(1, '01');
// FACTURA

$catalogs->description('tipo_documento', '03');
// BOLETA DE VENTA

$catalogs->description('afectacion_igv', '10');
// Gravado - Operación Onerosa

$catalogs->item(5, '1000');
// [
//   'code' => '1000',
//   'description' => 'IGV IMPUESTO GENERAL A LAS VENTAS',
//   'unece_5153' => 'VAT',
//   'unece_5305' => 'S',
// ]

$catalogs->catalog(54);
// devuelve metadata completa del catálogo 54

$catalogs->toJson(1);
// exporta el catálogo 01 en JSON
```

## Estructura

- `src/SunatCatalogs.php`: resolver principal
- `resources/catalogs.json`: data consolidada del anexo

## Fuente

- Documento base: `https://www.sunat.gob.pe/legislacion/superin/2017/anexoVII-117-2017.pdf`
- Título: **Anexo N.° 8 – Catálogo de códigos**

## Nota

Los catálogos 02, 03, 04, 13 y 25 no traen valores dentro del PDF; el anexo remite a estándares externos como ISO 4217, UN/ECE, ISO 3166, UBIGEO INEI y UNSPSC. Por eso en esta librería se exponen como catálogos de referencia sin items embebidos.
