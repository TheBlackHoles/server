<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCP\Calendar;

/**
 * Calendar Export Limit Range
 *
 * @since 31.0.0
 */
class CalendarExportRange {

	public ?int $start = null;
	public ?int $count = null;

}
