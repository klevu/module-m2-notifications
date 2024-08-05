<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\ViewModel\System\Config\Information;

use Klevu\Configuration\ViewModel\Config\FieldsetInterface;
use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Model\ResourceModel\Notification as NotificationResource;
use Klevu\Notifications\ViewModel\System\Config\Information\Notifications;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers Notifications::class
 * @method Notifications instantiateTestObject(?array $arguments = null)
 * @method FieldsetInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class NotificationsTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var ResourceConnection|null
     */
    private ?ResourceConnection $resourceConnection = null;
    /**
     * @var NotificationInterfaceFactory|null
     */
    private ?NotificationInterfaceFactory $notificationFactory = null;
    /**
     * @var UrlInterface|null
     */
    private ?UrlInterface $urlBuilder = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = Notifications::class;
        $this->interfaceFqcn = FieldsetInterface::class;

        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->notificationFactory = $this->objectManager->get(NotificationInterfaceFactory::class);
        $this->urlBuilder = $this->objectManager->get(UrlInterface::class);
    }

    public function testGetEscaper(): void
    {
        $viewModel = $this->instantiateTestObject();

        $this->assertInstanceOf(
            expected: Escaper::class,
            actual: $viewModel->getEscaper(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testGetMessageClassForNotification(): array
    {
        $objectManager = ObjectManager::getInstance();
        /** @var NotificationInterfaceFactory $notificationFactory */
        $notificationFactory = $objectManager->get(NotificationInterfaceFactory::class); // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor.AutogeneratedClassNotInConstructor, Generic.Files.LineLength.TooLong

        return [
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => Notification::STATUS_INFO,
                    ],
                ]),
                'message-info',
            ],
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => Notification::STATUS_PROGRESS,
                    ],
                ]),
                'message-progress',
            ],
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => Notification::STATUS_SUCCESS,
                    ],
                ]),
                'message-success',
            ],
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => Notification::STATUS_ERROR,
                    ],
                ]),
                'message-error',
            ],
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => Notification::STATUS_WARNING,
                    ],
                ]),
                'message-warning',
            ],
            [
                $notificationFactory->create([
                    'data' => [
                        Notification::FIELD_STATUS => '',
                    ],
                ]),
                'message-warning',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testGetMessageClassForNotification
     */
    public function testGetMessageClassForNotification(
        NotificationInterface $notification,
        string $expectedClass,
    ): void {
        $viewModel = $this->instantiateTestObject();

        $this->assertSame(
            expected: $expectedClass,
            actual: $viewModel->getMessageClassForNotification($notification),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetMuteUrlForNotifications(): void
    {
        $notification = $this->notificationFactory->create([
            'data' => [
                Notification::FIELD_ID => 999999,
            ],
        ]);

        $viewModel = $this->instantiateTestObject();

        $this->assertSame(
            expected: $this->urlBuilder->getUrl(
                routePath: 'klevu_notifications/notification/mute',
                routeParams: [
                    'id' => 999999,
                ],
            ),
            actual: $viewModel->getMuteUrlForNotification($notification),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetUnmuteUrlForNotifications(): void
    {
        $notification = $this->notificationFactory->create([
            'data' => [
                Notification::FIELD_ID => 999999,
            ],
        ]);

        $viewModel = $this->instantiateTestObject();

        $this->assertSame(
            expected: $this->urlBuilder->getUrl(
                routePath: 'klevu_notifications/notification/unmute',
                routeParams: [
                    'id' => 999999,
                ],
            ),
            actual: $viewModel->getUnmuteUrlForNotification($notification),
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetUnreadNotifications(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_MUTED => 0,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000000,
            Notification::FIELD_MUTED => 1,
        ]);

        $viewModel = $this->instantiateTestObject([
            'muted' => false,
        ]);

        $unreadNotifications = $viewModel->getNotifications();

        $this->assertIsArray($unreadNotifications);
        $this->assertGreaterThanOrEqual(1, count($unreadNotifications));

        $foundExpectedNotification = false;
        foreach ($unreadNotifications as $notification) {
            if ($notification->getId() === 999999) {
                $foundExpectedNotification = true;
            }
        }
        $this->assertTrue($foundExpectedNotification);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetMutedNotifications(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_MUTED => 0,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000000,
            Notification::FIELD_MUTED => 1,
        ]);

        $viewModel = $this->instantiateTestObject([
            'muted' => true,
        ]);

        $mutedNotifications = $viewModel->getNotifications();

        $this->assertIsArray($mutedNotifications);
        $this->assertGreaterThanOrEqual(1, count($mutedNotifications));

        $foundExpectedNotification = false;
        foreach ($mutedNotifications as $notification) {
            if ($notification->getId() === 1000000) {
                $foundExpectedNotification = true;
            }
        }
        $this->assertTrue($foundExpectedNotification);
    }

    public function testGetChildBlocks(): void
    {
        $viewModel = $this->instantiateTestObject();

        $this->assertSame([], $viewModel->getChildBlocks());
    }

    public function testGetMessages(): void
    {
        $viewModel = $this->instantiateTestObject();

        $this->assertSame([], $viewModel->getMessages());
    }

    public function testGetStyles(): void
    {
        $viewModel = $this->instantiateTestObject();

        $this->assertSame('', $viewModel->getStyles());
    }

    /**
     * @param mixed[] $data
     *
     * @return void
     */
    private function createNotificationFixture(array $data): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insert(
            table: $this->resourceConnection->getTableName(NotificationResource::TABLE),
            bind: $data,
        );
    }
}
