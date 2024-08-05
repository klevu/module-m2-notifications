<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\ViewModel\System\Config\Information;

use Klevu\Configuration\ViewModel\Config\FieldsetInterface;
use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class Notifications implements FieldsetInterface
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
     * @var Escaper
     */
    private readonly Escaper $escaper;
    /**
     * @var UrlInterface
     */
    private readonly UrlInterface $urlBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private readonly SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var NotificationRepositoryInterface
     */
    private readonly NotificationRepositoryInterface $notificationRepository;
    /**
     * @var bool|null
     */
    private readonly ?bool $muted;

    /**
     * @param LoggerInterface $logger
     * @param AuthorizationInterface $authorization
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param NotificationRepositoryInterface $notificationRepository
     * @param bool|null $muted
     */
    public function __construct(
        LoggerInterface $logger,
        AuthorizationInterface $authorization,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        NotificationRepositoryInterface $notificationRepository,
        ?bool $muted = null,
    ) {
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->notificationRepository = $notificationRepository;
        $this->muted = $muted;
    }

    /**
     * @return Escaper
     */
    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return string
     */
    public function getMessageClassForNotification(NotificationInterface $notification): string
    {
        $return = match ($notification->getStatus()) {
            Notification::STATUS_INFO => 'message-info',
            Notification::STATUS_PROGRESS => 'message-progress',
            Notification::STATUS_SUCCESS => 'message-success',
            Notification::STATUS_ERROR => 'message-error',
            default => 'message-warning',
        };

        if ($notification->isMuted()) {
            $return .= ' muted';
        }

        return $return;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return string
     */
    public function getMuteUrlForNotification(NotificationInterface $notification): string
    {
        return $this->urlBuilder->getUrl(
            routePath: 'klevu_notifications/notification/mute',
            routeParams: [
                'id' => $notification->getId(),
            ],
        );
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return string
     */
    public function getUnmuteUrlForNotification(NotificationInterface $notification): string
    {
        return $this->urlBuilder->getUrl(
            routePath: 'klevu_notifications/notification/unmute',
            routeParams: [
                'id' => $notification->getId(),
            ],
        );
    }

    /**
     * @return NotificationInterface[]
     */
    public function getNotifications(): array
    {
        $notifications = [];
        try {
            if (null !== $this->muted) {
                $this->searchCriteriaBuilder->addFilter(
                    field: Notification::FIELD_MUTED,
                    value: (int)$this->muted,
                    conditionType: 'eq',
                );
            }

            $notificationsResult = $this->notificationRepository->getList(
                searchCriteria: $this->searchCriteriaBuilder->create(),
            );

            $notifications = array_filter(
                array: $notificationsResult->getItems(),
                callback: fn (NotificationInterface $notification): bool => (
                    !$notification->getAclResource()
                    || $this->authorization->isAllowed($notification->getAclResource())
                ),
            );
        } catch (\Exception $exception) {
            $this->logger->error(
                message: 'Encountered exception loading unread notifications: {error}',
                context: [
                    'exception' => $exception,
                    'error' => $exception->getMessage(),
                    'method' => __METHOD__,
                ],
            );
        }

        return $notifications;
    }

    /**
     * @return string[]
     */
    public function getChildBlocks(): array
    {
        return [];
    }

    /**
     * @return Phrase[][]
     */
    public function getMessages(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getStyles(): string
    {
        return '';
    }
}
