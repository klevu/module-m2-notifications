<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\Data\NotificationSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class NotificationSearchResults extends SearchResults implements NotificationSearchResultsInterface
{
    /**
     * @return NotificationInterface[]
     */
    public function getItems(): array
    {
        $items = $this->_get(static::KEY_ITEMS);

        return is_array($items) ? $items : [];
    }

    /**
     * @param NotificationInterface[] $items
     *
     * @return NotificationSearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function setItems(array $items): NotificationSearchResultsInterface
    {
        foreach ($items as $key => $item) {
            if (!($item instanceof NotificationInterface)) {
                throw new \InvalidArgumentException(
                    message: sprintf(
                        'Argument "items" must contain instances of "%s", "%s" received for item %s.',
                        NotificationInterface::class,
                        get_debug_type($item),
                        $key,
                    ),
                );
            }
        }

        return $this->setData(static::KEY_ITEMS, $items);
    }
}
