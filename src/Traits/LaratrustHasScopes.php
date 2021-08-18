<?php

namespace Laratrust\Traits;

use Laratrust\Helper;
use Illuminate\Support\Facades\Config;

trait LaratrustHasScopes
{
    /**
     * This scope allows to retrive the users with a specific group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array<string>  $group
     * @param  mixed  $team
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereGroupIs($query, $group = '', $team = null, $boolean = 'and')
    {
        $method = $boolean == 'and' ? 'whereHas' : 'orWhereHas';

        return $query->$method('groups', function ($groupQuery) use ($group, $team) {
            $teamsStrictCheck = Config::get('laratrust.teams.strict_check');
            $method = is_array($group) ? 'whereIn' : 'where';

            $groupQuery->$method('name', $group)
                ->when($team || $teamsStrictCheck, function ($query) use ($team) {
                    $team = Helper::getIdFor($team, 'team');
                    return $query->where(Helper::teamForeignKey(), $team);
                });
        });
    }

    /**
     * This scope allows to retrive the users with a specific group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array<string>  $group
     * @param  mixed  $team
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrWhereGroupIs($query, $group = '', $team = null)
    {
        return $this->scopeWhereGroupIs($query, $group, $team, 'or');
    }

    /**
     * This scope allows to retrieve the users with a specific permission.
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array<string>  $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePermissionIs($query, $permission = '', $boolean = 'and')
    {
        $method = $boolean == 'and' ? 'where' : 'orWhere';

        return $query->$method(function ($query) use ($permission) {
            $method = is_array($permission) ? 'whereIn' : 'where';

            $query->whereHas('groups.permissions', function ($permissionQuery) use ($method, $permission) {
                $permissionQuery->$method('name', $permission);
            })->orWhereHas('permissions', function ($permissionQuery) use ($method, $permission) {
                $permissionQuery->$method('name', $permission);
            });
        });
    }

    /**
     * This scope allows to retrive the users with a specific permission.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array<string>  $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrWherePermissionIs($query, $permission = '')
    {
        return $this->scopeWherePermissionIs($query, $permission, 'or');
    }

    /**
     * Filter by the users that don't have groups assigned.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDoesntHaveGroup($query)
    {
        return $query->doesntHave('groups');
    }

    /**
     * Filter by the users that don't have permissions assigned.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDoesntHavePermission($query)
    {
        return $query->where(function ($query) {
            $query->doesntHave('permissions')
                ->orDoesntHave('groups.permissions');
        });
    }
}
