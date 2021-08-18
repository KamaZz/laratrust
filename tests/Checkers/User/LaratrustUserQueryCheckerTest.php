<?php

namespace Laratrust\Tests\Checkers\User;

use Illuminate\Support\Facades\Config;

class LaratrustUserQueryCheckerTest extends LaratrustUserCheckerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('laratrust.checker', 'query');
    }

    public function testGetGroups()
    {
        $this->getGroupsAssertions();
    }

    public function testHasGroup()
    {
        $this->hasGroupAssertions();
    }

    public function testHasPermission()
    {
        $this->hasPermissionAssertions();
    }

    public function testHasPermissionWithPlaceholderSupport()
    {
        $this->hasPermissionWithPlaceholderSupportAssertions();
    }

    // public function testUserCanDisableTheGroupsAndPermissionsCaching()
    // {
    //     $this->userCanDisableTheGroupsAndPermissionsCachingAssertions();
    // }
}
