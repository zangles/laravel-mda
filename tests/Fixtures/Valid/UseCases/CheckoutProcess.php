<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\UseCases;

use Zangles\Mda\Tests\Fixtures\Valid\Services\OrderService;

class CheckoutProcess
{
    public function __construct(private readonly OrderService $orders) {}
}
