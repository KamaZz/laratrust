<?php

namespace Laratrust\Checkers\User;

use Laratrust\Helper;
use Illuminate\Database\Eloquent\Model;

abstract class LaratrustUserChecker
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    public function __construct(Model $user)
    {
        $this->user = $user;
    }

    abstract public function currentUserHasGroup($name, $team = null, $requireAll = false);

    abstract public function currentUserHasPermission($permission, $team = null, $requireAll = false);

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
    public function currentUserHasAbility($groups, $permissions, $team = null, $options = [])
    {
        list($team, $options) = Helper::assignRealValuesTo($team, $options, 'is_array');
        // Convert string to array if that's what is passed in.
        $groups = Helper::standardize($groups, true);
        $permissions = Helper::standardize($permissions, true);

        // Set up default values and validate options.
        $options = Helper::checkOrSet('validate_all', $options, [false, true]);
        $options = Helper::checkOrSet('return_type', $options, ['boolean', 'array', 'both']);

        if ($options['return_type'] == 'boolean') {
            $hasGroups = $this->currentUserHasGroup($groups, $team, $options['validate_all']);
            $hasPermissions = $this->currentUserHasPermission($permissions, $team, $options['validate_all']);

            return $options['validate_all']
                ? $hasGroups && $hasPermissions
                : $hasGroups || $hasPermissions;
        }

        // Loop through groups and permissions and check each.
        $checkedGroups = [];
        $checkedPermissions = [];
        foreach ($groups as $group) {
            $checkedGroups[$group] = $this->currentUserHasGroup($group, $team);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->currentUserHasPermission($permission, $team);
        }

        // If validate all and there is a false in either.
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedGroups) || in_array(false, $checkedPermissions))) || (!$options['validate_all'] && (in_array(true, $checkedGroups) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option.
        if ($options['return_type'] == 'array') {
            return ['groups' => $checkedGroups, 'permissions' => $checkedPermissions];
        }

        return [$validateAll, ['groups' => $checkedGroups, 'permissions' => $checkedPermissions]];
    }

    abstract public function currentUserFlushCache();
}
