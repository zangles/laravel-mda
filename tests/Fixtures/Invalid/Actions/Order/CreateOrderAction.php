<?php

namespace Zangles\Mda\Tests\Fixtures\Invalid\Actions\Order;

use Zangles\Mda\Tests\Fixtures\Invalid\Queries\OrderQueries;

// Violación: una Action que usa Queries.
class CreateOrderAction
{
    public function __construct(private readonly OrderQueries $queries) {}
}
