<?php

namespace Laratrust\Traits;

use Laratrust\Helper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laratrust\Checkers\LaratrustCheckerManager;

trait LaratrustGroupTrait
{
    use LaratrustDynamicUserRelationsCalls;
    use LaratrustHasEvents;

    /**
     * Boots the group model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the group model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaratrustGroupTrait()
    {
        $flushCache = function ($group) {
            $group->flushCache();
        };

        // If the group doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($group) {
            if (method_exists($group, 'bootSoftDeletes') && !$group->forceDeleting) {
                return;
            }

            $group->permissions()->sync([]);

            foreach (array_keys(Config::get('laratrust.user_models')) as $key) {
                $group->$key()->sync([]);
            }
        });
    }

    /**
     * Return the right checker for the group model.
     *
     * @return \Laratrust\Checkers\Group\LaratrustGroupChecker
     */
    protected function laratrustGroupChecker()
    {
        return (new LaratrustCheckerManager($this))->getGroupChecker();
    }

    /**
     * Morph by Many relationship between the group and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laratrust.user_models')[$relationship],
            'user',
            Config::get('laratrust.tables.group_user'),
            Config::get('laratrust.foreign_keys.group'),
            Config::get('laratrust.foreign_keys.user')
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Config::get('laratrust.models.permission'),
            Config::get('laratrust.tables.permission_group'),
            Config::get('laratrust.foreign_keys.group'),
            Config::get('laratrust.foreign_keys.permission')
        );
    }

    /**
     * Checks if the group has a permission by its name.
     *
     * @param  string|array  $permission       Permission name or array of permission names.
     * @param  bool  $requireAll       All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false)
    {
        return $this->laratrustGroupChecker($this)
            ->currentGroupHasPermission($permission, $requireAll);
    }

    /**
     * Save the inputted permissions.
     *
     * @param  mixed  $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        $mappedPermissions = [];

        foreach ($permissions as $permission) {
            $mappedPermissions[] = Helper::getIdFor($permission, 'permission');
        }

        $changes = $this->permissions()->sync($mappedPermissions);
        $this->flushCache();
        $this->fireLaratrustEvent("permission.synced", [$this, $changes]);

        return $this;
    }

    /**
     * Attach permission to current group.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function attachPermission($permission)
    {
        $permission = Helper::getIdFor($permission, 'permission');

        $this->permissions()->attach($permission);
        $this->flushCache();
        $this->fireLaratrustEvent("permission.attached", [$this, $permission]);

        return $this;
    }

    /**
     * Detach permission from current group.
     *
     * @param  object|array  $permission
     * @return void
     */
    public function detachPermission($permission)
    {
        $permission = Helper::getIdFor($permission, 'permission');

        $this->permissions()->detach($permission);
        $this->flushCache();
        $this->fireLaratrustEvent("permission.detached", [$this, $permission]);

        return $this;
    }

    /**
     * Attach multiple permissions to current group.
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current group
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function detachPermissions($permissions = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }

        return $this;
    }

    /**
     * Flush the group's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        return $this->laratrustGroupChecker()->currentGroupFlushCache();
    }
}
