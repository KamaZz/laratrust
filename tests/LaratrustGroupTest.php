<?php

namespace Laratrust\Test;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustGroupTest extends LaratrustTestCase
{
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->group = Group::create(['name' => 'group']);
    }

    public function testUsersRelationship()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\MorphToMany', $this->group->users());
    }

    public function testAccessUsersRelationshipAsAttribute()
    {
        $this->assertEmpty($this->group->users);
    }

    public function testPermissionsRelationship()
    {
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Relations\BelongsToMany', $this->group->permissions());
    }

    public function testAttachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = Permission::create(['name' => 'permission_a']);
        $permB = Permission::create(['name' => 'permission_b']);
        $permC = Permission::create(['name' => 'permission_c']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->attachPermission($permA));
        $this->assertCount(1, $this->group->permissions()->get()->toArray());

        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->attachPermission($permB->toArray()));
        $this->assertCount(2, $this->group->permissions()->get()->toArray());

        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->attachPermission($permC->id));
        $this->assertCount(3, $this->group->permissions()->get()->toArray());

        if (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('InvalidArgumentException');
        } else {
            $this->expectException('InvalidArgumentException');
        }
        $this->group->attachPermission(true);
    }

    public function testDetachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = Permission::create(['name' => 'permission_a']);
        $permB = Permission::create(['name' => 'permission_b']);
        $permC = Permission::create(['name' => 'permission_c']);
        $this->group->permissions()->attach([$permA->id, $permB->id, $permC->id]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->detachPermission($permA));
        $this->assertCount(2, $this->group->permissions()->get()->toArray());

        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->detachPermission($permB->toArray()));
        $this->assertCount(1, $this->group->permissions()->get()->toArray());

        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->detachPermission($permB->id));
        $this->assertCount(1, $this->group->permissions()->get()->toArray());
    }

    public function testAttachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $perms = [
            Permission::create(['name' => 'permission_a']),
            Permission::create(['name' => 'permission_b']),
            Permission::create(['name' => 'permission_c']),
        ];

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->attachPermissions($perms));
        $this->assertCount(3, $this->group->permissions()->get()->toArray());
    }

    public function testDetachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $perms = [
            Permission::create(['name' => 'permission_a']),
            Permission::create(['name' => 'permission_b']),
            Permission::create(['name' => 'permission_c']),
        ];
        $this->group->attachPermissions($perms);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->detachPermissions($perms));
        $this->assertCount(0, $this->group->permissions()->get()->toArray());
    }

    public function testDetachAllPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $perms = [
            Permission::create(['name' => 'permission_a']),
            Permission::create(['name' => 'permission_b']),
            Permission::create(['name' => 'permission_c']),
        ];
        $this->group->attachPermissions($perms);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->detachPermissions());
        $this->assertCount(0, $this->group->permissions()->get()->toArray());
    }

    public function testSyncPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $perms = [
            Permission::create(['name' => 'permission_a'])->id,
            Permission::create(['name' => 'permission_b'])->id,
        ];
        $this->group->attachPermission(Permission::create(['name' => 'permission_c']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\Group', $this->group->syncPermissions($perms));
        $this->assertCount(2, $this->group->permissions()->get()->toArray());

        $this->group->syncPermissions([]);
        $this->group->syncPermissions(['permission_a', 'permission_b']);
        $this->assertCount(2, $this->group->permissions()->get()->toArray());
    }
}
