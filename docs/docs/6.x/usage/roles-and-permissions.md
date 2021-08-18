---
sidebarDepth: 2
---

# Groups & Permissions

## Setting things up
Let's start by creating the following `Group`s:

```php
$owner = Group::create([
    'name' => 'owner',
    'display_name' => 'Project Owner', // optional
    'description' => 'User is the owner of a given project', // optional
]);

$admin = Group::create([
    'name' => 'admin',
    'display_name' => 'User Administrator', // optional
    'description' => 'User is allowed to manage and edit other users', // optional
]);
```

Now we need to add `Permission`s:

```php
$createPost = Permission::create([
'name' => 'create-post',
'display_name' => 'Create Posts', // optional
'description' => 'create new blog posts', // optional
]);

$editUser = Permission::create([
'name' => 'edit-user',
'display_name' => 'Edit Users', // optional
'description' => 'edit existing users', // optional
]);
```

## Group Permissions Assignment & Removal

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
// equivalent to $user->permissions()->detach([$createPost->id]);

$user->detachPermissions([$createPost, $editUser]); // parameter can be a Permission object, array, id or the permission string name
// equivalent to $user->permissions()->detach([$createPost->id, $editUser->id]);
```

## Checking for Groups & Permissions
Now we can check for groups and permissions simply by doing:

```php
$user->hasGroup('owner');   // false
$user->hasGroup('admin');   // true
$user->isAbleTo('edit-user');   // false
$user->isAbleTo('create-post'); // true
```

::: tip NOTE
- If you want, you can use the `hasPermission` or `isAbleTo`.
- If you want, you can use the `isA` and `isAn` methods instead of the `hasGroup` method.
:::

::: tip NOTE
We dropped the usage of the `can` method in order to have full support to Laravel's Gates and Policies.
:::

Both `isAbleTo()` and `hasGroup()` can receive an array or pipe separated string of groups & permissions to check:

```php
$user->hasGroup(['owner', 'admin']);       // true
$user->isAbleTo(['edit-user', 'create-post']); // true

$user->hasGroup('owner|admin');       // true
$user->isAbleTo('edit-user|create-post'); // true
```

By default, if any of the groups or permissions are present for a user then the method will return true.
Passing `true` as a second parameter instructs the method to require **all** of the items:

```php
$user->hasGroup(['owner', 'admin']);             // true
$user->hasGroup(['owner', 'admin'], true);       // false, user does not have admin group
$user->isAbleTo(['edit-user', 'create-post']);       // true
$user->isAbleTo(['edit-user', 'create-post'], true); // false, user does not have edit-user permission
```

You can have as many `Group`s as you want for each `User` and vice versa. Also, you can have as many direct `Permissions`s as you want for each `User` and vice versa.

The `Laratrust` class has shortcuts to both `isAbleTo()` and `hasGroup()` for the currently logged in user:

```php
Laratrust::hasGroup('group-name');
Laratrust::isAbleTo('permission-name');

// is identical to

Auth::user()->hasGroup('group-name');
Auth::user()->hasPermission('permission-name');
```

You can also use wildcard to check any matching permission by doing:

```php
// match any admin permission
$user->isAbleTo('admin.*'); // true

// match any permission about users
$user->isAbleTo('*-users'); // true
```

### Magic `is able to` method
You can check if a user has some permissions by using the magic `isAbleTo` method:

```php
$user->isAbleToCreateUsers();
// Same as $user->isAbleTo('create-users');
```

If you want to change the case used when checking for the permission, you can change the `magic_can_method_case` value in your `config/laratrust.php` file.

```php
// config/laratrust.php
'magic_can_method_case' => 'snake_case', // The default value is 'kebab_case'

// In you controller
$user->isAbleToCreateUsers();
// Same as $user->isAbleTo('create_users');
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
    'validate_all' => true, //Default: false
    'return_type'  => 'array' //Default: 'boolean'. You can also set it as 'both'
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

[$validate, $allValidations] = $user->ability(
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