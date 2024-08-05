<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\Controller\Adminhtml\Notification;

use Klevu\Configuration\Test\Integration\Controller\Adminhtml\GetAdminFrontNameTrait;
use Klevu\Notifications\Api\Data\NotificationInterfaceFactory;
use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Klevu\Notifications\Controller\Adminhtml\Notification\Mute;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractBackendController as AbstractBackendControllerTestCase;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;

/**
 * @covers Mute::class
 * @method Mute instantiateTestObject(?array $arguments = null)
 * @method Mute instantiateTestObjectFromInterface(?array $arguments = null)
 */
class MuteTest extends AbstractBackendControllerTestCase
{
    use GetAdminFrontNameTrait;
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var NotificationRepositoryInterface|null
     */
    private ?NotificationRepositoryInterface $notificationRepository = null;
    /**
     * @var NotificationInterfaceFactory|null
     */
    private ?NotificationInterfaceFactory $notificationFactory = null;
    /**
     * @var StorageInterface|null
     */
    protected ?StorageInterface $storage = null;

    /**
     * @return void
     * @throws AuthenticationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = 'Klevu_Notifications::notifications';

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = Mute::class;
        $this->interfaceFqcn = HttpPostActionInterface::class;
        $this->expectPlugins = true;

        $this->notificationRepository = $this->objectManager->get(NotificationRepositoryInterface::class);
        $this->notificationFactory = $this->objectManager->get(NotificationInterfaceFactory::class);

        $this->storage = $this->objectManager->get(StorageInterface::class);
        if (method_exists($this->storage, 'setData')) {
            $this->storage->setData(TfaSessionInterface::KEY_PASSED, true);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testExecute(): void
    {
        // Can't create with parameters otherwise resource model doesn't save (no changes)
        $notification = $this->notificationFactory->create();
        $notification->setMuted(false);
        $notification = $this->notificationRepository->save($notification);

        $request = $this->getRequest();
        if (method_exists($request, 'setMethod')) {
            $request->setMethod('POST');
        }
        $this->dispatch(
            uri: $this->getAdminFrontName()
                . '/klevu_notifications/notification/mute/id/'
                . $notification->getId(),
        );

        $notificationReloaded = $this->notificationRepository->getById(
            id: $notification->getId(),
        );
        $this->assertTrue($notificationReloaded->isMuted());
    }
}
