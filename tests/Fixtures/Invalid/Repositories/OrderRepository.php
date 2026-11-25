<?php

namespace Zangles\Mda\Tests\Fixtures\Invalid\Repositories;

use Zangles\Mda\Tests\Fixtures\Invalid\Services\OrderService;

// Violación: un Repository que usa un Service.
class OrderRepository
{
    public function __construct(private readonly OrderService $service) {}
}
