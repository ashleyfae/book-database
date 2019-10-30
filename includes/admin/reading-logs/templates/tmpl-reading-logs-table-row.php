<?php
/**
 * Reading Logs Template: Table Row
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;
?>
<tr id="bdb-reading-log-{{ data.id }}" data-id="{{ data.id }}">
	<td class="bdb-reading-log-date-started" data-th="<?php esc_attr_e( 'Date Started', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.date_started_formatted ) { #>
				{{ data.date_started_formatted }}
			<# } else { #>
				&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-reading-log-date-started-{{ data.id }}" class="screen-reader-text"><?php _e( 'Date Started', 'book-database' ); ?></label>
			<input type="text" id="bdb-reading-log-date-started-{{ data.id }}" class="bdb-datepicker" value="{{ data.date_started }}">
		</div>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>

	<td class="bdb-reading-log-date-finished" data-th="<?php esc_attr_e( 'Date Finished', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.date_finished_formatted ) { #>
			{{ data.date_finished_formatted }}
			<# } else { #>
			&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-reading-log-date-finished-{{ data.id }}" class="screen-reader-text"><?php _e( 'Date Finished', 'book-database' ); ?></label>
			<input type="text" id="bdb-reading-log-date-finished-{{ data.id }}" class="bdb-datepicker" value="{{ data.date_finished }}">
		</div>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>

	<td class="bdb-reading-log-review-id" data-th="<?php esc_attr_e( 'Review ID', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.review_id && '0' !== data.review_id ) { #>
			<a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&review_id={{ data.review_id }}">{{ data.review_id }} <?php _e( '(Edit)', 'book-database' ); ?></a>
			<# } else { #>
			<a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'add' ) ) ); ?>&book_id={{ data.book_id }}&reading_log_id={{ data.id }}"><?php _e( 'Add Review', 'book-database' ); ?>
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			&mdash;
		</div>
	</td>

	<td class="bdb-reading-log-user-id" data-th="<?php esc_attr_e( 'User ID', 'book-database' ); ?>">
		<div class="bdb-table-display-value">{{ data.user_id }}</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-reading-log-user-id-{{ data.id }}" class="screen-reader-text"><?php _e( 'User ID', 'book-database' ); ?></label>
			<input type="number" id="bdb-reading-log-user-id-{{ data.id }}" value="{{ data.user_id }}">
		</div>
	</td>

	<td class="bdb-reading-log-percentage-complete" data-th="<?php esc_attr_e( '% Complete', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			{{ data.percentage_complete }}%
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-reading-log-percentage-complete-{{ data.id }}" class="screen-reader-text"><?php _e( 'Percentage Complete', 'book-database' ); ?></label>
			<input type="number" id="bdb-reading-log-percentage-complete-{{ data.id }}" value="{{ data.percentage_complete }}">
		</div>
	</td>

	<td class="bdb-reading-log-rating" data-th="<?php esc_attr_e( 'Rating', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.rating ) { #>
			{{ data.rating }}
			<# } else { #>
			&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-reading-log-rating-{{ data.id }}" class="screen-reader-text"><?php _e( 'Rating', 'book-database' ); ?></label>
			<select id="bdb-reading-log-rating-{{ data.id }}">
				<option value=""><?php _e( 'None', 'book-database' ); ?></option>
				<?php foreach ( get_available_ratings() as $rating_value => $rating_label ) : ?>
					<option value="<?php echo esc_attr( $rating_value ); ?>" <# if ( data.rating == '<?php echo esc_attr( $rating_value ); ?>' ) { #> selected="selected" <# } #>><?php echo esc_html( $rating_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</td>

	<td class="bdb-reading-log-actions" data-th="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
		<button type="button" class="button bdb-reading-log-toggle-editable"><?php _e( 'Edit', 'book-database' ); ?></button>
		<button type="button" class="button bdb-remove-reading-log"><?php _e( 'Remove', 'book-database' ); ?></button>
	</td>
</tr>