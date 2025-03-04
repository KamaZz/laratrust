<?php

namespace Laratrust\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laratrust\Helper;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laratrust\Checkers\LaratrustCheckerManager;

trait LaratrustUserTrait
{
    use LaratrustHasEvents;
    use LaratrustHasScopes;

    /**
     * Boots the user model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaratrustUserTrait()
    {
        $flushCache = function ($user) {
            $user->flushCache();
        };

        // If the user doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($user) {
            if (method_exists($user, 'bootSoftDeletes') && !$user->forceDeleting) {
                return;
            }

            $user->groups()->sync([]);
            $user->permissions()->sync([]);
        });
    }

    /**
     * Many-to-Many relations with Group.
     *
     * @return belongsToMany
     */
    public function groups()
    {
        $groups = $this->belongsToMany(
            Config::get('laratrust.models.group')
        );

        if (Config::get('laratrust.teams.enabled')) {
            $groups->withPivot(Config::get('laratrust.foreign_keys.team'));
        }

        return $groups;
    }

    /**
     * Many-to-Many relations with Team associated through the groups.
     *
     * @return MorphToMany
     */
    public function groupsTeams()
    {
        if (!Config::get('laratrust.teams.enabled')) {
            return null;
        }

        return $this->morphToMany(
            Config::get('laratrust.models.team'),
            'user',
            Config::get('laratrust.tables.group_user'),
            Config::get('laratrust.foreign_keys.user'),
            Config::get('laratrust.foreign_keys.team')
        )
            ->withPivot(Config::get('laratrust.foreign_keys.group'));
    }

    /**
     * Many-to-Many relations with Team associated through the permissions user is given.
     *
     * @return MorphToMany
     */
    public function permissionsTeams()
    {
        if (!Config::get('laratrust.teams.enabled')) {
            return null;
        }

        return $this->morphToMany(
            Config::get('laratrust.models.team'),
            'user',
            Config::get('laratrust.tables.permission_user'),
            Config::get('laratrust.foreign_keys.user'),
            Config::get('laratrust.foreign_keys.team')
        )
            ->withPivot(Config::get('laratrust.foreign_keys.permission'));
    }


    /**
     * Get a collection of all user teams
     *
     * @param  array|null  $columns
     * @return \Illuminate\Support\Collection
     */
    public function allTeams($columns = null)
    {
        $columns = is_array($columns) ? $columns : ['*'];
        if ($columns) {
            $columns[] = 'id';
            $columns = array_unique($columns);
        }

        if (!Config::get('laratrust.teams.enabled')) {
            return collect([]);
        }
        $permissionTeams = $this->permissionsTeams()->get($columns);
        $groupTeams = $this->groupsTeams()->get($columns);

        return $groupTeams->merge($permissionTeams)->unique('id');
    }


    /**
     * Many-to-Many relations with Permission.
     *
     * @return MorphToMany
     */
    public function permissions()
    {
        $permissions = $this->belongsToMany(
            Config::get('laratrust.models.permission')
        );

        if (Config::get('laratrust.teams.enabled')) {
            $permissions->withPivot(Config::get('laratrust.foreign_keys.team'));
        }

        return $permissions;
    }

    /**
     * Return the right checker for the user model.
     *
     * @return \Laratrust\Checkers\User\LaratrustUserChecker
     */
    protected function laratrustUserChecker()
    {
        return (new LaratrustCheckerManager($this))->getUserChecker();
    }

    /**
     * Get the the names of the user's groups.
     *
     * @param  string|bool   $team      Team name.
     * @return bool
     */
    public function getGroups($team = null)
    {
        return $this->laratrustUserChecker()->getCurrentUserGroups($team);
    }

    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|array  $name       Group name or array of group names.
     * @param  string|bool   $team      Team name or requiredAll groups.
     * @param  bool          $requireAll All groups in the array are required.
     * @return bool
     */
    public function hasGroup($name, $team = null, $requireAll = false)
    {
        return $this->laratrustUserChecker()->currentUserHasGroup(
            $name,
            $team,
            $requireAll
        );
    }

    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|array  $name       Group name or array of group names.
     * @param  string|bool   $team      Team name or requiredAll groups.
     * @param  bool          $requireAll All groups in the array are required.
     * @return bool
     */
    public function isA($group, $team = null, $requireAll = false)
    {
        return $this->hasGroup($group, $team, $requireAll);
    }

