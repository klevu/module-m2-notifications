<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\Notifications\Model\NotificationRepository;
use Klevu\Notifications\Model\ResourceModel\Notification as NotificationResource;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers NotificationRepository::class
 * @method NotificationRepositoryInterface instantiateTestObject(?array $arguments = null)
 * @method NotificationRepositoryInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class NotificationRepositoryTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var ResourceConnection|null
     */
    private ?ResourceConnection $resourceConnection = null;
    /**
     * @var SearchCriteriaBuilder|null
     */
    private ?SearchCriteriaBuilder $searchCriteriaBuilder = null;
    /**
     * @var NotificationInterfaceFactory|null
     */
    private ?NotificationInterfaceFactory $notificationInterfaceFactory = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = NotificationRepository::class;
        $this->interfaceFqcn = NotificationRepositoryInterface::class;

        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->notificationInterfaceFactory = $this->objectManager->create(NotificationInterfaceFactory::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $notificationRepository = $this->instantiateTestObject();
        $notificationRepository->clearCache();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetById_NotExists(): void
    {
        $notificationRepository = $this->instantiateTestObject();

        $this->expectException(NoSuchEntityException::class);
        $notificationRepository->getById(999999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetById_Exists(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test',
        ]);

        $notificationRepository = $this->instantiateTestObject();

        $notification = $notificationRepository->getById(999999);
        $this->assertInstanceOf(
            expected: NotificationInterface::class,
            actual: $notification,
        );
        $this->assertSame(
            expected: 999999,
            actual: $notification->getId(),
        );
        $this->assertSame(
            expected: 'phpunit_test',
            actual: $notification->getType(),
        );
        $this->assertSame(
            expected: 'Unit test',
            actual: $notification->getMessage(),
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDelete_NoId(): void
    {
        $notification = $this->notificationInterfaceFactory->create();
        $notification->setType('phpunit_test');
        $notification->setMessage('Unit Test');

        $notificationRepository = $this->instantiateTestObject();

        $notificationRepository->delete($notification);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDelete_IdNotExists(): void
    {
        $notificationRepository = $this->instantiateTestObject();

        try {
            $notificationFixture = $notificationRepository->getById(999999);
        } catch (NoSuchEntityException) {
            $notificationFixture = null;
        }
        $this->assertNull($notificationFixture);

        $notificationRepository->clearCache();

        $notification = $this->notificationInterfaceFactory->create();
        $notification->setId(999999);
        $notification->setType('phpunit_test');
        $notification->setMessage('Unit Test');

        $notificationRepository->delete($notification);

        $this->expectException(NoSuchEntityException::class);
        $notificationRepository->getById(999999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDelete_IdExists(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test',
        ]);

        $notificationRepository = $this->instantiateTestObject();

        $notificationFixture = $notificationRepository->getById(999999);
        $this->assertSame(999999, $notificationFixture->getId());

        $notificationRepository->clearCache();

        $notification = $this->notificationInterfaceFactory->create();
        $notification->setId(999999);
        $notification->setType('phpunit_test_modified');
        $notification->setMessage('Unit Test (Modified)');

        $notificationRepository->delete($notification);

        $this->expectException(NoSuchEntityException::class);
        $notificationRepository->getById(999999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteById_NotExists(): void
    {
        $notificationRepository = $this->instantiateTestObject();

        try {
            $notificationFixture = $notificationRepository->getById(999999);
        } catch (NoSuchEntityException) {
            $notificationFixture = null;
        }
        $this->assertNull($notificationFixture);

        $notificationRepository->clearCache();

        $this->expectException(NoSuchEntityException::class);
        $notificationRepository->deleteById(999999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteById_Exists(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test',
        ]);

        $notificationRepository = $this->instantiateTestObject();

        $notificationFixture = $notificationRepository->getById(999999);
        $this->assertSame(999999, $notificationFixture->getId());

        $notificationRepository->clearCache();

        $notificationRepository->deleteById(999999);

        $this->expectException(NoSuchEntityException::class);
        $notificationRepository->getById(999999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSave_Create(): void
    {
        $notification = $this->notificationInterfaceFactory->create();
        $notification->setType('phpunit_test');
        $notification->setMessage('Unit Test');
        $this->assertNull($notification->getId());

        $notificationRepository = $this->instantiateTestObject();

        $notification = $notificationRepository->save($notification);

        $this->assertNotNull($notification->getId());
        $this->assertSame('phpunit_test', $notification->getType());
        $this->assertSame('', $notification->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_NOTICE, $notification->getSeverity());
        $this->assertSame(Notification::STATUS_WARNING, $notification->getStatus());
        $this->assertSame('Unit Test', $notification->getMessage());
        $this->assertSame('', $notification->getDetails());
        $this->assertNotEmpty($notification->getDate());
        $dateUnixtime = strtotime($notification->getDate());
        $this->assertGreaterThan(time() - 60, $dateUnixtime);
        $this->assertLessThan(time() + 60, $dateUnixtime);
        $this->assertSame(false, $notification->isMuted());
        $this->assertSame(false, $notification->isDeleteAfterView());

        $this->assertSame('Unit Test', $notification->getText());
        $this->assertSame('klevu::phpunit_test', $notification->getIdentity());
        $this->assertSame(true, $notification->isDisplayed());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSave_Update(): void
    {
        $notification = $this->notificationInterfaceFactory->create();
        $notification->setType('phpunit_test');
        $notification->setMessage('Unit Test');
        $this->assertNull($notification->getId());

        $notificationRepository = $this->instantiateTestObject();

        $notificationSaved = $notificationRepository->save($notification);
        $originalId = $notificationSaved->getId();
        $originalDate = $notificationSaved->getDate();

        $notificationSaved->setType('phpunit_test_modified');
        $notificationSaved->setSeverity(MessageInterface::SEVERITY_CRITICAL);

        $notificationSavedTwice = $notificationRepository->save($notificationSaved);

        $this->assertSame($originalId, $notificationSavedTwice->getId());
        $this->assertSame('phpunit_test_modified', $notificationSavedTwice->getType());
        $this->assertSame('', $notificationSavedTwice->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_CRITICAL, $notificationSavedTwice->getSeverity());
        $this->assertSame(Notification::STATUS_WARNING, $notificationSavedTwice->getStatus());
        $this->assertSame('Unit Test', $notificationSavedTwice->getMessage());
        $this->assertSame('', $notificationSavedTwice->getDetails());
        $this->assertSame($originalDate, $notificationSavedTwice->getDate());
        $this->assertSame(false, $notificationSavedTwice->isMuted());
        $this->assertSame(false, $notificationSavedTwice->isDeleteAfterView());

        $this->assertSame('Unit Test', $notificationSavedTwice->getText());
        $this->assertSame('klevu::phpunit_test_modified', $notificationSavedTwice->getIdentity());
        $this->assertSame(true, $notificationSavedTwice->isDisplayed());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetList_NoResults(): void
    {
        $this->searchCriteriaBuilder->addFilter(
            field: 'id',
            value: 0,
            conditionType: 'lt',
        );
        $notificationRepository = $this->instantiateTestObject();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $notificationsResult = $notificationRepository->getList(
            searchCriteria: $searchCriteria,
        );
        $this->assertEquals(0, $notificationsResult->getTotalCount());
        $this->assertEmpty($notificationsResult->getItems());
        $this->assertSame($searchCriteria, $notificationsResult->getSearchCriteria());
    }

    /**
     * @magentoDbIsolation enabled
     * @group wip
     */
    public function testGetList_WithResults(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 1',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_CRITICAL,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000000,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 2',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_MAJOR,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000001,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 3',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_MINOR,
        ]);

        $this->searchCriteriaBuilder->addFilter(
            field: 'id',
            value: 1000000,
            conditionType: 'gteq',
        );
        $this->searchCriteriaBuilder->addFilter(
            field: 'severity',
            value: 3,
            conditionType: 'lt',
        );

        $notificationRepository = $this->instantiateTestObject();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $notificationsResult = $notificationRepository->getList(
            searchCriteria: $searchCriteria,
        );

        $this->assertSame(1, $notificationsResult->getTotalCount());
        $this->assertSame($searchCriteria, $notificationsResult->getSearchCriteria());
        $this->assertCount(1, $notificationsResult->getItems());

        $notification = current($notificationsResult->getItems());
        $this->assertInstanceOf(NotificationInterface::class, $notification);
        $this->assertSame(1000000, $notification->getId());
        $this->assertSame('Unit test 2', $notification->getMessage());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetList_PageExceedsBounds(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 1',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_CRITICAL,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000000,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 2',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_MAJOR,
        ]);
        $this->createNotificationFixture([
            Notification::FIELD_ID => 1000001,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 3',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_MINOR,
        ]);

        $this->searchCriteriaBuilder->addFilter(
            field: 'id',
            value: 999999,
            conditionType: 'gteq',
        );
        $this->searchCriteriaBuilder->setPageSize(1);
        $this->searchCriteriaBuilder->setCurrentPage(100);

        $notificationRepository = $this->instantiateTestObject();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $notificationsResult = $notificationRepository->getList(
            searchCriteria: $searchCriteria,
        );

        $this->assertSame(3, $notificationsResult->getTotalCount());
        $this->assertSame($searchCriteria, $notificationsResult->getSearchCriteria());
        $this->assertCount(0, $notificationsResult->getItems());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testClearCache(): void
    {
        $this->createNotificationFixture([
            Notification::FIELD_ID => 999999,
            Notification::FIELD_TYPE => 'phpunit_test',
            Notification::FIELD_MESSAGE => 'Unit test 1',
            Notification::FIELD_SEVERITY => MessageInterface::SEVERITY_CRITICAL,
        ]);
        $notificationRepository = $this->instantiateTestObject();

        $notification = $notificationRepository->getById(999999);
        $this->assertSame('phpunit_test', $notification->getType());
        $notification->setType('phpunit_test_modified');

        $notificationReloadedFromCache = $notificationRepository->getById(999999);
        // Object saved to cache by reference so changes above will be reflected
        $this->assertSame('phpunit_test_modified', $notificationReloadedFromCache->getType());

        $notificationRepository->clearCache();

        $notificationReloadedFromDb = $notificationRepository->getById(999999);
        $this->assertSame('phpunit_test', $notificationReloadedFromDb->getType());
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
