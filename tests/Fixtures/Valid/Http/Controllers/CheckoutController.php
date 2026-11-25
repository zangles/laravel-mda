<?php

namespace Zangles\Mda\Tests\Fixtures\Valid\Http\Controllers;

use Zangles\Mda\Tests\Fixtures\Valid\ApplicationServices\CheckoutApplicationService;

class CheckoutController
{
    public function __construct(private readonly CheckoutApplicationService $checkout) {}
}
