<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface NotificationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Klevu\Notifications\Api\Data\NotificationInterface[]
     */
    public function getItems(); // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint

    /**
     * @param \Klevu\Notifications\Api\Data\NotificationInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
