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
        },

        toggleEditFields(editionId) {
            const button = this.$el;
            const tableRow = document.querySelector('.bdb-editions-list-' + editionId);

            if (! tableRow) {
                return;
            }

            const displayValues = tableRow.querySelectorAll('.bdb-table-display-value');
            const editValues = tableRow.querySelectorAll('.bdb-table-edit-value');

            if (displayValues) {
                displayValues.forEach(el => {
                    el.style.display = 'none';
                });
            }

            if (editValues) {
                editValues.forEach(el => {
                    el.style.display = 'block';
                });
            }
        }
    }));
});
