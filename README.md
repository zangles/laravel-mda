# laravel-mda

Generadores y reglas de arquitectura para **[Model Domain Architecture (MDA)](https://zangles.github.io/MDA/)** en Laravel.

MDA recomienda, no impone. Este paquete tampoco: genera el esqueleto de cada componente en el lugar que le corresponde, con un docblock que explica su responsabilidad, y ofrece reglas de arquitectura opcionales para que romper una convención sea una decisión consciente y no un accidente.

> La teoría está en la [documentación de MDA](https://zangles.github.io/MDA/). Este README solo explica cómo usar el paquete.

## Requisitos

- PHP 8.2+
- Laravel 11 o 12
- Pest 3, solo si vas a usar el preset de arquitectura

## Instalación

```bash
composer require --dev zangles/laravel-mda
```

Es una dependencia de desarrollo: genera código y corre en los tests, nada de esto llega a producción.

Opcionalmente, publicá la configuración:

```bash
php artisan vendor:publish --tag=mda-config
```

## Generadores

### `mda:model`: el mini-módulo de un modelo

```bash
php artisan mda:model Order
```

```
app/Finders/OrderFinder.php
app/Queries/OrderQueries.php
app/Repositories/OrderRepository.php
app/Repositories/OrderRepositoryInterface.php
app/Services/OrderService.php
```

- El modelo se busca de forma recursiva en `app/Models`, porque MDA agrupa los modelos por base de datos: `Order` encuentra `App\Models\Main\Order` solo. Si hay dos modelos con el mismo nombre, indicá cuál: `mda:model Logistic/Order`.
- `--only=finder,service` o `--except=queries` para generar una parte.
- Si se genera una interfaz, el comando muestra la línea de `bind` para que la registres en tu ServiceProvider. No modifica tus providers.

### `mda:action`: una operación que modifica estado

```bash
php artisan mda:action Order UpdateOrderStatus
```

```
app/Actions/Order/UpdateOrderStatusAction.php   ← depende de OrderRepositoryInterface
app/DTO/Order/UpdateOrderStatusDTO.php          ← parámetro de handle()
```

`--no-dto` genera la Action sin DTO.

### Los demás

```bash
php artisan mda:dto Order CreateOrder              # app/DTO/Order/CreateOrderDTO.php
php artisan mda:use-case CheckoutProcess           # app/UseCases/CheckoutProcess.php
php artisan mda:use-case Pagos/ImputarPago         # app/UseCases/Pagos/ImputarPago.php
php artisan mda:application-service Checkout       # app/ApplicationServices/CheckoutApplicationService.php
```

Ningún comando sobrescribe un archivo existente salvo con `--force`.

## Configuración

| Clave | Por defecto | Para qué |
|---|---|---|
| `namespace` / `path` | `App` / `app/` | Raíz donde se genera. |
| `models.namespace` / `models.path` | `App\Models` / `app/Models` | Dónde buscar los modelos. |
| `folders.*` | `Actions`, `DTO`, `Finders`, ... | Nombre de la carpeta de cada componente (por ejemplo `DTOs`). |
| `group_by_model` | `false` | `true` → `Repositories/Order/OrderRepository.php` en vez de `Repositories/OrderRepository.php`. |
| `interfaces.repositories` | `true` | Genera `OrderRepositoryInterface`, y las Actions dependen de ella. |
| `interfaces.services` | `false` | Ídem para los Services. |

## Stubs propios

```bash
php artisan vendor:publish --tag=mda-stubs
```

Copia los stubs a `stubs/mda/`. Si existe un stub ahí, se usa ese en vez del del paquete. Sirve, por ejemplo, para que todas las Actions de tu proyecto extiendan una `BaseAction`.

## Reglas de arquitectura (Pest)

Registrá el preset en `tests/Pest.php`:

```php
\Zangles\Mda\Arch\MdaPreset::register();

// Si tus carpetas tienen otros nombres:
\Zangles\Mda\Arch\MdaPreset::register(folders: ['dtos' => 'DTOs']);
```

Y usalo en un test:

```php
arch()->preset()->mda();
```

Reglas que verifica:

| Componente | No puede usar |
|---|---|
| Repositories | Actions, Finders, Queries, Services, Use Cases, Application Services |
| Finders, Queries | Actions, Repositories, Services, Use Cases, Application Services (ni entre sí) |
| Actions | Queries, Services, Use Cases, Application Services |
| Services | Use Cases, Application Services |
| Use Cases | Application Services |
| DTOs | ningún otro componente |
| Todos | la capa HTTP (`App\Http`) |

### Romper una regla a conciencia

MDA permite romper sus reglas si sabés por qué. El preset no te lo impide: te pide que lo dejes escrito.

```php
arch()->preset()->mda()->ignoring([
    // Recalcula totales leyendo un resumen; mover la lectura al Service duplicaba la consulta.
    App\Actions\Order\RecalcularTotalesAction::class,
]);
```

La excepción queda en el test, con nombre y motivo, en vez de pasar desapercibida en un code review.

### Lo que el preset no verifica (todavía)

- **Que un Service no dependa de Services de otros modelos.** Las reglas de Pest trabajan por namespace, y todos los Services comparten `App\Services`. Requiere reglas por clase; queda pendiente.

## Versionado

Mientras el paquete esté en `0.x`, puede haber cambios incompatibles entre versiones menores. Desde `1.0` sigue [SemVer](https://semver.org/lang/es/).

## Licencia

MIT. Ver [LICENSE](LICENSE).
