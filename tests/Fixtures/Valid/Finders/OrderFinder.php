<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Finders;

use Zangles\Mda\Tests\Fixtures\Valid\Models\Order;

class OrderFinder
{
    public function findById(int $id): ?Order
    {
        return new Order;
    }
}
