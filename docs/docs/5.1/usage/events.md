# Events

Laratrust comes with an events system that works like the Laravel [model events](https://laravel.com/docs/eloquent#events). The events that you can listen to are **groupAttached**, **groupDetached**, **permissionAttached**, **permissionDetached**, **groupSynced**, **permissionSynced**.

::: tip NOTE
Inside the Group model only the **permissionAttached**, **permissionDetached** and **permissionSynced** events will be fired.
:::

If you want to listen to a Laratrust event, inside your `User` or `Group` models put this:

```php
<?php

namespace App;

use Laratrust\Traits\LaratrustUserTrait;

class User extends Model
{
    use LaratrustUserTrait;

    public static function boot() {
        parent::boot();

        static::groupAttached(function($user, $group, $team) {
        });
        static::groupSynced(function($user, $changes, $team) {
        });
    }
}
```

::: tip NOTE
The `$team` parameter is optional and if you are not using teams, it will be set to null.
:::

The eventing system also supports observable classes:

```php
<?php

namespace App\Observers;

use App\User;

class UserObserver
{

    public function groupAttached(User $user, $group, $team)
    {
        //
    }

    public function groupSynced(User $user, $changes, $team)
    {
        //
    }
}
```

To register an observer, use the laratrustObserve method on the model you wish to observe. You may register observers in the boot method of one of your service providers. In this example, we'll register the observer in the AppServiceProvider:

```php
<?php

namespace App\Providers;

use App\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        User::laratrustObserve(UserObserver::class);
    }

    ...
}
```

::: tip NOTE
Inside your observable classes you can have your normal model events methods alongside Laratrust's events methods.
:::

## Available Events


### User Events

- `groupAttached($user, $group, $team = null)`
    - `$user`: The user to whom the group was attached.
    - `$group`: The group id that was attached to the `$user`.
    - `$team`: The team id that was used to attach the group to the `$user`.

- `groupDetached($user, $group, $team = null)`
    - `$user`: The user to whom the group was detached.
    - `$group`: The group id that was detached from the `$user`.
    - `$team`: The team id that was used to detach the group from the `$user`.

- `permissionAttached($user, $permission, $team = null)`
    - `$user`: The user to whom the permission was attached.
    - `$permission`: The permission id that was attached to the `$user`.
    - `$team`: The team id that was used to attach the permission to the `$user`.

- `permissionDetached($user, $permission, $team = null)`
    - `$user`: The user to whom the permission was detached.
    - `$permission`: The permission id that was detached from the `$user`.
    - `$team`: The team id that was used to detach the permission from the `$user`.

- `groupSynced($user, $changes, $team)`
    - `$user`: The user to whom the groups were synced.
    - `$changes`: The value returned by the eloquent `sync` method containing the changes made in the database.
    - `$team`: The team id that was used to sync the groups to the user.

- `permissionSynced()`
    - `$user`: The user to whom the permissions were synced.
    - `$changes`: The value returned by the eloquent `sync` method containing the changes made in the database.
    - `$team`: The team id that was used to sync the permissions to the user.

### Group Events

- `permissionAttached($group, $permission)`
    - `$group`: The group to whom the permission was attached.
    - `$permission`: The permission id that was attached to the `$group`.

- `permissionDetached($group, $permission)`
    - `$group`: The group to whom the permission was detached.
    - `$permission`: The permission id that was detached from the `$group`.

- `permissionSynced()`
    - `$group`: The group to whom the permissions were synced.
    - `$changes`: The value returned by the eloquent `sync` method containing the changes made in the database.

