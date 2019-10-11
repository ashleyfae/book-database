/* global $, bdbVars */

/**
 * Spin a button
 *
 *      - Disables the button
 *      - Saves the current button text to `data-text`
 *      - Changes the text to either a WP-Admin spinner or "Please Wait..."
 *
 * @param button
 */
export function spinButton( button ) {

	let newText = bdbVars.is_admin ? '<span class="spinner is-active"></span>' : bdbVars.please_wait;

	button.prop( 'disabled', true ).data( 'text', button.text() ).html( newText );

}

/**
 * Unspin a button
 *
 *      - Enables teh button
 *      - Sets the text to the `data-text` attribute value
 *
 * @param button
 */
export function unspinButton( button ) {
	button.prop( 'disabled', false ).text( button.data( 'text' ) );
}