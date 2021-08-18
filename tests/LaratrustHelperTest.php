<?php

namespace Laratrust\Test;

use Laratrust\Helper;
use Laratrust\Tests\Models\Group;
use Illuminate\Support\Facades\Config;
use Laratrust\Tests\LaratrustTestCase;

class LaratrustHelperTest extends LaratrustTestCase
{
    protected $superadmin;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->superadmin = Group::create(['name' => 'superadmin']);
        $this->admin = Group::create(['name' => 'admin']);
    }

    public function testIfGroupIsEditable()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        Config::set('laratrust.panel.groups_restrictions.not_editable', ['superadmin']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse(Helper::groupIsEditable($this->superadmin));
        $this->assertFalse(Helper::groupIsEditable($this->superadmin->name));
        $this->assertTrue(Helper::groupIsEditable($this->admin));
        $this->assertTrue(Helper::groupIsEditable($this->admin->name));
    }

    public function testGroupIsDeletable()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        Config::set('laratrust.panel.groups_restrictions.not_deletable', ['superadmin']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse(Helper::groupIsDeletable($this->superadmin));
        $this->assertFalse(Helper::groupIsDeletable($this->superadmin->name));
        $this->assertTrue(Helper::groupIsDeletable($this->admin));
        $this->assertTrue(Helper::groupIsDeletable($this->admin->name));
    }

    public function testGroupIsRemovable()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        Config::set('laratrust.panel.groups_restrictions.not_removable', ['superadmin']);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse(Helper::groupIsRemovable($this->superadmin));
        $this->assertFalse(Helper::groupIsRemovable($this->superadmin->name));
        $this->assertTrue(Helper::groupIsRemovable($this->admin));
        $this->assertTrue(Helper::groupIsRemovable($this->admin->name));
    }
}
