# Migrations

Now generate the Laratrust migration

```bash
php artisan laratrust:migration
```

It will generate the `<timestamp>_laratrust_setup_tables.php` migration.
You may now run it with the artisan migrate command:

```bash
php artisan migrate
```

After the migration, five (or six if you use teams feature) new tables will be present:

* `groups` — stores group records.
* `permissions` — stores permission records.
* `teams` — stores teams records (Only if you use the teams feature).
* `group_user` — stores [polymorphic](https://laravel.com/docs/eloquent-relationships#polymorphic-relations) relations between groups and users.
* `permission_group` — stores [many-to-many](https://laravel.com/docs/eloquent-relationships#many-to-many) relations between groups and permissions.
* `permission_user` — stores [polymorphic](https://laravel.com/docs/eloquent-relationships#polymorphic-relations) relations between users and permissions.
