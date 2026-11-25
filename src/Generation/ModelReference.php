<?php

namespace Zangles\Mda\Generation;

final class ModelReference
{
    public function __construct(
        /** Nombre corto del modelo, por ejemplo "Order". */
        public readonly string $name,
        /** Nombre completo, por ejemplo "App\Models\Main\Order". */
        public readonly string $fqcn,
        /** Si el archivo del modelo existe en disco. */
        public readonly bool $exists,
    ) {}
}
