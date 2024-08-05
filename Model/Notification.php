<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Model;

use Klevu\Notifications\Api\Data\NotificationInterface;
use Klevu\Notifications\Model\ResourceModel\Notification as NotificationResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Notification\MessageInterface;

class Notification extends AbstractModel implements NotificationInterface
{
    public const FIELD_ID = 'id';
    public const FIELD_TYPE = 'type';
    public const FIELD_ACL_RESOURCE = 'acl_resource';
    public const FIELD_SEVERITY = 'severity';
    public const FIELD_STATUS = 'status';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_DETAILS = 'details';
    public const FIELD_DATE = 'date';
    public const FIELD_MUTED = 'muted';
    public const FIELD_DELETE_AFTER_VIEW = 'delete_after_view';
    // See Magento_AdminNotification::js/grid/columns/message
    public const STATUS_INFO = 0;
    public const STATUS_PROGRESS = 1;
    public const STATUS_SUCCESS = 2;
    public const STATUS_ERROR = 3;
    public const STATUS_WARNING = 4;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            resourceModel: NotificationResource::class,
        );
    }

    /**
     * @return int|null
     */
    public function getId(): ?int // phpcs:ignore SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
    {
        $id = $this->getData(static::FIELD_ID);
        if (!is_int($id)) {
            $id = is_numeric($id)
                ? (int)$id
                : null;
            $this->setId($id);
        }

        return $id;
    }

    /**
     * @param $id
     *
     * @return NotificationInterface
     */
    public function setId($id): NotificationInterface // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint,SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint,Generic.Files.LineLength.TooLong
    {
        return $this->setData(static::FIELD_ID, $id);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        $type = $this->getData(static::FIELD_TYPE);
        if (!is_string($type)) {
            $type = (string)$type;
            $this->setType($type);
        }

        return $type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->setData(static::FIELD_TYPE, $type);
    }

    /**
     * @return string
     */
    public function getAclResource(): string
    {
        $aclResource = $this->getData(static::FIELD_ACL_RESOURCE);
        if (!is_string($aclResource)) {
            $aclResource = (string)$aclResource;
            $this->setAclResource($aclResource);
        }

        return $aclResource;
    }

    /**
     * @param string $aclResource
     *
     * @return void
     */
    public function setAclResource(string $aclResource): void
    {
        $this->setData(static::FIELD_ACL_RESOURCE, $aclResource);
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        $severity = $this->getData(static::FIELD_SEVERITY);
        if (!is_int($severity)) {
            $severity = is_numeric($severity)
                ? (int)$severity
                : MessageInterface::SEVERITY_MINOR;
            $this->setData(static::FIELD_SEVERITY, $severity);
        }

        return $severity;
    }

    /**
     * @param int $severity
     *
     * @return void
     */
    public function setSeverity(int $severity): void
    {
        $this->setData(static::FIELD_SEVERITY, $severity);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        $status = $this->getData(static::FIELD_STATUS);
        if (!is_int($status)) {
            $status = is_numeric($status)
                ? (int)$status
                : static::STATUS_WARNING;
            $this->setData(static::FIELD_STATUS, $status);
        }

        return $status;
    }

    /**
     * @param int $status
     *
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->setData(static::FIELD_STATUS, (int)$status);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        $message = $this->getData(static::FIELD_MESSAGE);
        if (!is_string($message)) {
            $message = (string)$message;
            $this->setMessage($message);
        }

        return $message;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->setData(static::FIELD_MESSAGE, $message);
    }

    /**
     * @return string|null
     */
    public function getDetails(): ?string
    {
        $details = $this->getData(static::FIELD_DETAILS);
        if (!is_string($details) && null !== $details) {
            $details = (string)$details;
            $this->setDetails($details);
        }

        return $details;
    }

    /**
     * @param string|null $details
     *
     * @return void
     */
    public function setDetails(?string $details): void
    {
        $this->setData(static::FIELD_DETAILS, $details);
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        $date = $this->getData(static::FIELD_DATE);
        if (!is_string($date)) {
            $date = (string)$date;
            $this->setData($date);
        }

        return $date;
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public function setDate(string $date): void
    {
        $this->setData(static::FIELD_DATE, $date);
    }

    /**
     * @return bool
     */
    public function isMuted(): bool
    {
        $muted = $this->getData(static::FIELD_MUTED);
        if (!is_bool($muted)) {
            $muted = (bool)$muted;
            $this->setMuted($muted);
        }

        return $muted;
    }

    /**
     * @param bool $muted
     *
     * @return void
     */
    public function setMuted(bool $muted): void
    {
        $this->setData(static::FIELD_MUTED, $muted);
    }

    /**
     * @return bool
     */
    public function isDeleteAfterView(): bool
    {
        $deleteAfterView = $this->getData(static::FIELD_DELETE_AFTER_VIEW);
        if (!is_bool($deleteAfterView)) {
            $deleteAfterView = (bool)$deleteAfterView;
            $this->setDeleteAfterView($deleteAfterView);
        }

        return $deleteAfterView;
    }

    /**
     * @param bool $deleteAfterView
     *
     * @return void
     */
    public function setDeleteAfterView(bool $deleteAfterView): void
    {
        $this->setData(static::FIELD_DELETE_AFTER_VIEW, $deleteAfterView);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        $type = $this->getType();

        return $type
            ? 'klevu::' . $type
            : '';
    }

    /**
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return !$this->isMuted();
    }
}
