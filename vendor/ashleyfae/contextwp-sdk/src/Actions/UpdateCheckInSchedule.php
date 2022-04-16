<?php
/**
 * UpdateCheckInSchedule.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Actions;

use ContextWP\Repositories\CheckInScheduleRepository;
use ContextWP\Traits\Makeable;

class UpdateCheckInSchedule
{
    use Makeable;

    /**
     * Regular interval used for successful requests.
     */
    const REGULAR_INTERVAL = '+1 week';

    /**
     * Interval used when the service is temporarily unavailable.
     */
    const SERVICE_UNAVAILABLE = '+1 day';

    /** @var CheckInScheduleRepository $checkInRepository */
    protected $checkInRepository;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->checkInRepository = new CheckInScheduleRepository();
    }

    /**
     * Sets the next check in for a period in the future.
     *
     * @since 1.0
     *
     * @param  string|null  $period  Relative period (e.g. "+1 week"). See constants.
     */
    public function setNextCheckIn(?string $period = null): void
    {
        $period    = $period ?: static::REGULAR_INTERVAL;
        $timestamp = strtotime($period) ?: strtotime(static::REGULAR_INTERVAL);

        $this->checkInRepository->set($timestamp);
    }
}
