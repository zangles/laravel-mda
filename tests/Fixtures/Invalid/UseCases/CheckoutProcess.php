<?php

namespace Zangles\Mda\Tests\Fixtures\Invalid\UseCases;

use Zangles\Mda\Tests\Fixtures\Invalid\Http\Controllers\CheckoutController;

// Violación: un Use Case que conoce la capa HTTP.
class CheckoutProcess
{
    public function __construct(private readonly CheckoutController $controller) {}
}
