<?php

namespace Laratrust\Tests\Checkers\User;

use Laratrust\Tests\Models\Group;
use Laratrust\Tests\Models\User;
use Laratrust\Tests\LaratrustTestCase;
use Laratrust\Tests\Models\Permission;

class LaratrustUserCanCheckerTestCase extends LaratrustTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();

        $permissionA = Permission::create(['name' => 'permission_a']);
        $permissionB = Permission::create(['name' => 'permission_b']);

        $group = Group::create(['name' => 'group']);

        $group->attachPermissions([$permissionA, $permissionB]);

        $this->user = User::create(['name' => 'test', 'email' => 'test@test.com']);
        $this->user->attachGroup($group);
    }

    protected function canShouldReturnBooleanAssertions()
    {
        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Case: User has everything.
        $this->assertTrue(
            $this->user->can(
                ['permission_a', 'permission_b']
            )
        );

        // Case: User lacks a permission.
        if (method_exists($this->user, 'canAny')) {
            $this->assertTrue(
                $this->user->canAny(
                    ['user_cannot_a', 'permission_b']
                )
            );
        }
        $this->assertFalse(
            $this->user->can(
                ['user_cannot_a', 'permission_b']
            )
        );

        // Case: User lacks everything.
        $this->assertFalse(
            $this->user->can(
                ['user_cannot_a', 'user_cannot_b']
            )
        );
        if (method_exists($this->user, 'canAny')) {
            $this->assertFalse(
                $this->user->canAny(
                    ['user_cannot_a', 'user_cannot_b']
                )
            );
        }
    }
}
