<?php

namespace Laratrust\Tests;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\Permission;

class LaratrustGroupEventsTest extends LaratrustEventsTestCase
{
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->group = Group::create(['name' => 'group']);
    }

    public function testListenToThePermissionAttachedEvent()
    {
        $this->listenTo('permission.attached', Group::class);

        $this->assertHasListenersFor('permission.attached', Group::class);
    }

    public function testListenToThePermissionDetachedEvent()
    {
        $this->listenTo('permission.detached', Group::class);

        $this->assertHasListenersFor('permission.detached', Group::class);
    }

    public function testListenToThePermissionSyncedEvent()
    {
        $this->listenTo('permission.synced', Group::class);

        $this->assertHasListenersFor('permission.synced', Group::class);
    }

    public function testAnEventIsFiredWhenPermissionIsAttachedToGroup()
    {
        $permission = Permission::create(['name' => 'permission']);

        Group::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.attached', [$this->group, $permission->id], Group::class);

        $this->group->attachPermission($permission);
    }

    public function testAnEventIsFiredWhenPermissionIsDetachedFromGroup()
    {
        $permission = Permission::create(['name' => 'permission']);
        $this->group->attachPermission($permission);

        Group::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.detached', [$this->group, $permission->id], Group::class);

        $this->group->detachPermission($permission);
    }

    public function testAnEventIsFiredWhenPermissionsAreSynced()
    {
        $permission = Permission::create(['name' => 'permission']);
        $this->group->attachPermission($permission);

        Group::setEventDispatcher($this->dispatcher);

        $this->dispatcherShouldFire('permission.synced', [
            $this->group,
            [
                'attached' => [], 'detached' => [$permission->id], 'updated' => [],
            ]
        ], Group::class);

        $this->group->syncPermissions([]);
    }
}
