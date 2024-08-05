<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Model\ResourceModel;

use Klevu\Notifications\Model\Notification as NotificationModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Notification extends AbstractDb
{
    public const TABLE = 'klevu_notification';
    public const ID_FIELD_NAME = NotificationModel::FIELD_ID;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            mainTable: static::TABLE,
            idFieldName: static::ID_FIELD_NAME,
        );
    }
}
