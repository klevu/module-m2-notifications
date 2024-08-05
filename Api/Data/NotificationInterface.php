<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Api\Data;

use Magento\Framework\Notification\MessageInterface;

interface NotificationInterface extends MessageInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id): NotificationInterface; // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint,SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint,Generic.Files.LineLength.TooLong

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void;

    /**
     * @return string
     */
    public function getAclResource(): string;

    /**
     * @param string $aclResource
     *
     * @return void
     */
    public function setAclResource(string $aclResource): void;

    /**
     * @param int $severity
     *
     * @return void
     */
    public function setSeverity(int $severity): void;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     *
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     *
     * @return void
     */
    public function setMessage(string $message): void;

    /**
     * @return string|null
     */
    public function getDetails(): ?string;

    /**
     * @param string|null $details
     *
     * @return void
     */
    public function setDetails(?string $details): void;

    /**
     * @return string
     */
    public function getDate(): string;

    /**
     * @param string $date
     *
     * @return void
     */
    public function setDate(string $date): void;

    /**
     * @return bool
     */
    public function isMuted(): bool;

    /**
     * @param bool $muted
     *
     * @return void
     */
    public function setMuted(bool $muted): void;

    /**
     * @return bool
     */
    public function isDeleteAfterView(): bool;

    /**
     * @param bool $deleteAfterView
     *
     * @return void
     */
    public function setDeleteAfterView(bool $deleteAfterView): void;
}
