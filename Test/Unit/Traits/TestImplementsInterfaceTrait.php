<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Unit\Traits;

trait TestImplementsInterfaceTrait
{
    /**
     * @var class-string
     */
    private string $interfaceFqcn;

    /**
     * Check the implementation's service contract.
     *
     * @return void
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf($this->interfaceFqcn, $this->instantiateTestObject());
    }
}
