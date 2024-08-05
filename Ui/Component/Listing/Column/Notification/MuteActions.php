<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Notifications\Ui\Component\Listing\Column\Notification;

use Magento\Ui\Component\Listing\Columns\Column;

class MuteActions extends Column
{
    /**
     * @param mixed[] $dataSource
     *
     * @return mixed[]
     */
    public function prepareDataSource(array $dataSource) // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint,Generic.Files.LineLength.TooLong
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items']) || !is_array($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as $index => $item) {
            if (!($item['is_klevu'] ?? false) || !($item['klevu_id'] ?? null)) {
                continue;
            }

            $dataSource['data']['items'][$index][$this->getData('name')]['details'] = [
                'callback' => [
                    [
                        'provider' => 'ns = notification_area, index = columns',
                        'target' => 'mute',
                        'params' => [
                            0 => $item['klevu_id'],
                        ],
                    ],
                ],
                'href' => '#',
                'label' => __('Mute'),
            ];
        }

        return $dataSource;
    }
}
