/* global bdbVars, wp */

import {apiRequest} from "../../utils";

document.addEventListener('alpine:init', () => {
    Alpine.data('bdbEditions', (bookId) => ({
        loaded: false,
        editions: [],
        mode: 'view',
        bookId,
        error: null,

        init() {
            this.getEditions();
        },

        getEditions() {
            const editions = this;

            apiRequest('v1/edition', {book_id: editions.bookId, number: 50}, 'GET')
                .then(response => {
                    editions.editions = response;
                    editions.loaded = true;
                })
                .catch(error => {
                    editions.error = error;
                });
        }
    }));
});
