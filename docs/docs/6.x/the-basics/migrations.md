# Migrations

The migration will create five (or six if you use teams feature) tables in your database:

* `groups` — stores group records.
* `permissions` — stores permission records.
* `teams` — stores teams records (Only if you use the teams feature).
* `group_user` — stores [polymorphic](https://laravel.com/docs/eloquent-relationships#polymorphic-relations) relations between groups and users.
* `permission_group` — stores [many-to-many](https://laravel.com/docs/eloquent-relationships#many-to-many) relations between groups and permissions.
* `permission_user` — stores [polymorphic](https://laravel.com/docs/eloquent-relationships#polymorphic-relations) relations between users and permissions.
