<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Raíz de la aplicación
    |--------------------------------------------------------------------------
    |
    | Namespace y carpeta donde se generan los componentes. Con null se usa
    | el namespace de la aplicación (App) y app_path().
    |
    */

    'namespace' => null,

    'path' => null,

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Dónde buscar los modelos. MDA agrupa los modelos por base de datos
    | (Models/Main/Order.php), así que el generador los busca de forma
    | recursiva: `mda:model Order` encuentra App\Models\Main\Order solo.
    |
    */

    'models' => [
        'namespace' => null, // null = {namespace}\Models
        'path' => null,      // null = {path}/Models
    ],

    /*
    |--------------------------------------------------------------------------
    | Carpetas de cada componente
    |--------------------------------------------------------------------------
    |
    | Nombre de la carpeta (y del segmento del namespace) de cada componente.
    | Cambialo si tu proyecto usa otro nombre, por ejemplo 'DTOs' en vez de 'DTO'.
    |
    */

    'folders' => [
        'actions' => 'Actions',
        'dtos' => 'DTO',
        'finders' => 'Finders',
        'queries' => 'Queries',
        'repositories' => 'Repositories',
        'services' => 'Services',
        'use_cases' => 'UseCases',
        'application_services' => 'ApplicationServices',
    ],

    /*
    |--------------------------------------------------------------------------
    | Agrupar por modelo
    |--------------------------------------------------------------------------
    |
    | Actions y DTOs siempre van en una subcarpeta por modelo (Actions/Order/).
    | Finders, Queries, Repositories y Services son un archivo por modelo:
    |
    |   false → Repositories/OrderRepository.php        (como en la documentación)
    |   true  → Repositories/Order/OrderRepository.php  (útil si cada modelo
    |                                                     tiene también su interfaz)
    |
    */

    'group_by_model' => false,

    /*
    |--------------------------------------------------------------------------
    | Interfaces
    |--------------------------------------------------------------------------
    |
    | Genera una interfaz junto a la clase. Las Actions dependen de la interfaz
    | del Repository cuando existe, para poder reemplazarla en los tests.
    | Recordá registrar el binding en un ServiceProvider; el comando te muestra
    | la línea exacta.
    |
    */

    'interfaces' => [
        'repositories' => true,
        'services' => false,
    ],

];
