<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCP\Files\Mount\Event;

use OCP\EventDispatcher\Event;
use OCP\Files\Mount\IMountPoint;

/**
 * Event emitted when a mount was moved.
 *
 * @since 31.0.0
 */
class MountMovedEvent extends Event {
	public function __construct(
		public readonly IMountPoint $mountPoint,
		public readonly string $oldPath,
		public readonly string $newPath,
	) {
		parent::__construct();
	}
}
