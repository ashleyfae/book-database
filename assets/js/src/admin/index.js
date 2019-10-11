/**
 * Admin Scripts
 *
 * @package book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license GPL2+
 */

import { BDB_Book_Layout } from './modules/book-layout.js';
import { BDB_Book_Taxonomies } from './modules/taxonomies.js';

( function ( $ ) {

	BDB_Book_Layout.init();
	BDB_Book_Taxonomies.init();

} )( jQuery );