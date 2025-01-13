<?php
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Test\Files\Mount;

use OC\Files\Mount\Manager;
use OC\Files\Mount\MountPoint;
use OC\Files\SetupManagerFactory;
use OC\Files\Storage\Temporary;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\Mount\Event\MountAddedEvent;
use OCP\Files\Mount\Event\MountMovedEvent;
use OCP\Files\Mount\Event\MountRemovedEvent;
use Test\TestCase;

class LongId extends Temporary {
	public function getId(): string {
		return 'long:' . str_repeat('foo', 50) . parent::getId();
	}
}

class ManagerTest extends TestCase {
	private IEventDispatcher $eventDispatcher;
	private Manager $manager;

	protected function setUp(): void {
		parent::setUp();

		$this->eventDispatcher = $this->createMock(IEventDispatcher::class);

		$this->manager = new Manager(
			$this->createMock(SetupManagerFactory::class),
			$this->eventDispatcher,
		);
	}

	public function testFind(): void {
		$rootMount = new MountPoint(new Temporary([]), '/');
		$this->manager->addMount($rootMount);
		$this->assertEquals($rootMount, $this->manager->find('/'));
		$this->assertEquals($rootMount, $this->manager->find('/foo/bar'));

		$storage = new Temporary([]);
		$mount1 = new MountPoint($storage, '/foo');
		$this->manager->addMount($mount1);
		$this->assertEquals($rootMount, $this->manager->find('/'));
		$this->assertEquals($mount1, $this->manager->find('/foo/bar'));

		$this->assertEquals(1, count($this->manager->findIn('/')));
		$mount2 = new MountPoint(new Temporary([]), '/bar');
		$this->manager->addMount($mount2);
		$this->assertEquals(2, count($this->manager->findIn('/')));

		$id = $mount1->getStorageId();
		$this->assertEquals([$mount1], $this->manager->findByStorageId($id));

		$mount3 = new MountPoint($storage, '/foo/bar');
		$this->manager->addMount($mount3);
		$this->assertEquals([$mount1, $mount3], $this->manager->findByStorageId($id));
	}

	public function testLong(): void {
		$storage = new LongId([]);
		$mount = new MountPoint($storage, '/foo');
		$this->manager->addMount($mount);

		$id = $mount->getStorageId();
		$storageId = $storage->getId();
		$this->assertEquals([$mount], $this->manager->findByStorageId($id));
		$this->assertEquals([$mount], $this->manager->findByStorageId($storageId));
		$this->assertEquals([$mount], $this->manager->findByStorageId(md5($storageId)));
	}

	public function testAddMountEvent(): void {
		$this->eventDispatcher
			->expects($this->once())
			->method('dispatchTyped')
			->with($this->callback(fn (MountAddedEvent $event) => $event->mountPoint->getMountPoint() === '/foo/'));

		$this->manager->addMount(new MountPoint(new Temporary([]), '/foo'));
	}

	public function testRemoveMountEvent(): void {
		$this->eventDispatcher
			->expects($this->exactly(2))
			->method('dispatchTyped')
			->with($this->callback(fn (MountAddedEvent|MountRemovedEvent $event) => $event->mountPoint->getMountPoint() === '/foo/'));

		$this->manager->addMount(new MountPoint(new Temporary([]), '/foo'));
		$this->manager->removeMount('/foo');
	}

	public function testMoveMountEvent(): void {
		$this->eventDispatcher
			->expects($this->exactly(2))
			->method('dispatchTyped')
			->with($this->callback(fn (MountAddedEvent|MountMovedEvent $event) =>
				($event instanceof MountAddedEvent && $event->mountPoint->getMountPoint() === '/foo/')
				// The getMountPoint() still returns the old path in this test because it is updated outside the MountManager before calling moveMount().
				|| ($event instanceof MountMovedEvent && $event->mountPoint->getMountPoint() === '/foo/' && $event->oldPath === '/foo/' && $event->newPath === '/bar/'))
			);

		$this->manager->addMount(new MountPoint(new Temporary([]), '/foo'));
		$this->manager->moveMount('/foo/', '/bar/');
	}
}
