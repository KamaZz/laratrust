<?php

namespace Laratrust\Checkers\User;

use Laratrust\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class LaratrustUserDefaultChecker extends LaratrustUserChecker
{
    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|bool   $team      Team name.
     * @return array
     */
    public function getCurrentUserGroups($team = null)
    {
        $groups = collect($this->userCachedGroups());

        if (config('laratrust.teams.enabled') === false) {
            return $groups->pluck('name')->toArray();
        }

        if ($team === null && config('laratrust.teams.strict_check') === false) {
            return $groups->pluck('name')->toArray();
        }

        if ($team === null) {
            return $groups->filter(function ($group) {
                return $group['pivot'][config('laratrust.foreign_keys.team')] === null;
            })->pluck('name')->toArray();
        }

        $teamId = Helper::fetchTeam($team);

        return $groups->filter(function ($group) use ($teamId) {
            return $group['pivot'][config('laratrust.foreign_keys.team')] == $teamId;
        })->pluck('name')->toArray();
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
        $name = Helper::standardize($name);
        list($team, $requireAll) = Helper::assignRealValuesTo($team, $requireAll, 'is_bool');

        if (is_array($name)) {
            if (empty($name)) {
                return true;
            }

            foreach ($name as $groupName) {
                $hasGroup = $this->currentUserHasGroup($groupName, $team);

                if ($hasGroup && !$requireAll) {
                    return true;
                } elseif (!$hasGroup && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the groups were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the groups were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        $team = Helper::fetchTeam($team);

        foreach ($this->userCachedGroups() as $group) {
            if ($group['name'] == $name && Helper::isInSameTeam($group, $team)) {
                return true;
            }
        }

        return false;
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
        $permission = Helper::standardize($permission);
        list($team, $requireAll) = Helper::assignRealValuesTo($team, $requireAll, 'is_bool');

        if (is_array($permission)) {
            if (empty($permission)) {
                return true;
            }

            foreach ($permission as $permissionName) {
                $hasPermission = $this->currentUserHasPermission($permissionName, $team);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        $team = Helper::fetchTeam($team);

        foreach ($this->userCachedPermissions() as $perm) {
            if (Helper::isInSameTeam($perm, $team) && Str::is($permission, $perm['name'])) {
                return true;
            }
        }

        foreach ($this->userCachedGroups() as $group) {
            $group = Helper::hidrateModel(Config::get('laratrust.models.group'), $group);

            if (Helper::isInSameTeam($group, $team) && $group->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function currentUserFlushCache()
    {
        Cache::forget('laratrust_groups_for_'.$this->userModelCacheKey() .'_'. $this->user->getKey());
        Cache::forget('laratrust_permissions_for_'.$this->userModelCacheKey() .'_'. $this->user->getKey());
    }

    /**
     * Tries to return all the cached groups of the user.
     * If it can't bring the groups from the cache,
     * it brings them back from the DB.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function userCachedGroups()
    {
        $cacheKey = 'laratrust_groups_for_'.$this->userModelCacheKey() .'_'. $this->user->getKey();

        if (!Config::get('laratrust.cache.enabled')) {
            return $this->user->groups()->get();
        }

        return Cache::remember($cacheKey, Config::get('laratrust.cache.expiration_time', 60), function () {
            return $this->user->groups()->get()->toArray();
        });
    }

    /**
     * Tries to return all the cached permissions of the user
     * and if it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function userCachedPermissions()
    {
        $cacheKey = 'laratrust_permissions_for_'.$this->userModelCacheKey() .'_'. $this->user->getKey();

        if (!Config::get('laratrust.cache.enabled')) {
            return $this->user->permissions()->get();
        }

        return Cache::remember($cacheKey, Config::get('laratrust.cache.expiration_time', 60), function () {
            return $this->user->permissions()->get()->toArray();
        });
    }

    /**
     * Tries return key name for user_models
     *
     * @return string default key user
     */
    public function userModelCacheKey()
    {
        foreach (Config::get('laratrust.user_models') as $key => $model) {
            if ($this->user instanceof $model) {
                return $key;
            }
        }
    }
}
