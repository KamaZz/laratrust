<?php

namespace Laratrust\Checkers\Group;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class LaratrustGroupDefaultChecker extends LaratrustGroupChecker
{
    /**
     * Checks if the group has a permission by its name.
     *
     * @param  string|array  $permission       Permission name or array of permission names.
     * @param  bool  $requireAll       All permissions in the array are required.
     * @return bool
     */
    public function currentGroupHasPermission($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            if (empty($permission)) {
                return true;
            }

            foreach ($permission as $permissionName) {
                $hasPermission = $this->currentGroupHasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll.
            return $requireAll;
        }

        foreach ($this->currentGroupCachedPermissions() as $perm) {
            if (Str::is($permission, $perm['name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Flush the group's cache.
     *
     * @return void
     */
    public function currentGroupFlushCache()
    {
        Cache::forget('laratrust_permissions_for_group_' . $this->group->getKey());
    }

    /**
     * Tries to return all the cached permissions of the group.
     * If it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function currentGroupCachedPermissions()
    {
        $cacheKey = 'laratrust_permissions_for_group_' . $this->group->getKey();

        if (!Config::get('laratrust.cache.enabled')) {
            return $this->group->permissions()->get();
        }

        return Cache::remember($cacheKey, Config::get('laratrust.cache.expiration_time', 60), function () {
            return $this->group->permissions()->get()->toArray();
        });
    }
}
