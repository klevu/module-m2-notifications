/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */
define([
    'Magento_AsynchronousOperations/js/grid/listing',
    'underscore',
    'jquery'
], function (Listing, _, $) {
    'use strict';

    return Listing.extend({
        defaults: {
            klevuAjaxSettings: {
                method: 'POST',
                data: {},
                url: '${ $.muteUrl }'
            }
        },

        mute: function (items) {
            var config = _.extend({}, this.klevuAjaxSettings);

            config.data.id = items;
            this.showLoader();

            $.ajax(config)
                .done(this.reload)
                .fail(this.onError);
        }
    });
})