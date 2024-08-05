<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\Observer;

use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Observer\DeleteNotification;
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
 * @covers DeleteNotification::class
 * @method ObserverInterface instantiateTestObject(?array $arguments = null)
 * @method ObserverInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class DeleteNotificationTest extends TestCase
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
     * @var NotificationInterfaceFactory|null
     */
    private ?NotificationInterfaceFactory $notificationFactory = null;
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

        $this->implementationFqcn = DeleteNotification::class;
        $this->interfaceFqcn = ObserverInterface::class;

        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->notificationFactory = $this->objectManager->get(NotificationInterfaceFactory::class);
        $this->notificationRepository = $this->objectManager->get(NotificationRepositoryInterface::class);
        $this->eventManager = $this->objectManager->get(EventManagerInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDispatchEvent(): void
    {
        $type = 'phpunit_test_deleteNotification_' . crc32((string)rand());
        $date = date('Y-m-d H:i:s');

        $this->notificationRepository->save(
            $this->notificationFactory->create([
                'data' => [
                    Notification::FIELD_TYPE => $type,
                ],
            ]),
        );
        $this->notificationRepository->save(
            $this->notificationFactory->create([
                'data' => [
                    Notification::FIELD_TYPE => $type,
                ],
            ]),
        );

        $this->searchCriteriaBuilder->addFilter(
            field: Notification::FIELD_TYPE,
            value: $type,
        );
        $initialResults = $this->notificationRepository->getList(
            searchCriteria: $this->searchCriteriaBuilder->create(),
        );
        $this->assertSame(2, $initialResults->getTotalCount());

        $this->eventManager->dispatch(
            eventName: 'klevu_notifications_deleteNotification',
            data: [
                'notification_data' => [
                    'type' => $type,
                    // These are all irrelevant to delete
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

        $this->notificationRepository->clearCache();
        $this->searchCriteriaBuilder->addFilter(
            field: Notification::FIELD_TYPE,
            value: $type,
        );
        $postDispatchResults = $this->notificationRepository->getList(
            searchCriteria: $this->searchCriteriaBuilder->create(),
        );

        $this->assertSame(0, $postDispatchResults->getTotalCount());
    }
}
