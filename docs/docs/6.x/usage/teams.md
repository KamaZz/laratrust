---
sidebarDepth: 2
---

# Teams
::: tip NOTE
The teams feature is **optional**, please go to the <docs-link to="/the-basics/teams.html">teams configuration</docs-link> in order to use the feature.
:::

## Groups Assignment & Removal
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

## Permissions Assignment & Removal
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

## Checking Groups & Permissions
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
    $user->isAbleTo('edit-user', 'my-awesome-team');
    $user->isAbleTo(['edit-user', 'manage-users'], 'my-awesome-team', true);
```

## User Ability

The user ability is the same, but this time you can pass the team parameter.

```php
$options = [
    'validate_all' => true, //Default: false
    'return_type'  => 'array' //Default: 'boolean'. You can also set it as 'both'
];

$user->ability(['admin'], ['edit-user'], 'my-awesome-team');
$user->ability(['admin'], ['edit-user'], 'my-awesome-team', $options);
```

## Permissions, Groups & Ownership Checks
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
