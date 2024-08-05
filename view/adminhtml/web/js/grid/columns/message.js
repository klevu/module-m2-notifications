/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */
define([
    'Magento_AdminNotification/js/grid/columns/message',
    'underscore'
], function (Message, _) {
    'use strict';

    return Message.extend({
        getFieldClass: function ($row) {
            var result = {};

            var status = this.statusMap[$row.klevu_status] || 'warning';
            if (status) {
                result['message-' + status] = true;
                result = _.extend({}, this.fieldClass, result);
            } else {
               result = this._super($row);
            }

            return result;
        }
    });
});
