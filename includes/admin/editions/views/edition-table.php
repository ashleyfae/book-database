<?php
/**
 * edition-table.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 *
 * @var \Book_Database\Models\Book $book
 */

use function Book_Database\get_book_formats;
use function Book_Database\get_book_terms;
use function Book_Database\user_can_edit_books;

$sources = get_book_terms([
    'taxonomy' => 'source',
    'orderby'  => 'name',
    'order'    => 'ASC',
    'number'   => 999
]);
?>
    <div id="bdb-book-editions-list" class="postbox">
        <h2><?php esc_html_e('Owned Editions', 'book-database'); ?></h2>

        <div class="inside" x-data="bdbEditions(<?php echo esc_js($book->id); ?>)">
            <table class="wp-list-table widefat fixed posts">
                <thead>
                <tr>
                    <th class="column-primary"><?php esc_html_e('ISBN', 'book-database'); ?></th>
                    <th><?php esc_html_e('Format', 'book-database'); ?></th>
                    <th><?php esc_html_e('Date Acquired', 'book-database'); ?></th>
                    <th><?php esc_html_e('Source', 'book-database'); ?></th>
                    <th><?php esc_html_e('Signed', 'book-database'); ?></th>
                    <th><?php esc_html_e('Actions', 'book-database'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr x-show="! loaded">
                    <td colspan="6">
                        <?php esc_html_e('Loading...', 'book-database'); ?>
                    </td>
                </tr>

                <template x-for="edition in editions" :key="edition.id">
                    <tr>
                        <td
                            class="bdb-edition-isbn column-primary"
                            data-colname="<?php esc_attr_e('ISBN', 'book-database'); ?>"
                        >
                            <div class="bdb-table-display-value" x-text="edition.isbn"></div>

                            <div class="bdb-table-edit-value">
                                <label
                                    :for="'bdb-edition-isbn-' + edition.id"
                                    class="screen-reader-text"
                                ><?php _e('ISBN or ASIN', 'book-database'); ?></label>

                                <input
                                    type="text"
                                    :id="'bdb-edition-isbn-' + edition.id"
                                    x-model="edition.isbn"
                                >
                            </div>

                            <button type="button" class="toggle-row">
                                <span class="screen-reader-text"><?php esc_html_e('Show more details',
                                        'book-database'); ?></span>
                            </button>
                        </td>

                        <td
                            class="bdb-edition-format"
                            data-colname="<?php esc_attr_e('Format', 'book-database'); ?>"
                        >
                            <div
                                class="bdb-table-display-value"
                                x-html="edition.format_name ?? '&ndash;'"
                            ></div>

                            <div class="bdb-table-edit-value">
                                <label
                                    :for="'bdb-edition-format-' + edition.id"
                                    class="screen-reader-text"
                                ><?php esc_html_e('Format', 'book-database'); ?></label>

                                <select
                                    :id="'bdb-edition-format-' + edition.id"
                                    x-model="edition.format"
                                >
                                    <?php foreach (get_book_formats() as $format_key => $format_value) : ?>
                                        <option
                                            value="<?php echo esc_attr($format_key); ?>"
                                        ><?php echo esc_html($format_value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>

                        <td
                            class="bdb-edition-date-acquired"
                            data-colname="<?php esc_attr_e('Date Acquired', 'book-database'); ?>"
                        >
                            <div
                                class="bdb-table-display-value"
                                x-html="edition.date_acquired_formatted ?? '&ndash;'"
                            ></div>

                            <div class="bdb-table-edit-value">
                                <label
                                    :for="'bdb-edition-date-acquired-' + edition.id"
                                    class="screen-reader-text"
                                >
                                    <?php esc_html_e('Date you acquired this edition', 'book-database'); ?>
                                </label>
                                <input
                                    type="text"
                                    :id="'bdb-edition-date-acquired-' + edition.id"
                                    class="bdb-datepicker"
                                    x-model="edition.date_acquired"
                                >
                            </div>
                        </td>

                        <td
                            class="bdb-edition-source"
                            data-colname="<?php esc_attr_e('Source', 'book-database'); ?>"
                        >
                            <div
                                class="bdb-table-display-value"
                                x-html="edition.source_name ?? '&ndash;'"
                            ></div>

                            <div class="bdb-table-edit-value">
                                <label
                                    :for="'bdb-edition-source-' + edition.id"
                                    class="screen-reader-text"
                                ><?php esc_html_e('Source', 'book-database'); ?></label>
                                <select :id="'bdb-edition-source-' + edition.id">
                                    <option value="">&ndash;</option>
                                    <?php foreach ($sources as $source) : ?>
                                        <option
                                            value="<?php echo esc_attr($source->get_id()); ?>"
                                            x-model="edition.source_id"
                                        ><?php echo esc_html($source->get_name()); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>

                        <td
                            class="bdb-edition-signed"
                            data-colname="<?php esc_attr_e('Signed', 'book-database'); ?>"
                        >
                            <div
                                class="bdb-table-display-value"
                                x-html="edition.signed ? '<?php echo esc_js('Yes', 'book-database'); ?>' : '&ndash;'"
                            ></div>

                            <div class="bdb-table-edit-value">
                                <input
                                    type="checkbox"
                                    :id="'bdb-edition-signed-' + edition.id"
                                    value="1"
                                    x-model="edition.signed"
                                >
                                <label :for="'bdb-edition-signed-' + edition.id">
                                    <?php esc_html_e('Yes', 'book-database'); ?>
                                </label>
                            </div>
                        </td>

                        <td
                            class="bdb-edition-actions"
                            data-colname="<?php esc_attr_e('Actions', 'book-database'); ?>"
                        >
                            <?php if (user_can_edit_books()) : ?>
                                <button
                                    type="button"
                                    class="button bdb-edition-toggle-editable bdb-edit-row-with-datepicker"
                                ><?php esc_html_e('Edit', 'book-database'); ?></button>

                                <button
                                    type="button"
                                    class="button bdb-remove-edition"
                                ><?php esc_html_e('Remove', 'book-database'); ?></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                </template>

                <template x-show="loaded && editions.length === 0">
                    <td colspan="6">
                        <?php esc_html_e('No editions added.', 'book-database'); ?>
                    </td>
                </template>
                </tbody>
                <?php if (user_can_edit_books()) : ?>
                    <tfoot>
                    <tr>
                        <td colspan="6">
                            <button type="button" id="bdb-add-edition" class="button">
                                <?php esc_html_e('Add Edition', 'book-database'); ?>
                            </button>
                        </td>
                    </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php
