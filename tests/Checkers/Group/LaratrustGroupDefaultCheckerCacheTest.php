<?php

namespace Laratrust\Test\Checkers\Group;

use Laratrust\Tests\Models\Group;
use Illuminate\Support\Facades\Cache;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustGroupDefaultCheckerCacheTest extends LaratrustTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
    }

    public function testUserDisableTheGroupsAndPermissionsCaching()
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

        // With cache
        $this->app['config']->set('laratrust.cache.enabled', true);
        $group->hasPermission('some_permission');
        $this->assertTrue(Cache::has("laratrust_permissions_for_group_{$group->id}"));
        $group->flushCache();

        // Without cache
        $this->app['config']->set('laratrust.cache.enabled', false);
        $group->hasPermission('some_permission');
        $this->assertFalse(Cache::has("laratrust_permissions_for_group_{$group->id}"));
    }
}
