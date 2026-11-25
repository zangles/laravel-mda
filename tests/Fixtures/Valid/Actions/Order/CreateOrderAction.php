<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Actions\Order;

use Zangles\Mda\Tests\Fixtures\Valid\DTO\Order\CreateOrderDTO;
use Zangles\Mda\Tests\Fixtures\Valid\Models\Order;
use Zangles\Mda\Tests\Fixtures\Valid\Repositories\OrderRepository;

class CreateOrderAction
{
    public function __construct(private readonly OrderRepository $repository) {}

    public function handle(CreateOrderDTO $dto): Order
    {
        return $this->repository->create();
    }
}
