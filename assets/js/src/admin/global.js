/**
 * Admin Global Scripts
 *
 * We have a separate file here because this one is loaded on non-BDB pages and we want
 * to avoid loading *all* our plugin JS on such "public" pages like that.
 *
 * @package book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license GPL2+
 */

import { BDB_Post_Metabox } from "./modules/post-metabox";

( function ( $ ) {

	BDB_Post_Metabox.init();

} )( jQuery );