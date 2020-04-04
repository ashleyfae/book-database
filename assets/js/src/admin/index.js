/**
 * Admin Scripts
 *
 * @package book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license GPL2+
 */

import { BDB_Book_Layout } from './modules/book-layout.js';
import { BDB_Book_Index_Title } from './modules/book-index-title';
import { BDB_Book_Links } from "./modules/book-links";
import { BDB_Categories } from "./modules/categories";
import { BDB_Datepicker } from "./modules/datepicker";
import { BDB_Delete_Objects } from "./modules/delete-objects";
import { BDB_Editions } from "./modules/editions";
import { BDB_License } from "./modules/license";
import { BDB_Media } from "./modules/media-upload";
import { BDB_Reading_Logs } from "./modules/reading-logs";
import { BDB_Retailers } from "./modules/retailers";
import { BDB_Tags } from './modules/tags';
import { BDB_Book_Taxonomies } from './modules/taxonomies.js';

( function ( $ ) {

	BDB_Book_Layout.init();
	BDB_Book_Index_Title.init();
	BDB_Book_Links.init();
	BDB_Categories.init();
	BDB_Datepicker.init();
	BDB_Delete_Objects.init();
	BDB_Editions.init();
	BDB_License.init();
	BDB_Media.init();
	BDB_Reading_Logs.init();
	BDB_Retailers.init();
	BDB_Tags.init();
	BDB_Book_Taxonomies.init();

} )( jQuery );