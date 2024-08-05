<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\Observer;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Observer\AddNotification;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers AddNotification::class
 * @method ObserverInterface instantiateTestObject(?array $arguments = null)
 * @method ObserverInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class AddNotificationTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var SearchCriteriaBuilder|null
     */
    private ?SearchCriteriaBuilder $searchCriteriaBuilder = null;
    /**
     * @var NotificationRepositoryInterface|null
     */
    private ?NotificationRepositoryInterface $notificationRepository = null;
    /**
     * @var EventManagerInterface|null
     */
    private ?EventManagerInterface $eventManager = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = AddNotification::class;
        $this->interfaceFqcn = ObserverInterface::class;

        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->notificationRepository = $this->objectManager->get(NotificationRepositoryInterface::class);
        $this->eventManager = $this->objectManager->get(EventManagerInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDispatchEvent(): void
    {
        $type = 'phpunit_test_addNotification_' . crc32((string)rand());
        $date = date('Y-m-d H:i:s');

        $this->searchCriteriaBuilder->addFilter(
            field: Notification::FIELD_TYPE,
            value: $type,
        );
        $initialResults = $this->notificationRepository->getList(
            searchCriteria: $this->searchCriteriaBuilder->create(),
        );
        $this->assertSame(0, $initialResults->getTotalCount());

        $this->eventManager->dispatch(
            eventName: 'klevu_notifications_addNotification',
            data: [
                'notification_data' => [
                    'type' => $type,
                    'acl_resource' => 'Klevu_Notifications::notifications',
                    'severity' => MessageInterface::SEVERITY_MAJOR,
                    'status' => Notification::STATUS_SUCCESS,
                    'message' => 'PHPUnit DispatchEvent Test',
                    'details' => 'This is a test event',
                    'date' => $date,
                    'muted' => true,
                    'delete_after_view' => true,
                    'foo' => 'bar',
                ],
            ],
        );
        $this->eventManager->dispatch(
            eventName: 'klevu_notifications_addNotification',
            data: [
                'notification_data' => [
                    'type' => $type,
                    'acl_resource' => 'Klevu_Notifications::notifications',
                    'severity' => MessageInterface::SEVERITY_MINOR,
                    'status' => Notification::STATUS_PROGRESS,
                    'message' => 'PHPUnit DispatchEvent Test 2',
                    'details' => 'This is another test event',
                    'date' => $date,
                    'muted' => false,
                    'delete_after_view' => false,
                ],
            ],
        );

        $this->notificationRepository->clearCache();
        $this->searchCriteriaBuilder->addFilter(
            field: Notification::FIELD_TYPE,
            value: $type,
        );
        $postDispatchResults = $this->notificationRepository->getList(
            searchCriteria: $this->searchCriteriaBuilder->create(),
        );

        $this->assertSame(2, $postDispatchResults->getTotalCount());
        /** @var NotificationInterface[] $items */
        $items = $postDispatchResults->getItems();

        $notification = array_shift($items);
        $this->assertNotNull($notification->getId());
        $this->assertSame($type, $notification->getType());
        $this->assertSame('Klevu_Notifications::notifications', $notification->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_MAJOR, $notification->getSeverity());
        $this->assertSame(Notification::STATUS_SUCCESS, $notification->getStatus());
        $this->assertSame('PHPUnit DispatchEvent Test', $notification->getMessage());
        $this->assertSame('This is a test event', $notification->getDetails());
        $this->assertSame($date, $notification->getDate());
        $this->assertSame(true, $notification->isMuted());
        $this->assertSame(true, $notification->isDeleteAfterView());

        $notification = array_shift($items);
        $this->assertNotNull($notification->getId());
        $this->assertSame($type, $notification->getType());
        $this->assertSame('Klevu_Notifications::notifications', $notification->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_MINOR, $notification->getSeverity());
        $this->assertSame(Notification::STATUS_PROGRESS, $notification->getStatus());
        $this->assertSame('PHPUnit DispatchEvent Test 2', $notification->getMessage());
        $this->assertSame('This is another test event', $notification->getDetails());
        $this->assertSame($date, $notification->getDate());
        $this->assertSame(false, $notification->isMuted());
        $this->assertSame(false, $notification->isDeleteAfterView());
    }
}
