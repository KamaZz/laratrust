<?php

namespace Laratrust\Test\Checkers\Group;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustGroupDefaultCheckerTest extends LaratrustTestCase
{
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->app['config']->set('laratrust.checker', 'default');

        $this->group = Group::create(['name' => 'group']);
    }

    public function testHasPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
         */
        $permA = Permission::create(['name' => 'permission_a']);
        $permB = Permission::create(['name' => 'permission_b']);

        $this->group->permissions()->attach([$permA->id, $permB->id]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
         */
        $this->assertTrue($this->group->hasPermission('permission_a'));
        $this->assertTrue($this->group->hasPermission('permission_b'));
        $this->assertFalse($this->group->hasPermission('permission_c'));

        $this->assertTrue($this->group->hasPermission(['permission_a', 'permission_b']));
        $this->assertTrue($this->group->hasPermission(['permission_a', 'permission_c']));
        $this->assertFalse($this->group->hasPermission(['permission_a', 'permission_c'], true));
        $this->assertFalse($this->group->hasPermission(['permission_c', 'permission_d']));
    }
}
