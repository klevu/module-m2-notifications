<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Observer;

use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AddNotification implements ObserverInterface
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
     * @var NotificationRepositoryInterface
     */
    private readonly NotificationRepositoryInterface $notificationRepository;

    /**
     * @param LoggerInterface $logger
     * @param NotificationInterfaceFactory $notificationFactory
     * @param NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(
        LoggerInterface $logger,
        NotificationInterfaceFactory $notificationFactory,
        NotificationRepositoryInterface $notificationRepository,
    ) {
        $this->logger = $logger;
        $this->notificationFactory = $notificationFactory;
        $this->notificationRepository = $notificationRepository;
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
        if (!is_array($notificationData)) {
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

        $notification = $this->notificationFactory->create([
            'data' => $notificationData,
        ]);
        try {
            $this->notificationRepository->save($notification);
        } catch (\Exception $exception) {
            $this->logger->error(
                message: 'Encountered exception saving notification: {error}',
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
