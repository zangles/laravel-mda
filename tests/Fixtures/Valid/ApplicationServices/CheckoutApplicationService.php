<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\ApplicationServices;

use Zangles\Mda\Tests\Fixtures\Valid\UseCases\CheckoutProcess;

class CheckoutApplicationService
{
    public function __construct(private readonly CheckoutProcess $checkout) {}
}
