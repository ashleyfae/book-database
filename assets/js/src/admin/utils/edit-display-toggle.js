/**
 * Toggles the display and edit fields off/on within a wrapper.
 *
 * @param {HTMLElement} wrapper
 * @param {boolean} isEditMode
 */
export default function toggleDisplayAndEditFields(wrapper, isEditMode) {
    const displayValues = wrapper.querySelectorAll('.bdb-table-display-value');
    const editValues = wrapper.querySelectorAll('.bdb-table-edit-value');

    if (displayValues) {
        displayValues.forEach(el => {
            el.style.display = isEditMode ? 'none' : 'block';
        });
    }

    if (editValues) {
        editValues.forEach(el => {
            el.style.display = isEditMode ? 'block' : 'none';
        });
    }
}
