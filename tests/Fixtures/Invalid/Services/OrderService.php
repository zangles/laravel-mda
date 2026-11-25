<?php

namespace Zangles\Mda\Tests\Fixtures\Invalid\Services;

use Zangles\Mda\Tests\Fixtures\Invalid\UseCases\CheckoutProcess;

// Violación: un Service que usa un Use Case.
class OrderService
{
    public function __construct(private readonly CheckoutProcess $checkout) {}
}
