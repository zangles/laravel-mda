<?php

namespace Zangles\Mda\Arch;

use Pest\Arch\Contracts\ArchExpectation;

/**
 * Las relaciones entre componentes de MDA, como reglas de arquitectura de Pest.
 *
 * Registro, en tests/Pest.php:
 *
 *     \Zangles\Mda\Arch\MdaPreset::register();
 *
 * Uso, en cualquier test:
 *
 *     arch()->preset()->mda();
 *
 * MDA recomienda, no impone. Romper una regla es válido si es una decisión
 * consciente, y para eso está ignoring(): la excepción queda escrita en el
 * test, con nombre y apellido, en vez de pasar desapercibida en un code review.
 *
 *     arch()->preset()->mda()->ignoring(App\Actions\Order\RecalcularTotalesAction::class);
 */
final class MdaPreset
{
    public const DEFAULT_NAME = 'mda';

    /** @var array<string, string> */
    public const DEFAULT_FOLDERS = [
        'actions' => 'Actions',
        'dtos' => 'DTO',
        'finders' => 'Finders',
        'queries' => 'Queries',
        'repositories' => 'Repositories',
        'services' => 'Services',
        'use_cases' => 'UseCases',
        'application_services' => 'ApplicationServices',
    ];

    /**
     * @param  string  $namespace  namespace raíz de la aplicación
     * @param  array<string, string>  $folders  mismas claves que config('mda.folders'); solo hace falta pasar las que cambian
     */
    public static function register(string $namespace = 'App', array $folders = [], string $name = self::DEFAULT_NAME): void
    {
        pest()->presets()->custom($name, fn (): array => self::expectations($namespace, $folders));
    }

    /**
     * @param  array<string, string>  $folders
     * @return list<ArchExpectation>
     */
    public static function expectations(string $namespace = 'App', array $folders = []): array
    {
        $ns = self::namespaces($namespace, $folders);

        $all = static fn (string ...$except): array => array_values(array_diff_key($ns, array_flip($except)));

        // Las dependencias van hacia adentro: nada de MDA conoce la capa HTTP.
        // Una regla por namespace: en Pest 3, expect([...])->not->toUse() con un
        // array como objetivo pasa siempre sin revisar nada.
        $http = array_map(
            static fn (string $component): ArchExpectation => expect($component)->not->toUse($namespace.'\\Http'),
            array_values($ns),
        );

        return [
            ...$http,

            // Repository: persistencia básica. No conoce a nadie de MDA.
            expect($ns['repositories'])->not->toUse($all('repositories', 'dtos')),

            // Finder y Queries: solo lectura. No escriben ni orquestan.
            expect($ns['finders'])->not->toUse($all('finders', 'dtos')),
            expect($ns['queries'])->not->toUse($all('queries', 'dtos')),

            // Action: escribe a través del Repository. Nunca usa Queries.
            expect($ns['actions'])->not->toUse([
                $ns['queries'],
                $ns['services'],
                $ns['use_cases'],
                $ns['application_services'],
            ]),

            // Service: reglas de un modelo. No decide flujos de alto nivel.
            expect($ns['services'])->not->toUse([
                $ns['use_cases'],
                $ns['application_services'],
            ]),

            // Use Case: reglas de negocio. No conoce a quien lo orquesta.
            expect($ns['use_cases'])->not->toUse($ns['application_services']),

            // DTO: solo datos.
            expect($ns['dtos'])->not->toUse($all('dtos')),
        ];
    }

    /**
     * @param  array<string, string>  $folders
     * @return array<string, string>
     */
    private static function namespaces(string $namespace, array $folders): array
    {
        $namespace = rtrim($namespace, '\\');

        return array_map(
            fn (string $folder): string => $namespace.'\\'.$folder,
            array_replace(self::DEFAULT_FOLDERS, $folders),
        );
    }
}
