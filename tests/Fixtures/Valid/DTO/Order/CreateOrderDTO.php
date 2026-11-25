<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\DTO\Order;

class CreateOrderDTO
{
    public function __construct(public readonly int $userId) {}
}
