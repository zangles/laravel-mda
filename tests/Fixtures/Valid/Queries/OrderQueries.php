<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Queries;

use Zangles\Mda\Tests\Fixtures\Valid\Models\Order;

class OrderQueries
{
    public function summary(): Order
    {
        return new Order;
    }
}
