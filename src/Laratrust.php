<?php

namespace Laratrust;

/**
 * This class is the main entry point of laratrust. Usually this the interaction
 * with this class will be done through the Laratrust Facade
 *
 * @license MIT
 * @package Laratrust
 */
class Laratrust
{
    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new confide instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Checks if the current user has a group by its name.
     *
     * @param  string  $group  Group name.
     * @return bool
     */
    public function hasGroup($group, $team = null, $requireAll = false)
    {
        if ($user = $this->user()) {
            return $user->hasGroup($group, $team, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a permission by its name.
     *
     * @param  string  $permission Permission string.
     * @return bool
     */
    public function isAbleTo($permission, $team = null, $requireAll = false)
    {
        if ($user = $this->user()) {
            return $user->hasPermission($permission, $team, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a group or permission by its name.
     *
     * @param  array|string  $groups            The group(s) needed.
     * @param  array|string  $permissions      The permission(s) needed.
     * @param  array  $options                 The Options.
     * @return bool
     */
    public function ability($groups, $permissions, $team = null, $options = [])
    {
        if ($user = $this->user()) {
            return $user->ability($groups, $permissions, $team, $options);
        }

        return false;
    }

    /**
     * Checks if the user owns the thing.
     *
     * @param  Object  $thing
     * @param  string  $foreignKeyName
     * @return boolean
     */
    public function owns($thing, $foreignKeyName = null)
    {
        if ($user = $this->user()) {
            return $user->owns($thing, $foreignKeyName);
        }

        return false;
    }

    /**
     * Checks if the user has some group and if he owns the thing.
     *
     * @param  string|array  $group
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function hasGroupAndOwns($group, $thing, $options = [])
    {
        if ($user = $this->user()) {
            return $user->hasGroupAndOwns($group, $thing, $options);
        }

        return false;
    }

    /**
     * Checks if the user can do something and if he owns the thing.
     *
     * @param  string|array  $permission
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function isAbleToAndOwns($permission, $thing, $options = [])
    {
        if ($user = $this->user()) {
            return $user->isAbleToAndOwns($permission, $thing, $options);
        }

        return false;
    }

    /**
     * Get the currently authenticated user or null.
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function user()
    {
        return $this->app->auth->user();
    }
}
