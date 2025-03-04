---
sidebarDepth: 2
---

::: warning WARNING
You are using an old version of Laratrust. Consider updating to the <docs-link to="/installation.html" current-version>latest</docs-link> version
:::

# Concepts

## Set things up
Let's start by creating the following `Group`s:

```php
$owner = new Group();
$owner->name         = 'owner';
$owner->display_name = 'Project Owner'; // optional
$owner->description  = 'User is the owner of a given project'; // optional
$owner->save();

$admin = new Group();
$admin->name         = 'admin';
$admin->display_name = 'User Administrator'; // optional
$admin->description  = 'User is allowed to manage and edit other users'; // optional
$admin->save();
```

Now we need to add `Permission`s:

```php
$createPost = new Permission();
$createPost->name         = 'create-post';
$createPost->display_name = 'Create Posts'; // optional
// Allow a user to...
$createPost->description  = 'create new blog posts'; // optional
$createPost->save();

$editUser = new Permission();
$editUser->name         = 'edit-user';
$editUser->display_name = 'Edit Users'; // optional
// Allow a user to...
$editUser->description  = 'edit existing users'; // optional
$editUser->save();
```

## Group Permissions Assignment & Removal
By using the `LaratrustGroupTrait` we can do the following:

### Assignment

```php
$admin->attachPermission($createPost); // parameter can be a Permission object, array or id
// equivalent to $admin->permissions()->attach([$createPost->id]);

$owner->attachPermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->attach([$createPost->id, $editUser->id]);

$owner->syncPermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->sync([$createPost->id, $editUser->id]);
```

### Removal

```php
$admin->detachPermission($createPost); // parameter can be a Permission object, array or id
// equivalent to $admin->permissions()->detach([$createPost->id]);

$owner->detachPermissions([$createPost, $editUser]); // parameter can be a Permission object, array or id
// equivalent to $owner->permissions()->detach([$createPost->id, $editUser->id]);
```

## User Groups Assignment & Removal

With both groups created let's assign them to the users.
Thanks to the `LaratrustUserTrait` this is as easy as:

### Assignment

```php
$user->attachGroup($admin); // parameter can be a Group object, array, id or the group string name
// equivalent to $user->groups()->attach([$admin->id]);

$user->attachGroups([$admin, $owner]); // parameter can be a Group object, array, id or the group string name
// equivalent to $user->groups()->attach([$admin->id, $owner->id]);

$user->syncGroups([$admin->id, $owner->id]);
// equivalent to $user->groups()->sync([$admin->id, $owner->id]);

$user->syncGroupsWithoutDetaching([$admin->id, $owner->id]);
// equivalent to $user->groups()->syncWithoutDetaching([$admin->id, $owner->id]);
```

### Removal
```php
$user->detachGroup($admin); // parameter can be a Group object, array, id or the group string name
// equivalent to $user->groups()->detach([$admin->id]);

$user->detachGroups([$admin, $owner]); // parameter can be a Group object, array, id or the group string name
// equivalent to $user->groups()->detach([$admin->id, $owner->id]);
```

## User Permissions Assignment & Removal

You can attach single permissions to a user, so in order to do it you only have to make:

### Assignment

```php
$user->attachPermission($editUser); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->attach([$editUser->id]);

$user->attachPermissions([$editUser, $createPost]); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->attach([$editUser->id, $createPost->id]);

$user->syncPermissions([$editUser->id, $createPost->id]);
// equivalent to $user->permissions()->sync([$editUser->id, createPost->id]);

$user->syncPermissionsWithoutDetaching([$editUser, $createPost]); // parameter can be a Permission object, array or id
    // equivalent to $user->permissions()->syncWithoutDetaching([$createPost->id, $editUser->id]);
```

### Removal

```php
$user->detachPermission($createPost); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->groups()->detach([$createPost->id]);

$user->detachPermissions([$createPost, $editUser]); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->groups()->detach([$createPost->id, $editUser->id]);
```

## Checking for Groups & Permissions
Now we can check for groups and permissions simply by doing:

```php
$user->hasGroup('owner');   // false
$user->hasGroup('admin');   // true
$user->can('edit-user');   // false
$user->can('create-post'); // true
```

::: tip NOTE
- If you want, you can use the `hasPermission` and `isAbleTo` methods instead of the `can` method.
- If you want, you can use the `isA` and `isAn` methods instead of the `hasGroup` method.
:::

