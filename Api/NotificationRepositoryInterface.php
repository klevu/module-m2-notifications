<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Api;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\Data\NotificationSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface NotificationRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Klevu\Notifications\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): NotificationInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Klevu\Notifications\Api\Data\NotificationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): NotificationSearchResultsInterface;

    /**
     * @param \Klevu\Notifications\Api\Data\NotificationInterface $notification
     *
     * @return \Klevu\Notifications\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(NotificationInterface $notification): NotificationInterface;

    /**
     * @param \Klevu\Notifications\Api\Data\NotificationInterface $notification
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(NotificationInterface $notification): void;

    /**
     * @param int $id
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $id): void;

    /**
     * @return void
     */
    public function clearCache(): void;
}
