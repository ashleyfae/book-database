/**
 * Admin Scripts
 *
 * @package book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license GPL2+
 */

import { BDB_Book_Layout } from './modules/book-layout.js';
import { BDB_Book_Index_Title } from './modules/book-index-title';
import { BDB_Categories } from "./modules/categories";
import { BDB_Tags } from './modules/tags';
import { BDB_Book_Taxonomies } from './modules/taxonomies.js';

( function ( $ ) {

	BDB_Book_Layout.init();
	BDB_Book_Index_Title.init();
	BDB_Categories.init();
	BDB_Tags.init();
	BDB_Book_Taxonomies.init();

} )( jQuery );