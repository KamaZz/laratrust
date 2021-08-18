<?php

namespace Laratrust\Tests;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\User;
use Laratrust\Tests\Models\Permission;

class LaratrustUserEventsTest extends LaratrustEventsTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);
    }

    public function testListenToTheGroupAttachedEvent()
    {
        $this->listenTo('group.attached', User::class);

        $this->assertHasListenersFor('group.attached', User::class);
    }

    public function testListenToTheGroupDetachedEvent()
    {
        $this->listenTo('group.detached', User::class);

        $this->assertHasListenersFor('group.detached', User::class);
    }

    public function testListenToThePermissionAttachedEvent()
    {
        $this->listenTo('permission.attached', User::class);

        $this->assertHasListenersFor('permission.attached', User::class);
    }

    public function testListenToThePermissionDetachedEvent()
    {
        $this->listenTo('permission.detached', User::class);

        $this->assertHasListenersFor('permission.detached', User::class);
    }

    public function testListenToTheGroupSyncedEvent()
    {
        $this->listenTo('group.synced', User::class);

        $this->assertHasListenersFor('group.synced', User::class);
    }

    public function testListenToThePermissionSyncedEvent()
    {
        $this->listenTo('permission.synced', User::class);

        $this->assertHasListenersFor('permission.synced', User::class);
    }

    public function testAnEventIsFiredWhenGroupIsAttachedToUser()
    {
        User::setEventDispatcher($this->dispatcher);
        $group = Group::create(['name' => 'group']);

        $this->dispatcherShouldFire('group.attached', [$this->user, $group->id, null], User::class);

        $this->user->attachGroup($group);
    }

    public function testAnEventIsFiredWhenGroupIsDetachedFromUser()
    {
        $group = Group::create(['name' => 'group']);
        $this->user->attachGroup($group);

        User::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('group.detached', [$this->user, $group->id, null], User::class);

        $this->user->detachGroup($group);
    }

    public function testAnEventIsFiredWhenPermissionIsAttachedToUser()
    {
        $permission = Permission::create(['name' => 'permission']);

        User::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.attached', [$this->user, $permission->id, null], User::class);

        $this->user->attachPermission($permission);
    }

    public function testAnEventIsFiredWhenPermissionIsDetachedFromUser()
    {
        $permission = Permission::create(['name' => 'permission']);
        $this->user->attachPermission($permission);

        User::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.detached', [$this->user, $permission->id, null], User::class);

        $this->user->detachPermission($permission);
    }

    public function testAnEventIsFiredWhenGroupsAreSynced()
    {
        $group = Group::create(['name' => 'group']);

        User::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('group.synced', [
            $this->user,
            [
                'attached' => [$group->id], 'detached' => [], 'updated' => [],
            ],
            null
        ], User::class);

        $this->user->syncGroups([$group]);
    }

    public function testAnEventIsFiredWhenPermissionsAreSynced()
    {
        $permission = Permission::create(['name' => 'permission']);
        $this->user->attachPermission($permission);

        User::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.synced', [
            $this->user,
            [
                'attached' => [], 'detached' => [$permission->id], 'updated' => [],
            ],
            null
        ], User::class);

        $this->user->syncPermissions([]);
    }

    public function testAddObservableClasses()
    {
        $events = [
            'group.attached',
            'group.detached',
            'permission.attached',
            'permission.detached',
            'group.synced',
            'permission.synced',
        ];

        User::laratrustObserve(\Laratrust\Tests\Models\UserObserver::class);

        foreach ($events as $event) {
            $this->assertTrue(User::getEventDispatcher()->hasListeners("laratrust.{$event}: " . User::class));
        }
    }

    public function testObserversShouldBeRemovedAfterFlushEvents()
    {
        $events = [
            'group.attached',
            'group.detached',
            'permission.attached',
            'permission.detached',
            'group.synced',
            'permission.synced',
        ];

        User::laratrustObserve(\Laratrust\Tests\Models\UserObserver::class);
        User::laratrustFlushObservables();

        foreach ($events as $event) {
            $this->assertFalse(User::getEventDispatcher()->hasListeners("laratrust.{$event}: " . User::class));
        }
    }
}
