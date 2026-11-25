<?php

use Zangles\Mda\Arch\MdaPreset;
use Zangles\Mda\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/*
| Expectations
*/

expect()->extend('toBeValidPhp', function () {
    exec('php -l '.escapeshellarg($this->value).' 2>&1', $output, $exitCode);

    expect($exitCode)->toBe(0, implode("\n", $output));

    return $this;
});

/*
| Preset de arquitectura, apuntado a la app de ejemplo de tests/Fixtures/Valid
*/

MdaPreset::register('Zangles\Mda\Tests\Fixtures\Valid', name: 'mdaFixture');
