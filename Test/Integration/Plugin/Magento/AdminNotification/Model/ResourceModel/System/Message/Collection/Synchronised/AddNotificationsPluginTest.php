<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

// phpcs:ignore Generic.Files.LineLength.TooLong,SlevomatCodingStandard.Namespaces.NamespaceSpacing.IncorrectLinesCountBeforeNamespace
namespace Klevu\Notifications\Test\Integration\Plugin\Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronised;

use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Plugin\Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized\AddNotificationsPlugin; // phpcs:ignore Generic.Files.LineLength.TooLong
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\SynchronizedFactory as SynchronizedCollectionFactory; // phpcs:ignore Generic.Files.LineLength.TooLong
use Magento\Framework\Notification\MessageList;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers AddNotificationsPlugin::class
 * @method AddNotificationsPlugin instantiateTestObject(?array $arguments = null)
 */
class AddNotificationsPluginTest extends TestCase
{
    use ObjectInstantiationTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var NotificationInterfaceFactory|null
     */
    private ?NotificationInterfaceFactory $notificationFactory = null;
    /**
     * @var NotificationRepositoryInterface|null
     */
    private ?NotificationRepositoryInterface $notificationRepository = null;
    /**
     * @var SynchronizedCollectionFactory|null
     */
    private ?SynchronizedCollectionFactory $synchronizedCollectionFactory = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = AddNotificationsPlugin::class;

        $this->notificationFactory = $this->objectManager->get(NotificationInterfaceFactory::class);
        $this->notificationRepository = $this->objectManager->get(NotificationRepositoryInterface::class);
        $this->synchronizedCollectionFactory = $this->objectManager->get(SynchronizedCollectionFactory::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testAfterToArray(): void
    {
        $this->notificationRepository->save(
            $this->notificationFactory->create([
                'data' => [
                    Notification::FIELD_TYPE => 'phpunit_test_addNotificationsPlugin_visible',
                    Notification::FIELD_MESSAGE => 'This is a test message. It should show.',
                    Notification::FIELD_MUTED => false,
                ],
            ]),
        );
        $this->notificationRepository->save(
            $this->notificationFactory->create([
                'data' => [
                    Notification::FIELD_TYPE => 'phpunit_test_addNotificationsPlugin_notVisible',
                    Notification::FIELD_MESSAGE => 'This is a test message. It should not show.',
                    Notification::FIELD_MUTED => true,
                ],
            ]),
        );

        $mockMessageList = $this->createMock(MessageList::class);
        $mockMessageList->method('asArray')
            ->willReturn([]);

        $synchronizedCollection = $this->synchronizedCollectionFactory->create([
            'messageList' => $mockMessageList,
        ]);
        $synchronizedItems = $synchronizedCollection->toArray();

        $this->assertGreaterThanOrEqual(1, $synchronizedItems['totalRecords']);
        $this->assertCount(
            expectedCount: 1,
            haystack: array_filter(
                array: $synchronizedItems['items'],
                callback: static fn (array $notification): bool => (
                    'klevu::phpunit_test_addNotificationsPlugin_visible' === $notification['identity']
                ),
            ),
        );
        $this->assertCount(
            expectedCount: 0,
            haystack: array_filter(
                array: $synchronizedItems['items'],
                callback: static fn (array $notification): bool => (
                    'klevu::phpunit_test_addNotificationsPlugin_notVisible' === $notification['identity']
                ),
            ),
        );
    }
}
