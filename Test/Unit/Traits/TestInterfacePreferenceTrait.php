<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Unit\Traits;

trait TestInterfacePreferenceTrait
{
    /**
     * Create the service contract's configured implementation.
     *
     * @param mixed[]|null $arguments
     * @return object
     */
    private function instantiateTestObjectFromInterface(?array $arguments = null): object
    {
        return $this->objectManager->create($this->interfaceFqcn, $arguments ?? []);
    }

    /**
     * Check the service contract's dependency injection preference.
     *
     * @return void
     */
    public function testInterfacePreference(): void
    {
        $object = $this->instantiateTestObjectFromInterface();
        $this->assertInstanceOf($this->interfaceFqcn, $object);
        $this->assertInstanceOf($this->implementationFqcn, $object);
    }
}
