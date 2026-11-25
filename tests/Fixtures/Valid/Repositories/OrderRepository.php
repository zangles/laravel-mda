<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Repositories;

use Zangles\Mda\Tests\Fixtures\Valid\Models\Order;

class OrderRepository
{
    public function create(): Order
    {
        return new Order;
    }
}
