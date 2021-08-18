<?php

namespace Laratrust\Checkers\Group;

use Illuminate\Database\Eloquent\Model;

abstract class LaratrustGroupChecker
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $group;

    public function __construct(Model $group)
    {
        $this->group = $group;
    }

    abstract public function currentGroupHasPermission($permission, $requireAll = false);

    abstract public function currentGroupFlushCache();
}
