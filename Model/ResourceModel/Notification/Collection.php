<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Model\ResourceModel\Notification;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Model\ResourceModel\Notification as NotificationResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            model: Notification::class,
            resourceModel: NotificationResource::class,
        );
    }

    /**
     * @return NotificationInterface[]
     */
    public function getItems(): array // phpcs:ignore SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
    {
        /** @var NotificationInterface[] $items */
        $items = parent::getItems();

        return $items;
    }
}
