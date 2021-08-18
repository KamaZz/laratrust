<?php

namespace Laratrust\Checkers\User;

use Laratrust\Helper;
use Illuminate\Support\Facades\Config;

class LaratrustUserQueryChecker extends LaratrustUserChecker
{
    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|bool   $team      Team name.
     * @return array
     */
    public function getCurrentUserGroups($team = null)
    {
        if (config('laratrust.teams.enabled') === false) {
            return $this->user->groups->pluck('name')->toArray();
        }

        if ($team === null && config('laratrust.teams.strict_check') === false) {
            return $this->user->groups->pluck('name')->toArray();
        }

        if ($team === null) {
            return $this->user
                ->groups()
                ->wherePivot(config('laratrust.foreign_keys.team'), null)
                ->pluck('name')
                ->toArray();
        }

        $teamId = Helper::fetchTeam($team);

        return $this->user
            ->groups()
            ->wherePivot(config('laratrust.foreign_keys.team'), $teamId)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|array  $name       Group name or array of group names.
     * @param  string|bool   $team      Team name or requiredAll groups.
     * @param  bool          $requireAll All groups in the array are required.
     * @return bool
     */
    public function currentUserHasGroup($name, $team = null, $requireAll = false)
    {
        if (empty($name)) {
            return true;
        }

        $name = Helper::standardize($name);
        $groupsNames = is_array($name) ? $name : [$name];
        list($team, $requireAll) = Helper::assignRealValuesTo($team, $requireAll, 'is_bool');
        $useTeams = Config::get('laratrust.teams.enabled');
        $teamStrictCheck = Config::get('laratrust.teams.strict_check');

        $groupsCount = $this->user->groups()
            ->whereIn('name', $groupsNames)
            ->when($useTeams && ($teamStrictCheck || !is_null($team)), function ($query) use ($team) {
                $teamId = Helper::fetchTeam($team);

                return $query->where(Config::get('laratrust.foreign_keys.team'), $teamId);
            })
            ->count();

        return $requireAll ? $groupsCount == count($groupsNames) : $groupsCount > 0;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll groups.
     * @param  bool  $requireAll All groups in the array are required.
     * @return bool
     */
    public function currentUserHasPermission($permission, $team = null, $requireAll = false)
    {
        if (empty($permission)) {
            return true;
        }

        $permission = Helper::standardize($permission);
        $permissionsNames = is_array($permission) ? $permission : [$permission];
        list($team, $requireAll) = Helper::assignRealValuesTo($team, $requireAll, 'is_bool');
        $useTeams = Config::get('laratrust.teams.enabled');
        $teamStrictCheck = Config::get('laratrust.teams.strict_check');

        list($permissionsWildcard, $permissionsNoWildcard) =
            Helper::getPermissionWithAndWithoutWildcards($permissionsNames);

        $groupsPermissionsCount = $this->user->groups()
            ->withCount(['permissions' =>
                function ($query) use ($permissionsNoWildcard, $permissionsWildcard) {
                    $query->whereIn('name', $permissionsNoWildcard);
                    foreach ($permissionsWildcard as $permission) {
                        $query->orWhere('name', 'like', $permission);
                    }
                }
            ])
            ->when($useTeams && ($teamStrictCheck || !is_null($team)), function ($query) use ($team) {
                $teamId = Helper::fetchTeam($team);

                return $query->where(Config::get('laratrust.foreign_keys.team'), $teamId);
            })
            ->pluck('permissions_count')
            ->sum();

        $directPermissionsCount = $this->user->permissions()
            ->whereIn('name', $permissionsNoWildcard)
            ->when($permissionsWildcard, function ($query) use ($permissionsWildcard) {
                foreach ($permissionsWildcard as $permission) {
                    $query->orWhere('name', 'like', $permission);
                }

                return $query;
            })
            ->when($useTeams && ($teamStrictCheck || !is_null($team)), function ($query) use ($team) {
                $teamId = Helper::fetchTeam($team);

                return $query->where(Config::get('laratrust.foreign_keys.team'), $teamId);
            })
            ->count();

        return $requireAll
            ? $groupsPermissionsCount + $directPermissionsCount >= count($permissionsNames)
            : $groupsPermissionsCount + $directPermissionsCount > 0;
    }

    public function currentUserFlushCache()
    {
    }
}
