<?php

namespace Laratrust\Test;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\Team;
use Laratrust\Tests\Models\User;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustUserScopesTest extends LaratrustTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);

        $this->app['config']->set('laratrust.teams.enabled', true);
    }


    public function testScopeWhereGroupIs()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
         */
        $groupA = Group::create(['name' => 'group_a']);
        $groupB = Group::create(['name' => 'group_b']);
        $groupC = Group::create(['name' => 'group_c']);
        $groupD = Group::create(['name' => 'group_d']);
        $team = Team::create(['name' => 'team_a']);

        $this->user->attachGroups([$groupA, $groupB]);
        $this->user->attachGroup($groupD, $team->id);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertCount(1, User::whereGroupIs('group_a')->get());
        $this->assertCount(1, User::whereGroupIs(['group_a', 'group_c'])->get());
        $this->assertCount(0, User::whereGroupIs('group_c')->get());
        $this->assertCount(0, User::whereGroupIs(['group_c', 'group_x'])->get());

        $this->assertCount(1, User::whereGroupIs('group_d', 'team_a')->get());

        $this->app['config']->set('laratrust.teams.strict_check', true);
        $this->assertCount(0, User::whereGroupIs('group_d')->get());
        $this->assertCount(0, User::whereGroupIs(['group_d', 'group_c'])->get());
        $this->app['config']->set('laratrust.teams.strict_check', false);
        $this->assertCount(1, User::whereGroupIs('group_d')->get());
        $this->assertCount(1, User::whereGroupIs(['group_d', 'group_c'])->get());
    }

    public function testScopeOrWhereGroupIs()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
         */
        $groupA = Group::create(['name' => 'group_a']);
        $groupC = Group::create(['name' => 'group_c']);

        $this->user->attachGroup($groupA);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertCount(
            1,
            User::query()
                ->whereGroupIs('group_a')
                ->orWhereGroupIs('group_c')
                ->get()
        );
        $this->assertCount(
            0,
            User::query()
                ->whereGroupIs('group_d')
                ->orWhereGroupIs('group_c')
                ->get()
        );
    }

    public function testScopeWherePermissionIs()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
         */
        $groupA = Group::create(['name' => 'group_a']);
        $groupB = Group::create(['name' => 'group_b']);
        $permissionA = Permission::create(['name' => 'permission_a']);
        $permissionB = Permission::create(['name' => 'permission_b']);
        $permissionC = Permission::create(['name' => 'permission_c']);
        $permissionD = Permission::create(['name' => 'permission_d']);

        $groupA->attachPermissions([$permissionA, $permissionB]);
        $groupB->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachGroups([$groupA, $groupB]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertCount(1, User::wherePermissionIs('permission_a')->get());
        $this->assertCount(1, User::wherePermissionIs('permission_c')->get());
        $this->assertCount(1, User::wherePermissionIs(['permission_c', 'permission_d'])->get());
        $this->assertCount(0, User::wherePermissionIs('permission_d')->get());
    }

    public function testScopeOrWherePermissionIs()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
         */
        $groupA = Group::create(['name' => 'group_a']);
        $groupB = Group::create(['name' => 'group_b']);
        $permissionA = Permission::create(['name' => 'permission_a']);
        $permissionB = Permission::create(['name' => 'permission_b']);
        $permissionC = Permission::create(['name' => 'permission_c']);
        $permissionD = Permission::create(['name' => 'permission_d']);

        $groupA->attachPermissions([$permissionA, $permissionB]);
        $groupB->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachGroups([$groupA, $groupB]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertCount(
            1,
            User::query()
                ->wherePermissionIs('permission_a')
                ->orWherePermissionIs('permission_d')
                ->get()
        );
        $this->assertCount(
            1,
            User::query()
                ->wherePermissionIs('permission_c')
                ->orWherePermissionIs('permission_d')
                ->get()
        );
        $this->assertCount(
            0,
            User::query()
                ->orWherePermissionIs('permission_e')
                ->orWherePermissionIs('permission_d')
                ->get()
        );
    }

    public function testScopeToRetrieveTheUsersThatDontHaveGroups()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $groupA = Group::create(['name' => 'group_a']);
        $this->user->attachGroups([$groupA]);
        $userWithoutGroup = User::create(['name' => 'test2', 'email' => 'test2@test.com']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertEquals($userWithoutGroup->id, User::whereDoesntHaveGroup()->first()->id);
        $this->assertCount(1, User::whereDoesntHaveGroup()->get());
    }

    public function testScopeToRetrieveTheUsersThatDontHavePermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $groupA = Group::create(['name' => 'group_a']);
        $groupB = Group::create(['name' => 'group_b']);
        $permissionA = Permission::create(['name' => 'permission_a']);
        $permissionB = Permission::create(['name' => 'permission_b']);

        $groupA->attachPermissions([$permissionA]);
        $this->user->attachPermissions([$permissionB]);
        $this->user->attachGroups([$groupA]);
        $userWithoutPerms = User::create(['name' => 'test2', 'email' => 'test2@test.com']);
        $userWithoutPerms->attachGroup($groupB);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
         */
        $this->assertEquals($userWithoutPerms->id, User::whereDoesntHavePermission()->first()->id);
        $this->assertCount(1, User::whereDoesntHavePermission()->get());
    }
}
