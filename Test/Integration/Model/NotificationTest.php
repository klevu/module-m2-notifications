<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Test\Integration\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Model\Notification;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers Notification::class
 * @method NotificationInterface instantiateTestObject(?array $arguments = null)
 * @method NotificationInterface instantiateTestObjectFromInterface(?array $arguments = null)
 */
class NotificationTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->implementationFqcn = Notification::class;
        $this->interfaceFqcn = NotificationInterface::class;
    }

    public function testGettersAndSetters(): void
    {
        /** @var Notification $notification */
        $notification = $this->instantiateTestObject();

        $this->assertSame(null, $notification->getId());
        $this->assertSame('', $notification->getType());
        $this->assertSame('', $notification->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_MINOR, $notification->getSeverity());
        $this->assertSame(Notification::STATUS_WARNING, $notification->getStatus());
        $this->assertSame('', $notification->getMessage());
        $this->assertSame(null, $notification->getDetails());
        $this->assertSame('', $notification->getDate());
        $this->assertSame(false, $notification->isMuted());
        $this->assertSame(false, $notification->isDeleteAfterView());

        $this->assertSame('', $notification->getText());
        $this->assertSame('', $notification->getIdentity());
        $this->assertSame(true, $notification->isDisplayed());

        $notification->setId('42');
        $notification->setType('foo');
        $notification->setAclResource('bar');
        $notification->setSeverity(MessageInterface::SEVERITY_CRITICAL);
        $notification->setStatus(Notification::STATUS_SUCCESS);
        $notification->setMessage('Foo bar baz');
        $notification->setDetails('Foo
        Bar: Baz');
        $notification->setDate('2024-01-01 00:00:00');
        $notification->setMuted(true);
        $notification->setDeleteAfterView(true);

        $this->assertSame(42, $notification->getId());
        $this->assertSame('foo', $notification->getType());
        $this->assertSame('bar', $notification->getAclResource());
        $this->assertSame(MessageInterface::SEVERITY_CRITICAL, $notification->getSeverity());
        $this->assertSame(Notification::STATUS_SUCCESS, $notification->getStatus());
        $this->assertSame('Foo bar baz', $notification->getMessage());
        $this->assertSame('Foo
        Bar: Baz', $notification->getDetails());
        $this->assertSame('2024-01-01 00:00:00', $notification->getDate());
        $this->assertSame(true, $notification->isMuted());
        $this->assertSame(true, $notification->isDeleteAfterView());

        $this->assertSame('Foo bar baz', $notification->getText());
        $this->assertSame('klevu::foo', $notification->getIdentity());
        $this->assertSame(false, $notification->isDisplayed());
    }
}
