<?php
/**
 * CheckInScheduleRepository.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Repositories;

class CheckInScheduleRepository
{
    const OPTION_NAME = 'contextwp_next_checkin';

    /**
     * Sets the check-in timestamp.
     *
     * @since 1.0
     *
     * @param  int  $timestamp
     */
    public function set(int $timestamp): void
    {
        update_option(static::OPTION_NAME, $timestamp, false);
    }

    /**
     * Retrieves the timestamp for the next check-in.
     *
     * @since 1.0
     *
     * @return int|null Timestamp, if one exists, null if not.
     */
    public function get(): ?int
    {
        $value = get_option(static::OPTION_NAME);

        return $value ? (int) $value : null;
    }
}
