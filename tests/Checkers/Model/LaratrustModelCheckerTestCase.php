<?php

namespace Laratrust\Tests\Checkers\Model;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\User;
use Laratrust\Tests\Models\Other;
use Illuminate\Support\Facades\Cache;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustModelCheckerTestCase extends LaratrustTestCase
{
    protected $user;

    protected $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);
        $this->other = Other::create(['name' => 'test', 'email' => 'test@test.com']);

        $this->app['config']->set('laratrust.use_morph_map', true);
        $this->app['config']->set('laratrust.user_models', [
            'users' => 'Laratrust\Tests\Models\User',
            'others' => 'Laratrust\Tests\Models\Other'
        ]);
    }

    public function modelDisableTheGroupsAndPermissionsCachingAssertions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $group = Group::create(['name' => 'group_a'])
            ->attachPermissions([
                Permission::create(['name' => 'permission_a']),
                Permission::create(['name' => 'permission_b']),
                Permission::create(['name' => 'permission_c']),
            ]);

        $this->user->groups()->attach($group->id);
        $this->user->permissions()->attach([
            Permission::create(['name' => 'permission_d']),
            Permission::create(['name' => 'permission_e']),
        ]);

        $this->other->groups()->attach($group->id);
        $this->other->permissions()->attach([
            Permission::UpdateOrcreate(['name' => 'permission_d']),
            Permission::UpdateOrcreate(['name' => 'permission_e']),
        ]);

        // With cache
        $this->app['config']->set('laratrust.cache.enabled', true);
        $this->user->hasGroup('some_group');
        $this->user->hasPermission('some_permission');

        $this->other->hasGroup('some_group');
        $this->other->hasPermission('some_permission');

        $this->assertTrue(Cache::has("laratrust_groups_for_users_{$this->user->id}"));
        $this->assertTrue(Cache::has("laratrust_permissions_for_users_{$this->user->id}"));

        $this->assertTrue(Cache::has("laratrust_groups_for_others_{$this->other->id}"));
        $this->assertTrue(Cache::has("laratrust_permissions_for_others_{$this->other->id}"));

        $this->user->flushCache();
        $this->other->flushCache();

        // Without cache
        $this->app['config']->set('laratrust.cache.enabled', false);
        $this->user->hasGroup('some_group');
        $this->user->hasPermission('some_permission');

        $this->other->hasGroup('some_group');
        $this->other->hasPermission('some_permission');

        $this->assertFalse(Cache::has("laratrust_groups_for_users_{$this->user->id}"));
        $this->assertFalse(Cache::has("laratrust_permissions_for_users_{$this->user->id}"));

        $this->assertFalse(Cache::has("laratrust_groups_for_others_{$this->other->id}"));
        $this->assertFalse(Cache::has("laratrust_permissions_for_others_{$this->other->id}"));
    }

    public function migrate()
    {
        $migrations = [
            \Laratrust\Tests\Migrations\UsersMigration::class,
            \Laratrust\Tests\Migrations\OthersMigration::class,
            \Laratrust\Tests\Migrations\LaratrustSetupTables::class,
        ];

        foreach ($migrations as $migration) {
            (new $migration)->up();
        }
    }
}
