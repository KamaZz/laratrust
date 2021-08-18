<?php

namespace Laratrust\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\OwnableObject;
use Laratrust\Tests\Models\Permission;
use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\Team;
use Laratrust\Tests\Models\User;
use Laratrust\Traits\LaratrustUserTrait;
use Mockery as m;

class LaratrustUserTest extends LaratrustTestCase
{
    /**
     * @var LaratrustUserTrait|Model
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);

        $this->app['config']->set('laratrust.teams.enabled', true);
    }

    public function testGroupsRelationship()
    {
        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->groups()
        );

        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->groups()
        );
    }

    public function testPermissionsRelationship()
    {
        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->permissions()
        );

        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->permissions()
        );
    }

    public function testGroupsTeams()
    {
        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertNull($this->user->groupsTeams());

        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->groupsTeams()
        );
    }


    public function testPermissionsTeams()
    {
        /*
       |------------------------------------------------------------
       | Set
       |------------------------------------------------------------
       */

        $team = Team::create(['name' => 'team_a']);

        $this->user->attachPermissions([
            Permission::create(['name' => 'permission_a']),
            Permission::create(['name' => 'permission_b']),
        ], $team);


        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertNull($this->user->permissionsTeams());

        $this->app['config']->set('laratrust.teams.enabled', true);
        $this->assertInstanceOf(
            '\Illuminate\Database\Eloquent\Relations\MorphToMany',
            $this->user->permissionsTeams()
        );
        $this->assertInstanceOf(
            Team::class,
            $this->user->permissionsTeams()->first()
        );
    }


    public function testAllTeams()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $teamA = Team::create(['name' => 'team_a']);
        $teamB = Team::create(['name' => 'team_b']);
        $this->user->attachPermissions([
            Permission::create(['name' => 'permission_a']),
            Permission::create(['name' => 'permission_b']),
            Permission::create(['name' => 'permission_c'])
        ], $teamA);

        $this->user->attachGroups([
            Group::create(['name' => 'group_a']),
            Group::create(['name' => 'group_b']),
            Group::create(['name' => 'group_c'])
        ], $teamB);

        $this->user->attachGroups([
            Group::create(['name' => 'group_d']),
        ], $teamA);


        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf(  '\Illuminate\Database\Eloquent\Collection', $this->user->allTeams());


        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertEmpty($this->user->allTeams());

        $this->app['config']->set('laratrust.teams.enabled', true);

        $this->assertSame(
            ['team_a', 'team_b',],
            $this->user->allTeams()->sortBy('name')->pluck('name')->all()
        );
        $onlySomeColumns = $this->user->allTeams(['name'])->first()->toArray();
        $this->assertArrayHasKey('id', $onlySomeColumns);
        $this->assertArrayHasKey('name', $onlySomeColumns);
        $this->assertArrayNotHasKey('displayName', $onlySomeColumns);

    }

    public function testIsAbleTo()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('hasPermission')->with('manage_user', null, false)->andReturn(true)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->isAbleTo('manage_user'));
    }

    public function testMagicIsAbleToPermissionMethod()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $this->user->permissions()->attach([
            Permission::create(['name' => 'manage-user'])->id,
            Permission::create(['name' => 'manage_user'])->id,
            Permission::create(['name' => 'manageUser'])->id,
        ]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->app['config']->set('laratrust.magic_can_method_case', 'kebab_case');
        $this->assertTrue($this->user->isAbleToManageUser());

        $this->app['config']->set('laratrust.magic_can_method_case', 'snake_case');
        $this->assertTrue($this->user->isAbleToManageUser());

        $this->app['config']->set('laratrust.magic_can_method_case', 'camel_case');
        $this->assertTrue($this->user->isAbleToManageUser());
    }

    public function testAttachGroup()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $group = Group::create(['name' => 'group_a']);
        $team = Team::create(['name' => 'team_a']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Can attach group by passing an object
        $this->assertWasAttached('group', $this->user->attachGroup($group));
        // Can attach group by passing an id
        $this->assertWasAttached('group', $this->user->attachGroup($group->id));
        // Can attach group by passing an array with 'id' => $id
        $this->assertWasAttached('group', $this->user->attachGroup($group->toArray()));
        // Can attach group by passing the group name
        $this->assertWasAttached('group', $this->user->attachGroup('group_a'));
        // Can attach group by passing the group and team
        $this->assertWasAttached('group', $this->user->attachGroup($group, $team));
        // Can attach group by passing the group and team id
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachGroup($group, $team->id));
        $this->assertEquals($team->id, $this->user->groups()->first()->pivot->team_id);
        $this->user->groups()->sync([]);
        // Can attach group by passing the group and team name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachGroup($group, 'team_a'));
        $this->assertEquals($team->id, $this->user->groups()->first()->pivot->team_id);
        $this->user->groups()->sync([]);

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertWasAttached('group', $this->user->attachGroup($group));

        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachGroup($group, 'team_a'));
        $this->assertNull($this->user->groups()->first()->pivot->team_id);
        $this->user->groups()->sync([]);
    }

    public function testDetachGroup()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $group = Group::create(['name' => 'group_a']);
        $this->user->groups()->attach($group->id);
        $team = Team::create(['name' => 'team_a']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Can attach group by passing an object
        $this->assertWasDetached('group', $this->user->detachGroup($group), $group);
        // Can detach group by passing an id
        $this->assertWasDetached('group', $this->user->detachGroup($group->id), $group);
        // Can detach group by passing an array with 'id' => $id
        $this->assertWasDetached('group', $this->user->detachGroup($group->toArray()), $group);
        // Can detach group by passing the group name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachGroup('group_a'));
        $this->assertEquals(0, $this->user->groups()->count());
        $this->user->groups()->attach($group->id, ['team_id' => $team->id]);
        // Can detach group by passing the group and team
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachGroup($group, $team));
        $this->assertEquals(0, $this->user->groups()->count());
        $this->user->groups()->attach($group->id, ['team_id' => $team->id]);
        // Can detach group by passing the group and team id
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachGroup($group, $team->id));
        $this->assertEquals(0, $this->user->groups()->count());
        $this->user->groups()->attach($group->id, ['team_id' => $team->id]);
        // Can detach group by passing the group and team name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachGroup($group, 'team_a'));

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertWasDetached('group', $this->user->detachGroup($group), $group);
        $this->assertWasDetached('group', $this->user->detachGroup($group, 'TeamA'), $group);
    }

    public function testAttachGroups()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('attachGroup')->with(m::anyOf(1, 2, 3), m::anyOf(null, 'TeamA'))->times(6);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->attachGroups([1, 2, 3]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->attachGroups([1, 2, 3], 'TeamA'));
    }

    public function testDetachGroups()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('groups->get')->andReturn([1, 2, 3])->once();
        $user->shouldReceive('detachGroup')->with(m::anyOf(1, 2, 3), m::anyOf(null, 'TeamA'))->times(9);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachGroups([1, 2, 3]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachGroups([]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachGroups([1, 2, 3], 'TeamA'));
    }

    public function testSyncGroups()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $groups = [
            Group::create(['name' => 'group_a'])->id,
            Group::create(['name' => 'group_b']),
        ];
        $this->user->attachGroup(Group::create(['name' => 'group_c']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroups($groups));
        $this->assertEquals(2, $this->user->groups()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroups($groups, 'team_a'));
        $this->assertEquals(4, $this->user->groups()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroups(['group_a']));
        $this->assertEquals(3, $this->user->groups()->count());

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->user->syncGroups([]);
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroups($groups, null));
        $this->assertEquals(2, $this->user->groups()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroups($groups, 'team_a', false));
        $this->assertEquals(2, $this->user->groups()->count());
    }

    public function testSyncGroupsWithoutDetaching()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $groups = [
            Group::create(['name' => 'group_a'])->id,
            Group::create(['name' => 'group_b'])->id,
        ];
        $this->user->attachGroup(Group::create(['name' => 'group_c']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroupsWithoutDetaching($groups));
        $this->assertEquals(3, $this->user->groups()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroupsWithoutDetaching($groups, 'team_a'));
        $this->assertEquals(5, $this->user->groups()->count());

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->user->detachGroups([1]);
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroupsWithoutDetaching($groups, null));
        $this->assertEquals(4, $this->user->groups()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncGroupsWithoutDetaching($groups, 'team_a', false));
        $this->assertEquals(4, $this->user->groups()->count());
    }

    public function testAttachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permission = Permission::create(['name' => 'permission_a']);
        $team = Team::create(['name' => 'team_a']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Can attach permission by passing an object
        $this->assertWasAttached('permission', $this->user->attachPermission($permission));
        // Can attach permission by passing an id
        $this->assertWasAttached('permission', $this->user->attachPermission($permission->id));
        // Can attach permission by passing an array with 'id' => $id
        $this->assertWasAttached('permission', $this->user->attachPermission($permission->toArray()));
        // Can attach permission by passing the permission name
        $this->assertWasAttached('permission', $this->user->attachPermission('permission_a'));
        // Can attach permission by passing the permission and team
        $this->assertWasAttached('permission', $this->user->attachPermission($permission, $team));
        // Can attach permission by passing the permission and team id
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachPermission($permission, $team->id));
        $this->assertEquals($team->id, $this->user->permissions()->first()->pivot->team_id);
        $this->user->permissions()->sync([]);
        // Can attach permission by passing the permission and team name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachPermission($permission, 'team_a'));
        $this->assertEquals($team->id, $this->user->permissions()->first()->pivot->team_id);
        $this->user->permissions()->sync([]);

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertWasAttached('permission', $this->user->attachPermission($permission));

        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->attachPermission($permission, 'team_a'));
        $this->assertNull($this->user->permissions()->first()->pivot->team_id);
        $this->user->permissions()->sync([]);
    }

    public function testDetachPermission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permission = Permission::create(['name' => 'permission_a']);
        $this->user->permissions()->attach($permission->id);
        $team = Team::create(['name' => 'team_a']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Can attach permission by passing an object
        $this->assertWasDetached('permission', $this->user->detachPermission($permission), $permission);
        // Can detach permission by passing an id
        $this->assertWasDetached('permission', $this->user->detachPermission($permission->id), $permission);
        // Can detach permission by passing an array with 'id' => $id
        $this->assertWasDetached('permission', $this->user->detachPermission($permission->toArray()), $permission);
        // Can detach permission by passing the permission name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachPermission('permission_a'));
        $this->assertEquals(0, $this->user->permissions()->count());
        $this->user->permissions()->attach($permission->id, ['team_id' => $team->id]);
        // Can detach permission by passing the permission and team
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachPermission($permission, $team));
        $this->assertEquals(0, $this->user->permissions()->count());
        $this->user->permissions()->attach($permission->id, ['team_id' => $team->id]);
        // Can detach permission by passing the permission and team id
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachPermission($permission, $team->id));
        $this->assertEquals(0, $this->user->permissions()->count());
        $this->user->permissions()->attach($permission->id, ['team_id' => $team->id]);
        // Can detach permission by passing the permission and team name
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->detachPermission($permission, 'team_a'));

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->assertWasDetached('permission', $this->user->detachPermission($permission), $permission);
        $this->assertWasDetached('permission', $this->user->detachPermission($permission, 'team_a'), $permission);
    }

    public function testAttachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('attachPermission')->with(m::anyOf(1, 2, 3), m::anyOf(null, 'TeamA'))->times(6);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->attachPermissions([1, 2, 3]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->attachPermissions([1, 2, 3], 'TeamA'));
    }

    public function testDetachPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('permissions->get')->andReturn([1, 2, 3])->once();
        $user->shouldReceive('detachPermission')->with(m::anyOf(1, 2, 3), m::anyOf(null, 'TeamA'))->times(9);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachPermissions([1, 2, 3]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachPermissions([]));
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $user->detachPermissions([1, 2, 3], 'TeamA'));
    }

    public function syncPermissions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $permissions = [
            Permission::create(['name' => 'permission_a'])->id,
            Permission::create(['name' => 'permission_b']),
        ];
        $this->user->attachPermission(Permission::create(['name' => 'permission_c']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissions($permissions));
        $this->assertEquals(2, $this->user->permissions()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissions($permissions, 'team_a'));
        $this->assertEquals(4, $this->user->permissions()->count());

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->user->syncPermissions([]);
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissions($permissions, null));
        $this->assertEquals(2, $this->user->permissions()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissions($permissions, 'team_a', false));
        $this->assertEquals(2, $this->user->permissions()->count());
    }


    public function testSyncPermissionsWithoutDetaching()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $permissions = [
            Permission::create(['name' => 'permission_a'])->id,
            Permission::create(['name' => 'permission_b'])->id,
        ];
        $this->user->attachPermission(Permission::create(['name' => 'permission_c']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissionsWithoutDetaching($permissions));
        $this->assertEquals(3, $this->user->permissions()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissionsWithoutDetaching($permissions, 'team_a'));
        $this->assertEquals(5, $this->user->permissions()->count());

        $this->app['config']->set('laratrust.teams.enabled', false);
        $this->user->detachPermissions([1]);
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissionsWithoutDetaching($permissions, null));
        $this->assertEquals(4, $this->user->permissions()->count());
        $this->assertInstanceOf('Laratrust\Tests\Models\User', $this->user->syncPermissionsWithoutDetaching($permissions, 'team_a', false));
        $this->assertEquals(4, $this->user->permissions()->count());
    }

    public function testUserOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();
        $className = Str::snake(get_class($user)) . '_id';

        $post = new \stdClass();
        $post->$className = $user->getKey();

        $post2 = new \stdClass();
        $post2->$className = 9;

        $ownableObject = new OwnableObject;

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->owns($post));
        $this->assertFalse($user->owns($post2));
        $this->assertFalse($user->owns($ownableObject));
    }

    public function testUserOwnsaPostModelCustomKey()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();
        $post = new \stdClass();
        $post->UserId = $user->getKey();

        $post2 = new \stdClass();
        $post2->UserId = 9;

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->owns($post, 'UserId'));
        $this->assertFalse($user->owns($post2, 'UserId'));
    }

    public function testUserHasGroupAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();
        $post = new \stdClass();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('hasGroup')->with('editor', null, false)->andReturn(true)->once();
        $user->shouldReceive('owns')->with($post, null)->andReturn(true)->once();
        $user->shouldReceive('hasGroup')->with('regular-user', null, false)->andReturn(false)->once();
        $user->shouldReceive('hasGroup')->with('administrator', null, true)->andReturn(true)->once();
        $user->shouldReceive('hasGroup')->with('team-member', $team, true)->andReturn(false)->once();
        $user->shouldReceive('owns')->with($post, 'UserID')->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->hasGroupAndOwns('editor', $post));
        $this->assertFalse($user->hasGroupAndOwns('regular-user', $post));
        $this->assertFalse($user->hasGroupAndOwns('administrator', $post, [
            'requireAll' => true, 'foreignKeyName' => 'UserID'
        ]));
        $this->assertFalse($user->hasGroupAndOwns('team-member', $post, [
            'requireAll' => true,
            'foreignKeyName' => 'UserID',
            'team' => $team
        ]));
    }

    public function testUserIsAbleToAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $team = Team::create(['name' => 'team_a']);
        $user = m::mock('Laratrust\Tests\Models\User')->makePartial();
        $post = new \stdClass();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('hasPermission')->with('edit-post', null, false)->andReturn(true)->once();
        $user->shouldReceive('owns')->with($post, null)->andReturn(true)->once();
        $user->shouldReceive('hasPermission')->with('update-post', null, false)->andReturn(false)->once();
        $user->shouldReceive('hasPermission')->with('enhance-post', null, true)->andReturn(true)->once();
        $user->shouldReceive('hasPermission')->with('edit-team', $team, true)->andReturn(false)->once();
        $user->shouldReceive('owns')->with($post, 'UserID')->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->isAbleToAndOwns('edit-post', $post));
        $this->assertFalse($user->isAbleToAndOwns('update-post', $post));
        $this->assertFalse($user->isAbleToAndOwns('enhance-post', $post, [
            'requireAll' => true, 'foreignKeyName' => 'UserID'
        ]));
        $this->assertFalse($user->isAbleToAndOwns('edit-team', $post, [
            'requireAll' => true,
            'foreignKeyName' => 'UserID',
            'team' => $team
        ]));
    }

    public function testAllPermissions()
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

        $groupA->attachPermissions([$permissionA, $permissionB]);
        $groupB->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachGroups([$groupA, $groupB]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame(
            ['permission_a', 'permission_b', 'permission_c'],
            $this->user->allPermissions()->sortBy('name')->pluck('name')->all()
        );

        $onlySomeColumns = $this->user->allPermissions(['name'])->first()->toArray();
        $this->assertArrayHasKey('id', $onlySomeColumns);
        $this->assertArrayHasKey('name', $onlySomeColumns);
        $this->assertArrayNotHasKey('displayName', $onlySomeColumns);
    }

    public function testAllPermissionsScopedOnTeams()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $groupA = Group::create(['name' => 'group_a']);
        $groupB = Group::create(['name' => 'group_b']);
        $groupC = Group::create(['name' => 'group_c']);
        $permissionA = Permission::create(['name' => 'permission_a']);
        $permissionB = Permission::create(['name' => 'permission_b']);
        $permissionC = Permission::create(['name' => 'permission_c']);
        $permissionD = Permission::create(['name' => 'permission_d']);

        $teamA = Team::create(['name' => 'team_a']);
        $teamB = Team::create(['name' => 'team_b']);

        $groupA->attachPermissions([$permissionA, $permissionB]);
        $groupB->attachPermissions([$permissionB, $permissionC]);
        $groupC->attachPermissions([$permissionD]);

        $this->user->attachPermissions([$permissionB, $permissionC]);
        $this->user->attachPermissions([$permissionC], $teamA);
        $this->user->attachGroup($groupA);
        $this->user->attachGroup($groupB, $teamA);
        $this->user->attachGroup($groupC, $teamB);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame(
            ['permission_a', 'permission_b', 'permission_c', 'permission_d'],
            $this->user->allPermissions(null, false)->sortBy('name')->pluck('name')->all()
        );
        $this->assertSame(
            ['permission_a', 'permission_b', 'permission_c'],
            $this->user->allPermissions(null, null)->sortBy('name')->pluck('name')->all()
        );
        $this->assertSame(
            ['permission_b', 'permission_c'],
            $this->user->allPermissions(null, 'team_a')->sortBy('name')->pluck('name')->all()
        );

        $this->assertSame(
            ['permission_d',],
            $this->user->allPermissions(null, 'team_b')->sortBy('name')->pluck('name')->all()
        );

    }

    protected function assertWasAttached($objectName, $result)
    {
        $relationship = \Illuminate\Support\Str::plural($objectName);

        $this->assertInstanceOf('Laratrust\Tests\Models\User', $result);
        $this->assertEquals(1, $this->user->$relationship()->count());
        $this->user->$relationship()->sync([]);
    }

    protected function assertWasDetached($objectName, $result, $toReAttach)
    {
        $relationship = \Illuminate\Support\Str::plural($objectName);

        $this->assertInstanceOf('Laratrust\Tests\Models\User', $result);
        $this->assertEquals(0, $this->user->$relationship()->count());
        $this->user->$relationship()->attach($toReAttach->id);
    }
}
