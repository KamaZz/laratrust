<?php

use Mockery as m;
use Laratrust\Laratrust;
use Laratrust\Tests\LaratrustTestCase;

class LaratrustTest extends LaratrustTestCase
{
    protected $laratrust;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->laratrust = m::mock('Laratrust\Laratrust[user]', [$this->app]);
        $this->user = m::mock('_mockedUser');
    }

    public function testHasGroup()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasGroup')->with('UserGroup', null, false)->andReturn(true)->once();
        $this->user->shouldReceive('hasGroup')->with('NonUserGroup', null, false)->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->hasGroup('UserGroup'));
        $this->assertFalse($this->laratrust->hasGroup('NonUserGroup'));
        $this->assertFalse($this->laratrust->hasGroup('AnyGroup'));
    }

    public function testIsAbleTo()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasPermission')->with('user_can', null, false)->andReturn(true)->once();
        $this->user->shouldReceive('hasPermission')->with('user_cannot', null, false)->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->isAbleTo('user_can'));
        $this->assertFalse($this->laratrust->isAbleTo('user_cannot'));
        $this->assertFalse($this->laratrust->isAbleTo('any_permission'));
    }

    public function testAbility()
    {
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('ability')->with('admin', 'user_can', null, [])->andReturn(true)->once();
        $this->user->shouldReceive('ability')->with('admin', 'user_cannot', null, [])->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->ability('admin', 'user_can'));
        $this->assertFalse($this->laratrust->ability('admin', 'user_cannot'));
        $this->assertFalse($this->laratrust->ability('any_group', 'any_permission'));
    }

    public function testUserOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->twice()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('owns')->with($postModel, null)->andReturn(true)->once();
        $this->user->shouldReceive('owns')->with($postModel, 'UserId')->andReturn(false)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->owns($postModel, null));
        $this->assertFalse($this->laratrust->owns($postModel, 'UserId'));
        $this->assertFalse($this->laratrust->owns($postModel, 'UserId'));
    }

    public function testUserHasGroupAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->once()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('hasGroupAndOwns')->with('admin', $postModel, [])->andReturn(true)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->hasGroupAndOwns('admin', $postModel));
        $this->assertFalse($this->laratrust->hasGroupAndOwns('admin', $postModel));
    }

    public function testUserIsAbleToAndOwnsaPostModel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $postModel = m::mock('SomeObject');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $this->laratrust->shouldReceive('user')->andReturn($this->user)->once()->ordered();
        $this->laratrust->shouldReceive('user')->andReturn(false)->once()->ordered();
        $this->user->shouldReceive('isAbleToAndOwns')->with('update-post', $postModel, [])->andReturn(true)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($this->laratrust->isAbleToAndOwns('update-post', $postModel));
        $this->assertFalse($this->laratrust->isAbleToAndOwns('update-post', $postModel));
    }

    public function testUser()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $this->laratrust = new Laratrust($this->app);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($this->user)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($this->user, $this->laratrust->user());
    }
}