    /**
     * Checks if the user has a group by its name.
     *
     * @param  string|array  $name       Group name or array of group names.
     * @param  string|bool   $team      Team name or requiredAll groups.
     * @param  bool          $requireAll All groups in the array are required.
     * @return bool
     */
    public function isAn($group, $team = null, $requireAll = false)
    {
        return $this->hasGroup($group, $team, $requireAll);
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll groups.
     * @param  bool  $requireAll All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $team = null, $requireAll = false)
    {
        return $this->laratrustUserChecker()->currentUserHasPermission(
            $permission,
            $team,
            $requireAll
        );
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission  Permission string or array of permissions.
     * @param  string|bool  $team  Team name or requiredAll groups.
     * @param  bool  $requireAll  All permissions in the array are required.
     * @return bool
     */
    public function isAbleTo($permission, $team = null, $requireAll = false)
    {
        return $this->hasPermission($permission, $team, $requireAll);
    }

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
    public function ability($groups, $permissions, $team = null, $options = [])
    {
        return $this->laratrustUserChecker()->currentUserHasAbility(
            $groups,
            $permissions,
            $team,
            $options
        );
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  string  $relationship
     * @param  mixed  $object
     * @param  mixed  $team
     * @return static
     */
    private function attachModel($relationship, $object, $team)
    {
        if (!Helper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }

        $attributes = [];
        $objectType = Str::singular($relationship);
        $object = Helper::getIdFor($object, $objectType);

        if (Config::get('laratrust.teams.enabled')) {
            $team = Helper::getIdFor($team, 'team');

            if (
                    $this->$relationship()
                    ->wherePivot(Helper::teamForeignKey(), $team)
                    ->wherePivot(Config::get("laratrust.foreign_keys.{$objectType}"), $object)
                    ->count()
                ) {
                return $this;
            }

            $attributes[Helper::teamForeignKey()] = $team;
        }

        $this->$relationship()->attach(
            $object,
            $attributes
        );
        $this->flushCache();
        $this->fireLaratrustEvent("{$objectType}.attached", [$this, $object, $team]);

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  string  $relationship
     * @param  mixed  $object
     * @param  mixed  $team
     * @return static
     */
    private function detachModel($relationship, $object, $team)
    {
        if (!Helper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }

        $objectType = Str::singular($relationship);
        $relationshipQuery = $this->$relationship();

        if (Config::get('laratrust.teams.enabled')) {
            $relationshipQuery->wherePivot(
                Helper::teamForeignKey(),
                Helper::getIdFor($team, 'team')
            );
        }

        $object = Helper::getIdFor($object, $objectType);
        $relationshipQuery->detach($object);

        $this->flushCache();
        $this->fireLaratrustEvent("{$objectType}.detached", [$this, $object, $team]);

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's sync() method.
     *
     * @param  string  $relationship
     * @param  mixed  $objects
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    private function syncModels($relationship, $objects, $team, $detaching)
    {
        if (!Helper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }

        $objectType = Str::singular($relationship);
        $mappedObjects = [];
        $useTeams = Config::get('laratrust.teams.enabled');
        $team = $useTeams ? Helper::getIdFor($team, 'team') : null;

        foreach ($objects as $object) {
            if ($useTeams && $team) {
                $mappedObjects[Helper::getIdFor($object, $objectType)] = [Helper::teamForeignKey() => $team];
            } else {
                $mappedObjects[] = Helper::getIdFor($object, $objectType);
            }
        }

        $relationshipToSync = $this->$relationship();

        if ($useTeams) {
            $relationshipToSync->wherePivot(Helper::teamForeignKey(), $team);
        }

        $result = $relationshipToSync->sync($mappedObjects, $detaching);

        $this->flushCache();
        $this->fireLaratrustEvent("{$objectType}.synced", [$this, $result, $team]);

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $group
     * @param  mixed  $team
     * @return static
     */
    public function attachGroup($group, $team = null)
    {
        return $this->attachModel('groups', $group, $team);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $group
     * @param  mixed  $team
     * @return static
     */
    public function detachGroup($group, $team = null)
    {
        return $this->detachModel('groups', $group, $team);
    }

    /**
     * Attach multiple groups to a user.
     *
     * @param  mixed  $groups
     * @param  mixed  $team
     * @return static
     */
    public function attachGroups($groups = [], $team = null)
    {
        foreach ($groups as $group) {
            $this->attachGroup($group, $team);
        }

        return $this;
    }

    /**
     * Detach multiple groups from a user.
     *
     * @param  mixed  $groups
     * @param  mixed  $team
     * @return static
     */
    public function detachGroups($groups = [], $team = null)
    {
        if (empty($groups)) {
            $groups = $this->groups()->get();
        }

        foreach ($groups as $group) {
            $this->detachGroup($group, $team);
        }

        return $this;
    }

    /**
     * Sync groups to the user.
     *
     * @param  array  $groups
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    public function syncGroups($groups = [], $team = null, $detaching = true)
    {
        return $this->syncModels('groups', $groups, $team, $detaching);
    }

    /**
     * Sync groups to the user without detaching.
     *
     * @param  array  $groups
     * @param  mixed  $team
     * @return static
     */
    public function syncGroupsWithoutDetaching($groups = [], $team = null)
    {
        return $this->syncGroups($groups, $team, false);
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function attachPermission($permission, $team = null)
    {
        return $this->attachModel('permissions', $permission, $team);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function detachPermission($permission, $team = null)
    {
        return $this->detachModel('permissions', $permission, $team);
    }

    /**
     * Attach multiple permissions to a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function attachPermissions($permissions = [], $team = null)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission, $team);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function detachPermissions($permissions = [], $team = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission, $team);
        }

        return $this;
    }

    /**
     * Sync permissions to the user.
     *
     * @param  array  $permissions
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    public function syncPermissions($permissions = [], $team = null, $detaching = true)
    {
        return $this->syncModels('permissions', $permissions, $team, $detaching);
    }

    /**
     * Sync permissions to the user without detaching.
     *
     * @param  array  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function syncPermissionsWithoutDetaching($permissions = [], $team = null)
    {
        return $this->syncPermissions($permissions, $team, false);
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
        if ($thing instanceof \Laratrust\Contracts\Ownable) {
            $ownerKey = $thing->ownerKey($this);
        } else {
            $className = (new \ReflectionClass($this))->getShortName();
            $foreignKeyName = $foreignKeyName ?: Str::snake($className . 'Id');
            $ownerKey = $thing->$foreignKeyName;
        }

        return $ownerKey == $this->getKey();
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
        $options = Helper::checkOrSet('requireAll', $options, [false, true]);
        $options = Helper::checkOrSet('team', $options, [null]);
        $options = Helper::checkOrSet('foreignKeyName', $options, [null]);

        return $this->hasGroup($group, $options['team'], $options['requireAll'])
                && $this->owns($thing, $options['foreignKeyName']);
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
        $options = Helper::checkOrSet('requireAll', $options, [false, true]);
        $options = Helper::checkOrSet('foreignKeyName', $options, [null]);
        $options = Helper::checkOrSet('team', $options, [null]);

        return $this->hasPermission($permission, $options['team'], $options['requireAll'])
                && $this->owns($thing, $options['foreignKeyName']);
    }

    /**
     * Return all the user permissions.
     * if $team param is false it ignores teams
     *
     * @param  null|array  $columns
     * @param  null|false $team
     * @return \Illuminate\Support\Collection|static
     */
    public function allPermissions($columns = null, $team = false)
    {
        $columns = is_array($columns) ? $columns : null;
        if ($columns) {
            $columns[] = 'id';
            $columns = array_unique($columns);
        }
        $withColumns = $columns ? ":" . implode(',', $columns) : '';

        $groups = $this->groups()
            ->when(config('laratrust.teams.enabled') && $team !== false, function ($query) use ($team) {
                return $query->whereHas('permissions', function ($permissionQuery) use ($team) {
                    $permissionQuery->where(config('laratrust.foreign_keys.team'), Helper::getIdFor($team, 'team'));
                });
            })
            ->with("permissions{$withColumns}")->get();

        $groupsPermissions = $groups->flatMap(function ($group) {
            return $group->permissions;
        });

        $directPermissions = $this->permissions()
            ->when(config('laratrust.teams.enabled') && $team !== false, function ($query) use ($team) {
                $query->where(config('laratrust.foreign_keys.team'), Helper::getIdFor($team, 'team'));
            });

        return $directPermissions->get($columns ?? ['*'])->merge($groupsPermissions)->unique('id');
    }

    /**
     * Flush the user's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        return $this->laratrustUserChecker()->currentUserFlushCache();
    }

    /**
     * Handles the call to the magic methods with can,
     * like $user->isAbleToEditSomething().
     * @param  string  $method
     * @param  array  $parameters
     * @return boolean
     */
    private function handleMagicIsAbleTo($method, $parameters)
    {
        $case = str_replace('_case', '', Config::get('laratrust.magic_is_able_to_method_case'));
        $method = preg_replace('/^isAbleTo/', '', $method);

        if ($case == 'kebab') {
            $permission = Str::snake($method, '-');
        } else {
            $permission = Str::$case($method);
        }

        return $this->hasPermission($permission, array_shift($parameters), false);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!preg_match('/^isAbleTo[A-Z].*/', $method)) {
            return parent::__call($method, $parameters);
        }

        return $this->handleMagicIsAbleTo($method, $parameters);
    }
}