::: tip NOTE
If you want to use the Authorizable trait alongside Laratrust please check the <docs-link to="/troubleshooting.html">troubleshooting</docs-link> page.
:::

Both `can()` and `hasGroup()` can receive an array or pipe separated string of groups & permissions to check:

```php
$user->hasGroup(['owner', 'admin']);       // true
$user->can(['edit-user', 'create-post']); // true

$user->hasGroup('owner|admin');       // true
$user->can('edit-user|create-post'); // true
```

By default, if any of the groups or permissions are present for a user then the method will return true.
Passing `true` as a second parameter instructs the method to require **all** of the items:

```php
$user->hasGroup(['owner', 'admin']);             // true
$user->hasGroup(['owner', 'admin'], true);       // false, user does not have admin group
$user->can(['edit-user', 'create-post']);       // true
$user->can(['edit-user', 'create-post'], true); // false, user does not have edit-user permission
```

You can have as many `Group`s as you want for each `User` and vice versa. Also, you can have as many direct `Permissions`s as you want for each `User` and vice versa.

The `Laratrust` class has shortcuts to both `can()` and `hasGroup()` for the currently logged in user:

```php
Laratrust::hasGroup('group-name');
Laratrust::can('permission-name');

// is identical to

Auth::user()->hasGroup('group-name');
Auth::user()->hasPermission('permission-name');
```

::: warning
There aren't  `Laratrust::hasPermission` or `Laratrust::isAbleTo` facade methods, because you can use the `Laratrust::can` even when using the `Authorizable` trait.
:::

You can also use wildcard to check any matching permission by doing:

```php
// match any admin permission
$user->can('admin.*'); // true

// match any permission about users
$user->can('*-users'); // true
```

### Magic can method
You can check if a user has some permissions by using the magic can method:

```php
$user->canCreateUsers();
// Same as $user->can('create-users');
```

If you want to change the case used when checking for the permission, you can change the `magic_can_method_case` value in your `config/laratrust.php` file.

```php
// config/laratrust.php
'magic_can_method_case' => 'snake_case', // The default value is 'kebab_case'

// In you controller
$user->canCreateUsers();
// Same as $user->can('create_users');
```

## User ability

More advanced checking can be done using the awesome `ability` function.
It takes in three parameters (groups, permissions, options):

* `groups` is a set of groups to check.
* `permissions` is a set of permissions to check.
* `options` is a set of options to change the method behavior.

Either of the groups or permissions variable can be a pipe separated string or an array:

```php
$user->ability(['admin', 'owner'], ['create-post', 'edit-user']);

// or

$user->ability('admin|owner', 'create-post|edit-user');
```

This will check whether the user has any of the provided groups and permissions.
In this case it will return true since the user is an `admin` and has the `create-post` permission.

The third parameter is an options array:

```php
$options = [
    'validate_all' => true | false (Default: false),
    'return_type'  => boolean | array | both (Default: boolean)
];
```

* `validate_all` is a boolean flag to set whether to check all the values for true, or to return true if at least one group or permission is matched.
* `return_type` specifies whether to return a boolean, array of checked values, or both in an array.

Here is an example output:

```php
$options = [
    'validate_all' => true,
    'return_type' => 'both'
];

list($validate, $allValidations) = $user->ability(
    ['admin', 'owner'],
    ['create-post', 'edit-user'],
    $options
);

var_dump($validate);
// bool(false)

var_dump($allValidations);
// array(4) {
//     ['group'] => bool(true)
//     ['group_2'] => bool(false)
//     ['create-post'] => bool(true)
//     ['edit-user'] => bool(false)
// }
```

The `Laratrust` class has a shortcut to `ability()` for the currently logged in user:

```php
Laratrust::ability('admin|owner', 'create-post|edit-user');

// is identical to

Auth::user()->ability('admin|owner', 'create-post|edit-user');
```

## Retrieving Relationships
The `LaratrustUserTrait` has the `groups` and `permissions` relationship, that return a `MorphToMany` relationships.

The `groups` relationship has all the groups attached to the user.

The `permissions` relationship has all the direct permissions attached to the user.

If you want to retrieve all the user permissions, you can use the `allPermissions` method. It returns a unified collection with all the permissions related to the user (via the groups and permissions relationships).

