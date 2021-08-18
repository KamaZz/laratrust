<?php

namespace Laratrust\Tests\Checkers\User;

class LaratrustUserDefaultCheckerTest extends LaratrustUserCheckerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('laratrust.checker', 'default');
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

    public function testUserDisableTheGroupsAndPermissionsCaching()
    {
        $this->userDisableTheGroupsAndPermissionsCachingAssertions();
    }
}
