<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\Data\NotificationSearchResultsInterface;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\ResourceModel\Notification as NotificationResource;
use Klevu\Notifications\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;
    /**
     * @var NotificationInterfaceFactory
     */
    private readonly NotificationInterfaceFactory $notificationFactory;
    /**
     * @var NotificationResource
     */
    private readonly NotificationResource $notificationResource;
    /**
     * @var NotificationCollectionFactory
     */
    private readonly NotificationCollectionFactory $notificationCollectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private readonly CollectionProcessorInterface $collectionProcessor;
    /**
     * @var NotificationSearchResultsFactory
     */
    private readonly NotificationSearchResultsFactory $searchResultsFactory;
    /**
     * @var array<int, NotificationInterface>
     */
    private array $instances = [];

    /**
     * @param LoggerInterface $logger
     * @param NotificationInterfaceFactory $notificationFactory
     * @param NotificationResource $notificationResource
     * @param NotificationCollectionFactory $notificationCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param NotificationSearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        LoggerInterface $logger,
        NotificationInterfaceFactory $notificationFactory,
        NotificationResource $notificationResource,
        NotificationCollectionFactory $notificationCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        NotificationSearchResultsFactory $searchResultsFactory,
    ) {
        $this->logger = $logger;
        $this->notificationFactory = $notificationFactory;
        $this->notificationResource = $notificationResource;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $id
     *
     * @return NotificationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): NotificationInterface
    {
        if (!isset($this->instances[$id])) {
            $notification = $this->notificationFactory->create();
            if (!($notification instanceof AbstractModel)) {
                throw new \LogicException(sprintf(
                    'Notification model must be instance of %s; received %s from %s',
                    AbstractModel::class,
                    get_debug_type($notification),
                    $this->notificationFactory::class,
                ));
            }

            $this->notificationResource->load(
                object: $notification,
                value: $id,
                field: Notification::FIELD_ID,
            );

            if (!$notification->getId()) {
                throw NoSuchEntityException::singleField(
                    fieldName: Notification::FIELD_ID,
                    fieldValue: $id,
                );
            }

            $this->instances[$id] = $notification;
        }

        return $this->instances[$id];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return NotificationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): NotificationSearchResultsInterface
    {
        $notificationCollection = $this->notificationCollectionFactory->create();
        $this->collectionProcessor->process(
            searchCriteria: $searchCriteria,
            collection: $notificationCollection,
        );

        $lastPageNumber = $notificationCollection->getLastPageNumber();
        $currentPage = $searchCriteria->getCurrentPage();
        $pageSize = $searchCriteria->getPageSize();
        /*
         * If a collection page is requested that does not exist, Magento reverts to get the first page
         * of that collection using this plugin \Magento\Theme\Plugin\Data\Collection::afterGetCurPage.
         * We do not want that behaviour here, return empty result instead.
         * Only do this where currentPage and pageSize are set in searchCriteria
         */
        $invalidPage = $currentPage && $pageSize && $lastPageNumber < $currentPage;

        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount(
            count: $searchCriteria->getPageSize()
                ? $notificationCollection->getSize()
                : count($notificationCollection),
        );
        $searchResults->setItems(
            items: $invalidPage
                ? []
                : $notificationCollection->getItems(), // @phpstan-ignore-line
        );

        return $searchResults;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return NotificationInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function save(NotificationInterface $notification): NotificationInterface
    {
        if (!($notification instanceof AbstractModel)) {
            throw new \InvalidArgumentException(sprintf(
                'Notification model must be instance of %s; received %s in %s',
                AbstractModel::class,
                get_debug_type($notification),
                __METHOD__,
            ));
        }

        if (!$notification->getId()) {
            if (!$notification->getStatus()) {
                $notification->setStatus(Notification::STATUS_WARNING);
            }
        }

        try {
            $this->notificationResource->save($notification);
        } catch (LocalizedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $message = __('Could not save notification record: %1', $exception->getMessage());
            $this->logger->error(
                message: (string)$message,
                context: [
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'notification' => [
                        'id' => $notification->getId(),
                        'type' => $notification->getType(),
                        'message' => $notification->getMessage(),
                    ],
                ],
            );

            throw new CouldNotSaveException(
                phrase: $message,
                cause: $exception,
                code: $exception->getCode(),
            );
        }

        unset(
            $this->instances[$notification->getId()],
        );

        return $this->getById(
            (int)$notification->getId(),
        );
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function delete(NotificationInterface $notification): void
    {
        if (!($notification instanceof AbstractModel)) {
            throw new \InvalidArgumentException(sprintf(
                'Notification model must be instanceof %s: received %s in %s',
                AbstractModel::class,
                get_debug_type($notification),
                __METHOD__,
            ));
        }

        try {
            $this->notificationResource->delete($notification);
            unset(
                $this->instances[$notification->getId()],
            );
        } catch (LocalizedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $message = __('Could not delete notification record: %1', $exception->getMessage());
            $this->logger->error(
                message: (string)$message,
                context: [
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'notification' => [
                        'id' => $notification->getId(),
                        'type' => $notification->getType(),
                        'message' => $notification->getMessage(),
                    ],
                ],
            );

            throw new CouldNotDeleteException(
                phrase: $message,
                cause: $exception,
                code: $exception->getCode(),
            );
        }
    }

    /**
     * @param int $id
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): void
    {
        $this->delete(
            $this->getById($id),
        );
    }

    /**
     * @return void
     */
    public function clearCache(): void
    {
        $this->instances = [];
    }
}
