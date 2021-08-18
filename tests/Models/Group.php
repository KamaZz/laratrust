<?php

namespace Laratrust\Tests\Models;

use Laratrust\Models\LaratrustGroup;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends LaratrustGroup
{
    use SoftDeletes;

    protected $guarded = [];
}