```php
dump($user->allPermissions());
/*
    Illuminate\Database\Eloquent\Collection {#646
    #items: array:2 [
    0 => App\Permission {#662
        ...
        #attributes: array:6 [
        "id" => "1"
        "name" => "edit-users"
        "display_name" => "Edit Users"
        "description" => null
        "created_at" => "2017-06-19 04:58:30"
        "updated_at" => "2017-06-19 04:58:30"
        ]
        ...
    }
    1 => App\Permission {#667
        ...
        #attributes: array:6 [
        "id" => "2"
        "name" => "manage-users"
        "display_name" => "Manage Users"
        "description" => null
        "created_at" => "2017-06-19 04:58:30"
        "updated_at" => "2017-06-19 04:58:30"
        ]
        ...
    }
    ]
}
*/
```

If you want to retrieve the users that have some group you can use the query scope `whereGroupIs` or `orWhereGroupIs`:

```php
// This will return the users with 'admin' group.
$users = User::whereGroupIs('admin')->orWhereGroupIs('regular-user')->get();
```

Also, if you want to retrieve the users that have some permission you can use the query scope `wherePermissionIs` or `orWherePermissionIs`:

```php
// This will return the users with 'edit-user' permission.
$users = User::wherePermissionIs('edit-user')->orWherePermissionIs('create-user')->get();
```

## Objects Ownership
If you need to check if the user owns an object you can use the user function `owns`:

```php
public function update (Post $post) {
    if ($user->owns($post)) { //This will check the 'user_id' inside the $post
        abort(403);
    }

    ...
}
```

If you want to change the foreign key name to check for, you can pass a second attribute to the method:

```php
public function update (Post $post) {
    if ($user->owns($post, 'idUser')) { //This will check for 'idUser' inside the $post
        abort(403);
    }

    ...
}
```

### Permissions, Groups & Ownership Checks
If you want to check if a user can do something or has a group, and also is the owner of an object you can use the `canAndOwns` and `hasGroupAndOwns` methods:

Both methods accept three parameters:

* `permission` or `group` are the permission or group to check (This can be an array of groups or permissions).
* `thing` is the object used to check the ownership.
* `options` is a set of options to change the method behavior (optional).

The third parameter is an options array:

```php
$options = [
    'requireAll' => true | false (Default: false),
    'foreignKeyName'  => 'canBeAnyString' (Default: null)
];
```

Here's an example of the usage of both methods:

```php
$post = Post::find(1);
$user->canAndOwns('edit-post', $post);
$user->canAndOwns(['edit-post', 'delete-post'], $post);
$user->canAndOwns(['edit-post', 'delete-post'], $post, ['requireAll' => false, 'foreignKeyName' => 'writer_id']);

$user->hasGroupAndOwns('admin', $post);
$user->hasGroupAndOwns(['admin', 'writer'], $post);
$user->hasGroupAndOwns(['admin', 'writer'], $post, ['requireAll' => false, 'foreignKeyName' => 'writer_id']);
```

The `Laratrust` class has a shortcut to `owns()`, `canAndOwns` and `hasGroupAndOwns` methods for the currently logged in user:

```php
Laratrust::owns($post);
Laratrust::owns($post, 'idUser');

Laratrust::canAndOwns('edit-post', $post);
Laratrust::canAndOwns(['edit-post', 'delete-post'], $post, ['requireAll' => false, 'foreignKeyName' => 'writer_id']);

Laratrust::hasGroupAndOwns('admin', $post);
Laratrust::hasGroupAndOwns(['admin', 'writer'], $post, ['requireAll' => false, 'foreignKeyName' => 'writer_id']);
```

### Ownable Interface
If the object ownership is resolved through a more complex logic you can implement the Ownable interface so you can use the `owns`, `canAndOwns` and `hasGroupAndOwns` methods in those cases:

```php
class SomeOwnedObject implements \Laratrust\Contracts\Ownable
{
    ...

    public function ownerKey($owner)
    {
        return $this->someRelationship->user->id;
    }

    ...
}
```

::: tip IMPORTANT
- The `ownerKey` method **must** return the object's owner id value.
- The `ownerKey` method receives as a parameter the object that called the `owns` method.
:::

After implementing it, you can simply do:

```php
$user = User::find(1);
$theObject = new SomeOwnedObject;
$user->owns($theObject);            // This will return true or false depending on what the ownerKey method returns
```

## Teams
::: tip NOTE
The teams feature is **optional**, please go to the <docs-link to="/configuration/teams.html">teams configuration</docs-link> in order to use the feature.
:::

### Groups Assignment & Removal
The groups assignment and removal are the same, but this time you can pass the team as an optional parameter.

