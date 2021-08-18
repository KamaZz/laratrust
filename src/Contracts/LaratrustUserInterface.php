<?php

namespace Laratrust\Contracts;

interface LaratrustUserInterface
{
    /**
     * Many-to-Many relations with Group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function groups();

    /**
     * Many-to-Many relations with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions();

    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|array  $name       Group name or array of group names.
     * @param  string|bool   $team      Team name or requiredAll groups.
     * @param  bool          $requireAll All groups in the array are required.
     * @return bool
     */
    public function hasGroup($name, $team = null, $requireAll = false);

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll groups.
     * @param  bool  $requireAll All groups in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $team = null, $requireAll = false);

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission  Permission string or array of permissions.
     * @param  string|bool  $team  Team name or requiredAll groups.
     * @param  bool  $requireAll  All permissions in the array are required.
     * @return bool
     */
    public function isAbleTo($permission, $team = null, $requireAll = false);

    /**
     * Checks group(s) and permission(s).
     *
     * @param  string|array  $groups       Array of groups or comma separated string
     * @param  string|array  $permissions Array of permissions or comma separated string.
     * @param  string|bool  $team      Team name or requiredAll groups.
     * @param  array  $options     validate_all (true|false) or return_type (boolean|array|both)
     * @throws \InvalidArgumentException
     * @return array|bool
     */
    public function ability($groups, $permissions, $team = null, $options = []);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $group
     * @param  mixed  $team
     * @return static
     */
    public function attachGroup($group, $team = null);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $group
     * @param  mixed  $team
     * @return static
     */
    public function detachGroup($group, $team = null);

    /**
     * Attach multiple groups to a user.
     *
     * @param  mixed  $groups
     * @param  mixed  $team
     * @return static
     */
    public function attachGroups($groups = [], $team = null);

    /**
     * Detach multiple groups from a user.
     *
     * @param  mixed  $groups
     * @param  mixed  $team
     * @return static
     */
    public function detachGroups($groups = [], $team = null);

    /**
     * Sync groups to the user.
     *
     * @param  array  $groups
     * @param  mixed  $team
     * @return static
     */
    public function syncGroups($groups = [], $team = null);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function attachPermission($permission, $team = null);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function detachPermission($permission, $team = null);

    /**
     * Attach multiple permissions to a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function attachPermissions($permissions = [], $team = null);

    /**
     * Detach multiple permissions from a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function detachPermissions($permissions = [], $team = null);

    /**
     * Sync groups to the user.
     *
     * @param  array  $permissions
     * @return static
     */
    public function syncPermissions($permissions = [], $team = null);

    /**
     * Checks if the user owns the thing.
     *
     * @param  Object  $thing
     * @param  string  $foreignKeyName
     * @return boolean
     */
    public function owns($thing);

    /**
     * Checks if the user has some group and if he owns the thing.
     *
     * @param  string|array  $group
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function hasGroupAndOwns($group, $thing, $options = []);

    /**
     * Checks if the user can do something and if he owns the thing.
     *
     * @param  string|array  $permission
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function isAbleToAndOwns($permission, $thing, $options = []);

    /**
     * Return all the user permissions.
     *
     * @return boolean
     */
    public function allPermissions();
}
