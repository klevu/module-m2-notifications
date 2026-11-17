<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Unit\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Model\NotificationSearchResults;
use PHPUnit\Framework\TestCase;

class NotificationSearchResultsTest extends TestCase
{
    /**
     * @return void
     */
    public function testMissingItemsReturnAnEmptyArray(): void
    {
        $searchResults = new NotificationSearchResults();

        $this->assertSame([], $searchResults->getItems());
    }

    /**
     * @return void
     */
    public function testNonArrayItemsReturnAnEmptyArray(): void
    {
        $searchResults = new NotificationSearchResults(['items' => 'invalid']);

        $this->assertSame([], $searchResults->getItems());
    }

    /**
     * @return void
     */
    public function testSetItemsPreservesNotificationsAndKeys(): void
    {
        $searchResults = new NotificationSearchResults();
        $items = [42 => $this->createStub(NotificationInterface::class)];

        $this->assertSame($searchResults, $searchResults->setItems($items));
        $this->assertSame($items, $searchResults->getItems());
        $this->assertSame($searchResults, $searchResults->setItems([]));
        $this->assertSame([], $searchResults->getItems());
    }

    /**
     * @return void
     */
    public function testInvalidItemIsRejectedWithoutReplacingExistingItems(): void
    {
        $searchResults = new NotificationSearchResults();
        $items = [$this->createStub(NotificationInterface::class)];
        $searchResults->setItems($items);

        try {
            $searchResults->setItems([$items[0], 'invalid' => new \stdClass()]);
            $this->fail('Expected an invalid notification to be rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame(
                sprintf(
                    'Argument "items" must contain instances of "%s", "stdClass" received for item invalid.',
                    NotificationInterface::class,
                ),
                $exception->getMessage(),
            );
        }

        $this->assertSame($items, $searchResults->getItems());
    }
}
