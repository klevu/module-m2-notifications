<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

// phpcs:ignore Generic.Files.LineLength.TooLong,SlevomatCodingStandard.Namespaces.NamespaceSpacing.IncorrectLinesCountBeforeNamespace
namespace Klevu\Notifications\Plugin\Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized as SynchronizedCollection;
use Magento\AdminNotification\Model\System\MessageFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\AuthorizationInterface;
use Psr\Log\LoggerInterface;

class AddNotificationsPlugin
{
    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;
    /**
     * @var AuthorizationInterface
     */
    private readonly AuthorizationInterface $authorization;
    /**
     * @var MessageFactory
     */
    private readonly MessageFactory $messageFactory;
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
     * @param AuthorizationInterface $authorization
     * @param MessageFactory $messageFactory
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        AuthorizationInterface $authorization,
        MessageFactory $messageFactory,
        NotificationRepositoryInterface $notificationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->messageFactory = $messageFactory;
        $this->notificationRepository = $notificationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param SynchronizedCollection $subject
     * @param mixed[] $result
     *
     * @return mixed[]
     */
    public function afterToArray(
        SynchronizedCollection $subject, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        array $result,
    ): array {
        $notificationMessageData = [];

        $notifications = $this->getNotifications();
        foreach ($notifications as $notification) {
            if (!$this->canDisplay($notification)) {
                continue;
            }

            $notificationMessage = $this->messageFactory->create(
                data: $this->getMessageData($notification),
            );
            $notificationMessageData[] = $notificationMessage->toArray();

            $this->deleteAfterViewIfApplicable($notification);
        }

        $result['totalRecords'] += count($notificationMessageData);
        $result['items'] = array_merge($notificationMessageData, $result['items']);

        return $result;
    }

    /**
     * @return array<int, NotificationInterface>
     */
    private function getNotifications(): array
    {
        $this->searchCriteriaBuilder->addFilter(
            field: Notification::FIELD_MUTED,
            value: 0,
        );

        $notificationResult = $this->notificationRepository->getList(
            searchCriteria: $this->searchCriteriaBuilder->create(),
        );

        return $notificationResult->getItems();
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return bool
     */
    private function canDisplay(NotificationInterface $notification): bool
    {
        return !$notification->getAclResource()
            || $this->authorization->isAllowed($notification->getAclResource());
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return mixed[][]
     */
    private function getMessageData(NotificationInterface $notification): array
    {
        return [
            'data' => [
                'text' => __($notification->getText()),
                'severity' => $notification->getSeverity(),
                'klevu_status' => $notification->getStatus(),
                'identity' => $notification->getIdentity(),
                'created_at' => $notification->getDate(),
                'is_klevu' => true,
                'klevu_id' => $notification->getId(),
            ],
        ];
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return void
     */
    private function deleteAfterViewIfApplicable(NotificationInterface $notification): void
    {
        if (!$notification->isDeleteAfterView()) {
            return;
        }

        try {
            $this->notificationRepository->delete($notification);
        } catch (\Exception $exception) {
            $this->logger->error(
                message: 'Could not delete notification after view',
                context: [
                    'exception' => $exception,
                    'error' => $exception->getMessage(),
                    'notification' => [
                        'id' => $notification->getId(),
                        'identity' => $notification->getIdentity(),
                        'severity' => $notification->getSeverity(),
                        'message' => $notification->getMessage(),
                    ],
                ],
            );
        }
    }
}
