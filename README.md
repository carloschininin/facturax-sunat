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

### Anexos del catálogo 25

El catálogo 25 declara además la estructura de los anexos `25.1`, `25.2` y `25.3` (código producto SUNAT):

| Anexo  | Alcance |
| ------ | ------- |
| `25.1` | Oro, explosivos, insumos químicos fiscalizados, combustibles, maquinaria y equipo de minería y construcción, y servicios asociados |
| `25.2` | Bienes agropecuarios e hidrobiológicos: harina de pescado, minerales no auríferos, piedra, arena, madera, chatarra, carnes, caña de azúcar, páprika, aceite de pescado, leche y maíz |
| `25.3` | Bienes vinculados a percepción o ISC: dióxido de carbono, PET, envases, cerveza, agua y granos |

**Los tres anexos están pendientes de publicación oficial por SUNAT**: no existe resolución de superintendencia a la fecha, de modo que `items` está vacío, `published` es `false` y `resolution` es `null`. La estructura es idéntica a la de `items` de cualquier catálogo, por lo que publicarlos será una carga de datos directa, sin cambios de diseño.

Accesores disponibles: `annexes()`, `annexNumber()`, `annex()`, `hasAnnex()`, `annexItems()`, `annexItem()`, `annexDescription()` e `isAnnexPopulated()`. Los alias registrados son `producto_sunat_25_1`, `producto_sunat_25_2` y `producto_sunat_25_3`.

A diferencia de los catálogos, la búsqueda dentro de un anexo usa comparación **exacta**: un código de producto con ceros a la izquierda es un código distinto y no se normaliza.

`SunatProductCodeGate` encapsula la validación futura de códigos de producto. Está apagada por defecto y, aun habilitándola, solo valida si el anexo tiene códigos cargados; mientras no exista data oficial acepta cualquier valor y nunca bloquea la emisión.

```php
$gate = new SunatProductCodeGate($catalogs, enabled: true);
$gate->shouldValidate('25.1'); // false: el anexo aún no tiene data
$gate->isValid('25.1', '10101500'); // true: sin data, no valida nada
```
