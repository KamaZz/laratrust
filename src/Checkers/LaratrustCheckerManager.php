<?php

namespace Laratrust\Checkers;

use Illuminate\Support\Facades\Config;
use Laratrust\Checkers\Group\LaratrustGroupQueryChecker;
use Laratrust\Checkers\User\LaratrustUserQueryChecker;
use Laratrust\Checkers\Group\LaratrustGroupDefaultChecker;
use Laratrust\Checkers\User\LaratrustUserDefaultChecker;

class LaratrustCheckerManager
{
    /**
     * The object in charge of checking the groups and permissions.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Return the right checker according to the configuration.
     *
     * @return \Laratrust\Checkers\LaratrustChecker
     */
    public function getUserChecker()
    {
        switch (Config::get('laratrust.checker', 'default')) {
            case 'default':
                return new LaratrustUserDefaultChecker($this->model);
            case 'query':
                return new LaratrustUserQueryChecker($this->model);
        }
    }

    /**
     * Return the right checker according to the configuration.
     *
     * @return \Laratrust\Checkers\LaratrustChecker
     */
    public function getGroupChecker()
    {
        switch (Config::get('laratrust.checker', 'default')) {
            case 'default':
                return new LaratrustGroupDefaultChecker($this->model);
            case 'query':
                return new LaratrustGroupQueryChecker($this->model);
        }
    }
}
