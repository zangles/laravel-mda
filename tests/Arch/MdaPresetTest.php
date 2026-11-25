<?php

use Pest\Arch\GroupArchExpectation;
use Pest\Arch\SingleArchExpectation;
use PHPUnit\Framework\AssertionFailedError;
use Zangles\Mda\Arch\MdaPreset;

arch('una app que respeta MDA pasa el preset')
    ->preset()->mdaFixture();

it('detecta cada relación prohibida', function () {
    $violations = [];

    foreach (MdaPreset::expectations('Zangles\Mda\Tests\Fixtures\Invalid') as $rule) {
        // Cada regla es un grupo (un objetivo, varias dependencias prohibidas). Pest lo
        // verifica en el destructor y corta en el primer fallo, así que acá verifico
        // cada dependencia por separado para juntar todos los fallos.
        $singles = (new ReflectionProperty(GroupArchExpectation::class, 'expectations'))->getValue($rule);

        foreach ($singles as $single) {
            /** @var SingleArchExpectation $single */
            try {
                $single->ensureLazyExpectationIsVerified();
            } catch (AssertionFailedError $e) {
                $violations[] = $e->getMessage();
            }
        }
    }

    $report = implode("\n", $violations);

    // Pest acorta los nombres en el mensaje ("Zangles\Mda\Tests\Fixtures\In…tories") y
    // cuánto acorta cambia entre versiones, así que comparo solo por el final de cada uno.
    expect($violations)->toHaveCount(4, $report)
        ->and($report)
        ->toMatch("/tories' not to use '[^']*rvices'/")  // Repository → Service
        ->toMatch("/ctions' not to use '[^']*ueries'/")  // Action → Queries
        ->toMatch("/rvices' not to use '[^']*eCases'/")  // Service → Use Case
        ->toMatch("/eCases' not to use '[^']*Http'/");   // Use Case → HTTP
});
