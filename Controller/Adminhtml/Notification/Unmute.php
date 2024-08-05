<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Controller\Adminhtml\Notification;

use Klevu\Notifications\Api\NotificationRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Unmute extends Action implements HttpPostActionInterface
{
    /**
     * @var NotificationRepositoryInterface
     */
    private NotificationRepositoryInterface $notificationRepository;

    /**
     * @param Context $context
     * @param NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(
        Context $context,
        NotificationRepositoryInterface $notificationRepository,
    ) {
        parent::__construct($context);

        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $request = $this->getRequest();
        $notificationIds = $request->getParam('id', []);
        if (!is_array($notificationIds)) {
            $notificationIds = [$notificationIds];
        }
        $notificationIds = array_map('intval', $notificationIds);

        $errors = [];
        foreach ($notificationIds as $notificationId) {
            try {
                $notification = $this->notificationRepository->getById($notificationId);
                $notification->setMuted(false);
                $this->notificationRepository->save($notification);
            } catch (NoSuchEntityException) {
                continue;
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
                continue;
            }
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($result instanceof JsonResult) {
            $result->setData([
                'success' => !$errors,
                'errors' => $errors,
            ]);
        }

        return $result;
    }
}
