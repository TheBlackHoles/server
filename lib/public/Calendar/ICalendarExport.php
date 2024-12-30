<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCP\Calendar;

use Generator;

/**
 * ICalendar Interface Extension to export data
 *
 * @since 31.0.0
 */
interface ICalendarExport {
	
	/**
	 * Export objects
	 *
	 * @since 31.0.0
	 *
	 * @return Generator<\Sabre\VObject\Component\VCalendar>
	 */
	public function export(?CalendarExportRange $range = null): Generator;

}
