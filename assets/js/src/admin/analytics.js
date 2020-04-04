/**
 * Analytics
 *
 * @package book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license GPL2+
 */

/* globals jQuery */

import { BDB_Analytics } from "./modules/analytics";
import { BDB_Datepicker } from "./modules/datepicker";

( function ( $ ) {

	BDB_Analytics.init();
	BDB_Datepicker.init();

} )( jQuery );