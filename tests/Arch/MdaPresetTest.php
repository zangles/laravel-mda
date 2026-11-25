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

    // Pest acorta los nombres en el mensaje ("Zangles\Mda\Tests\Fixtures\In…tories"),
    // así que comparo por el final de cada uno.
    expect($violations)->toHaveCount(4, $report)
        ->and($report)
        ->toContain("tories' not to use 'Zangles\\Mda\\Tests\\Fixtures\\In…rvices'")   // Repository → Service
        ->toContain("ctions' not to use 'Zangles\\Mda\\Tests\\Fixtures\\In…ueries'")   // Action → Queries
        ->toContain("rvices' not to use 'Zangles\\Mda\\Tests\\Fixtures\\In…eCases'")   // Service → Use Case
        ->toContain("eCases' not to use 'Zangles\\Mda\\Tests\\Fixtures\\In…d\\Http'"); // Use Case → HTTP
});