```php
$team = Team::where('name', 'my-awesome-team')->first();
$admin = Group::where('name', 'admin')->first();

$user->attachGroup($admin, $team); // parameter can be an object, array, id or the string name.
```

This will attach the `admin` group to the user but only within the `my-awesome-team` team.

You can also attach multiple groups to the user within a team:

```php
$team = Team::where('name', 'my-awesome-team')->first();
$admin = Group::where('name', 'admin')->first();
$owner = Group::where('name', 'owner')->first();

$user->attachGroups([$admin, $owner], $team); // parameter can be an object, array, id or the string name.
```

To remove the groups you can do:

```php
$user->detachGroup($admin, $team); // parameter can be an object, array, id or the string name.
$user->detachGroups([$admin, $owner], $team); // parameter can be an object, array, id or the string name.
```

You can also sync groups within a group:

```php
$user->syncGroups([$admin, $owner], $team); // parameter can be an object, array, id or the string name.
```

::: tip IMPORTANT
It will sync the groups depending of the team passed, because there is a `wherePivot` constraint in the syncing method. So if you pass a team with id of 1, it will sync all the groups that are attached to the user where the team id is 1.

So if you don't pass any team, it will sync the groups where the team id is `null` in the pivot table.
:::

### Permissions Assignment & Removal
The permissions assignment and removal are the same, but this time you can pass the team as an optional parameter.

```php
$team = Team::where('name', 'my-awesome-team')->first();
$editUser = Permission::where('name', 'edit-user')->first();

$user->attachPermission($editUser, $team); // parameter can be an object, array, id or the string name.
```

This will attach the `edit-user` permission to the user but only within the `my-awesome-team` team.

You can also attach multiple permissions to the user within a team:

```php
$team = Team::where('name', 'my-awesome-team')->first();
$editUser = Permission::where('name', 'edit-user')->first();
$manageUsers = Permission::where('name', 'manage-users')->first();

$user->attachPermissions([$editUser, $manageUsers], $team); // parameter can be an object, array, id or the string name.
```

To remove the permissions you can do:

```php
$user->detachPermission($editUser, $team); // parameter can be an object, array, id or the string name.
$user->detachPermissions([$editUser, $manageUsers], $team); // parameter can be an object, array, id or the string name.
```

You can also sync permissions within a group:

```php
$user->syncPermissions([$editUser, $manageUsers], $team); // parameter can be an object, array, id or the string name.
```

::: tip IMPORTANT
It will sync the permissions depending of the team passed, because there is a `wherePivot` constraint in the syncing method. So if you pass a team with id of 1, it will sync all the permissions that are attached to the user where the team id is 1 in the pivot table.

So if you don't pass any team, it will sync the permissions where the team id is `null` in the pivot table.
:::

### Checking Groups & Permissions
The groups and permissions verification is the same, but this time you can pass the team parameter.

The teams groups and permissions check can be configured by changing the `teams_strict_check` value inside the `config/laratrust.php` file. This value can be `true` or `false`:

- If `teams_strict_check` is set to `false`:
    When checking for a group or permission if no team is given, it will check if the user has the group or permission regardless if that group or permissions was attached inside a team.

- If `teams_strict_check` is set to `true`:
    When checking for a group or permission if no team is given, it will check if the user has the group or permission where the team id is null.

Check groups:

```php
    $user->hasGroup('admin', 'my-awesome-team');
    $user->hasGroup(['admin', 'user'], 'my-awesome-team', true);
```

Check permissions:
```php
    $user->can('edit-user', 'my-awesome-team');
    $user->can(['edit-user', 'manage-users'], 'my-awesome-team', true);
```

### User Ability

The user ability is the same, but this time you can pass the team parameter.

```php
$options = [
    'requireAll' => true | false (Default: false),
    'foreignKeyName'  => 'canBeAnyString' (Default: null)
];

$user->ability(['admin'], ['edit-user'], 'my-awesome-team');
$user->ability(['admin'], ['edit-user'], 'my-awesome-team', $options);
```

### Permissions, Groups & Ownership Checks
The permissions, groups and ownership checks work the same, but this time you can pass the team in the options array.

```php
$options = [
    'team' => 'my-awesome-team',
    'requireAll' => false,
    'foreignKeyName' => 'writer_id'
];

$post = Post::find(1);
$user->canAndOwns(['edit-post', 'delete-post'], $post, $options);
$user->hasGroupAndOwns(['admin', 'writer'], $post, $options);
```