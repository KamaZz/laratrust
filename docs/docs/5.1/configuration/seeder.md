# Seeder

Laratrust comes with a database seeder, this seeder helps you fill the permissions for each group depending on the module, and creates one user for each group.

::: tip NOTE
Laratrust now accepts multiple user models so the seeder is going to work with the first user model inside the user_models array.
:::

::: tip NOTE
Laratrust now has teams feature, the seeder doesn't support it.
:::

To generate the seeder you have to run

```bash
php artisan laratrust:seeder
```

and

```bash
composer dump-autoload
```

And in the `database/seeds/DatabaseSeeder.php` file you have to add this to the `run` method:

```php
$this->call(LaratrustSeeder::class);
```

::: tip NOTE
If you **have not** run `php artisan vendor:publish --tag="laratrust"` you should run it in order to customize the groups, modules and permissions in each case.
:::

Your `config/laratrust_seeder.php` file looks like this:

```php
return [
    'group_structure' => [
        'superadministrator' => [
            'users' => 'c,r,u,d',
            'acl' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'administrator' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'user' => [
                'profile' => 'r,u'
        ],
    ],
    'permission_structure' => [
        'cru_user' => [
            'profile' => 'c,r,u'
        ],
    ],
    ...
];
```

To understand the `group_structure` you must know:

* The first level is the groups.
* The second level is the modules.
* The second level assignments are the permissions.

With that in mind, you should arrange your groups, modules and permissions like this:

```php
return [
    'group_structure' => [
        'group' => [
            'module' => 'permissions',
        ],
    ]
];
```

To understand the `permission_structure` you must know:

* The first level is the users.
* The second level is the modules.
* The second level assignments are the permissions.

With that in mind, you should arrange your users, modules and permissions like this:

```php
return [
    'permission_structure' => [
        'user' => [
            'module' => 'permissions',
        ],
    ]
];
```

## Permissions

In case that you do not want to use the `c,r,u,d` permissions, in the `config/laratrust_seeder.php` there the `permissions_map` where you can change the permissions mapping.
