<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Services;

use Zangles\Mda\Tests\Fixtures\Valid\Actions\Order\CreateOrderAction;
use Zangles\Mda\Tests\Fixtures\Valid\DTO\Order\CreateOrderDTO;
use Zangles\Mda\Tests\Fixtures\Valid\Finders\OrderFinder;
use Zangles\Mda\Tests\Fixtures\Valid\Models\Order;
use Zangles\Mda\Tests\Fixtures\Valid\Queries\OrderQueries;

class OrderService
{
    public function __construct(
        private readonly OrderFinder $finder,
        private readonly OrderQueries $queries,
        private readonly CreateOrderAction $create,
    ) {}

    public function create(CreateOrderDTO $dto): Order
    {
        return $this->create->handle($dto);
    }
}
