<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Unit\Traits;

use Magento\Framework\Interception\InterceptorInterface;

trait ObjectInstantiationTrait
{
    /**
     * @var class-string
     */
    private string $implementationFqcn;

    private bool $expectPlugins = false;

    /**
     * Create the implementation with optional constructor arguments.
     *
     * @param mixed[]|null $arguments
     * @return object
     */
    private function instantiateTestObject(?array $arguments = null): object
    {
        return $this->objectManager->create($this->implementationFqcn, $arguments ?? []);
    }

    /**
     * Check the implementation and whether it is intercepted.
     *
     * @return void
     */
    public function testCanInstantiate(): void
    {
        $object = $this->instantiateTestObject();
        $this->assertInstanceOf($this->implementationFqcn, $object);
        $this->assertSame($this->expectPlugins, $object instanceof InterceptorInterface);
    }
}
