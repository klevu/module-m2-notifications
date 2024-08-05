<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Observer;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class DeleteNotification implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;
    /**
     * @var NotificationRepositoryInterface
     */
    private readonly NotificationRepositoryInterface $notificationRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private readonly SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param LoggerInterface $logger
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        NotificationRepositoryInterface $notificationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
        $this->logger = $logger;
        $this->notificationRepository = $notificationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $notificationData = $event->getData('notification_data');
        if (!is_array($notificationData) || empty($notificationData[Notification::FIELD_TYPE])) {
            $this->logger->warning(
                message: sprintf(
                    'Received invalid notification data in observer. Expected array; Received %s',
                    get_debug_type($notificationData),
                ),
                context: [
                    'method' => __METHOD__,
                    'notification_data' => $notificationData,
                ],
            );

            return;
        }

        $notifications = $this->getNotifications($notificationData);
        foreach ($notifications as $notification) {
            try {
                $this->notificationRepository->delete($notification);
            } catch (\Exception $exception) {
                $this->logger->error(
                    message: 'Encountered exception deleting notification: {error}',
                    context: [
                        'exception' => $exception,
                        'error' => $exception->getMessage(),
                        'method' => __METHOD__,
                        'notification_data' => $notificationData,
                        'notification' => $notification,
                    ],
                );
            }
        }
    }

    /**
     * @param mixed[] $notificationData
     *
     * @return NotificationInterface[]
     */
    private function getNotifications(array $notificationData): array
    {
        $notifications = [];
        try {
            $this->searchCriteriaBuilder->addFilter(
                field: Notification::FIELD_TYPE,
                value: $notificationData[Notification::FIELD_TYPE],
                conditionType: 'eq',
            );
            $notificationsResult = $this->notificationRepository->getList(
                searchCriteria: $this->searchCriteriaBuilder->create(),
            );

            $notifications = $notificationsResult->getItems();
        } catch (\Exception $exception) {
            $this->logger->error(
                message: 'Encountered exception retrieving notifications for delete: {error}',
                context: [
                    'exception' => $exception,
                    'error' => $exception->getMessage(),
                    'method' => __METHOD__,
                    'notification_data' => $notificationData,
                ],
            );
        }

        return $notifications;
    }
}
