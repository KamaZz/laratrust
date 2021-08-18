<?php

namespace Laratrust\Tests\Models;

class UserObserver
{
    public function groupAttached($user, $thing, $team)
    {
    }

    public function groupDetached($user, $thing, $team)
    {
    }

    public function permissionAttached($user, $thing, $team)
    {
    }

    public function permissionDetached($user, $thing, $team)
    {
    }

    public function groupSynced($user, $thing, $team)
    {
    }

    public function permissionSynced($user, $thing, $team)
    {
    }
}
