<?php

namespace Laratrust\Traits;

use Illuminate\Support\Facades\Config;

trait LaratrustPermissionTrait
{
    use LaratrustDynamicUserRelationsCalls;

    /**
     * Boots the permission model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaratrustPermissionTrait()
    {
        static::deleting(function ($permission) {
            if (!method_exists(Config::get('laratrust.models.permission'), 'bootSoftDeletes')) {
                $permission->groups()->sync([]);
            }
        });

        static::deleting(function ($permission) {
            if (method_exists($permission, 'bootSoftDeletes') && !$permission->forceDeleting) {
                return;
            }

            $permission->groups()->sync([]);

            foreach (array_keys(Config::get('laratrust.user_models')) as $key) {
                $permission->$key()->sync([]);
            }
        });
    }

    /**
     * Many-to-Many relations with group model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(
            Config::get('laratrust.models.group'),
            Config::get('laratrust.tables.permission_group'),
            Config::get('laratrust.foreign_keys.permission'),
            Config::get('laratrust.foreign_keys.group')
        );
    }

    /**
     * Morph by Many relationship between the permission and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getMorphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laratrust.user_models')[$relationship],
            'user',
            Config::get('laratrust.tables.permission_user'),
            Config::get('laratrust.foreign_keys.permission'),
            Config::get('laratrust.foreign_keys.user')
        );
    }
}
