<?php
/**
 * Date Tests
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use function Book_Database\getParsableDateFormat;

class TestDates extends UnitTestCase
{

    /**
     * @covers ::\Book_Database\getParsableDateFormat()
     */
    public function test_parsable_date_format_returns_valid_site_date()
    {
        update_option('date_format', 'F j, Y');

        $this->assertSame('F j, Y', getParsableDateFormat());
    }

    /**
     * @covers ::\Book_Database\getParsableDateFormat()
     */
    public function test_parsable_date_format_returns_ymd_on_invalid_site_date()
    {
        update_option('date_format', 'j F, Y');

        $this->assertSame('Y-m-d', getParsableDateFormat());
    }

}
