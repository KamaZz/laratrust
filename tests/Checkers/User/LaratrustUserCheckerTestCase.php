<?php

namespace Laratrust\Tests\Checkers\User;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\Team;
use Laratrust\Tests\Models\User;
use Illuminate\Support\Facades\Cache;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustUserCheckerTestCase extends LaratrustTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);

        $this->app['config']->set('laratrust.teams.enabled', true);
    }

    protected function getGroupsAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $groups = [
            Group::create(['name' => 'group_a'])->id => ['team_id' => null],
            Group::create(['name' => 'group_b'])->id => ['team_id' => null],
            Group::create(['name' => 'group_c'])->id => ['team_id' => $team->id ]
        ];
        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->user->groups()->attach($groups);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.teams.strict_check', true);
        $this->assertEquals(['group_a', 'group_b'], $this->user->getGroups());
        $this->app['config']->set('laratrust.teams.strict_check', false);
        $this->assertEquals(['group_a', 'group_b', 'group_c'], $this->user->getGroups());

        $this->app['config']->set('laratrust.teams.strict_check', true);
        $this->assertEquals(['group_c'], $this->user->getGroups('team_a'));
        $this->app['config']->set('laratrust.teams.strict_check', false);
        $this->assertEquals(['group_c'], $this->user->getGroups('team_a'));

        $this->app['config']->set('laratrust.cache.enabled', false);
        $this->assertEquals(['group_a', 'group_b', 'group_c'], $this->user->getGroups());
        $this->assertEquals(['group_c'], $this->user->getGroups('team_a'));
    }

    protected function hasGroupAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $groups = [
            Group::create(['name' => 'group_a'])->id => ['team_id' => null],
            Group::create(['name' => 'group_b'])->id => ['team_id' => null],
            Group::create(['name' => 'group_c'])->id => ['team_id' => $team->id ]
        ];
        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->user->groups()->attach($groups);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->user->hasGroup([]));
        $this->assertTrue($this->user->hasGroup('group_a'));
        $this->assertTrue($this->user->hasGroup('group_b'));
        $this->assertTrue($this->user->hasGroup('group_c'));
        $this->app['config']->set('laratrust.teams.strict_check', true);
        $this->assertFalse($this->user->hasGroup('group_c'));
        $this->app['config']->set('laratrust.teams.strict_check', false);
        $this->assertTrue($this->user->hasGroup('group_c', 'team_a'));
        $this->assertFalse($this->user->hasGroup('group_a', 'team_a'));

        $this->assertTrue($this->user->hasGroup('group_a|group_b'));
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_b']));
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_c']));
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_c'], 'team_a'));
        $this->assertFalse($this->user->hasGroup(['group_a', 'group_c'], 'team_a', true));
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_c'], true));
        $this->assertFalse($this->user->hasGroup(['group_c', 'group_d'], true));

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_c'], 'team_a'));
        $this->assertFalse($this->user->hasGroup(['group_c', 'group_d'], true));

        $this->app['config']->set('laratrust.cache.enabled', false);
        $this->assertTrue($this->user->hasGroup('group_a'));
        $this->assertTrue($this->user->hasGroup(['group_a', 'group_c'], 'team_a'));
        $this->assertTrue($this->user->hasGroup('group_c', 'team_a'));
    }

    protected function hasPermissionAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);

        $groupA = Group::create(['name' => 'group_a'])
            ->attachPermission(Permission::create(['name' => 'permission_a']));
        $groupB = Group::create(['name' => 'group_b'])
            ->attachPermission(Permission::create(['name' => 'permission_b']));

        $this->user->groups()->attach([
            $groupA->id => ['team_id' => null],
            $groupB->id => ['team_id' => $team->id ]
        ]);

        $this->user->permissions()->attach([
            Permission::create(['name' => 'permission_c'])->id => ['team_id' => $team->id ],
            Permission::create(['name' => 'permission_d'])->id => ['team_id' => null],
        ]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->user->hasPermission([]));
        $this->assertTrue($this->user->hasPermission('permission_a'));
        $this->assertTrue($this->user->hasPermission('permission_b', 'team_a'));
        $this->assertTrue($this->user->hasPermission('permission_b', $team));
        $this->app['config']->set('laratrust.teams.strict_check', true);
        $this->assertFalse($this->user->hasPermission('permission_c'));
        $this->app['config']->set('laratrust.teams.strict_check', false);
        $this->assertTrue($this->user->hasPermission('permission_c'));
        $this->assertTrue($this->user->hasPermission('permission_c', 'team_a'));
        $this->assertTrue($this->user->hasPermission('permission_c', $team));
        $this->assertTrue($this->user->hasPermission('permission_d'));
        $this->assertFalse($this->user->hasPermission('permission_e'));

        $this->assertTrue($this->user->hasPermission(['permission_a', 'permission_b', 'permission_c', 'permission_d', 'permission_e']));
        $this->assertTrue($this->user->hasPermission('permission_a|permission_b|permission_c|permission_d|permission_e'));
        $this->assertTrue($this->user->hasPermission(['permission_a', 'permission_d'], true));
        $this->assertTrue($this->user->hasPermission(['permission_a', 'permission_b', 'permission_d'], true));
        $this->assertFalse($this->user->hasPermission(['permission_a', 'permission_b', 'permission_d'], 'team_a', true));
        $this->assertFalse($this->user->hasPermission(['permission_a', 'permission_b', 'permission_d'], $team, true));
        $this->assertFalse($this->user->hasPermission(['permission_a', 'permission_b', 'permission_e'], true));
        $this->assertFalse($this->user->hasPermission(['permission_e', 'permission_f']));

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertTrue($this->user->hasPermission(['permission_a', 'permission_b', 'permission_d'], 'team_a', true));
        $this->assertTrue($this->user->hasPermission(['permission_a', 'permission_b', 'permission_d'], $team, true));

        $this->app['config']->set('laratrust.cache.enabled', false);
        $this->assertTrue($this->user->hasPermission('permission_b', 'team_a'));
        $this->assertTrue($this->user->hasPermission('permission_c', 'team_a'));
    }

    protected function hasPermissionWithPlaceholderSupportAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);

        $group = Group::create(['name' => 'group_a'])
            ->attachPermissions([
                Permission::create(['name' => 'admin.posts']),
                Permission::create(['name' => 'admin.pages']),
                Permission::create(['name' => 'admin.users']),
            ]);

        $this->user->groups()->attach($group->id);

        $this->user->permissions()->attach([
            Permission::create(['name' => 'config.things'])->id => ['team_id' => $team->id ],
            Permission::create(['name' => 'config.another_things'])->id => ['team_id' => $team->id],
        ]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->user->hasPermission('admin.posts'));
        $this->assertTrue($this->user->hasPermission('admin.pages'));
        $this->assertTrue($this->user->hasPermission('admin.users'));
        $this->assertFalse($this->user->hasPermission('admin.config', 'team_a'));

        $this->assertTrue($this->user->hasPermission(['admin.*']));
        $this->assertTrue($this->user->hasPermission(['admin.*']));
        $this->assertTrue($this->user->hasPermission(['config.*'], 'team_a'));
        $this->assertTrue($this->user->hasPermission(['config.*']));
        $this->assertFalse($this->user->hasPermission(['site.*']));
    }

    public function userDisableTheGroupsAndPermissionsCachingAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $group = Group::create(['name' => 'group_a'])
            ->attachPermissions([
                Permission::create(['name' => 'permission_a']),
                Permission::create(['name' => 'permission_b']),
                Permission::create(['name' => 'permission_c']),
            ]);

        $this->user->groups()->attach($group->id);

        $this->user->permissions()->attach([
            Permission::create(['name' => 'permission_d'])->id => ['team_id' => $team->id ],
            Permission::create(['name' => 'permission_e'])->id => ['team_id' => $team->id],
        ]);

        // With cache
        $this->app['config']->set('laratrust.cache.enabled', true);
        $this->user->hasGroup('some_group');
        $this->user->hasPermission('some_permission');
        $this->assertTrue(Cache::has("laratrust_groups_for_users_{$this->user->id}"));
        $this->assertTrue(Cache::has("laratrust_permissions_for_users_{$this->user->id}"));
        $this->user->flushCache();

        // Without cache
        $this->app['config']->set('laratrust.cache.enabled', false);
        $this->user->hasGroup('some_group');
        $this->user->hasPermission('some_permission');
        $this->assertFalse(Cache::has("laratrust_groups_for_users_{$this->user->id}"));
        $this->assertFalse(Cache::has("laratrust_permissions_for_users_{$this->user->id}"));
    }
}
