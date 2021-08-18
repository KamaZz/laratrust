# Administration Panel

Laratrust comes with a simple administration panel for groups, permissions and their assignment to the users.

Currently it supports:

1. Permissions CRUD.
2. Groups CRUD and permissions assignment.
3. Assignment of groups and permission to the multiple users defined in `user_models` in the `config/laratrust.php` file.
4. Restricting groups from being edited, deleted or removed.

## How to use it

1. Go to your `config/laratrust.php` file and change the `panel.register` value to `true`.
2. Publish the assets used by the panel by running:
```bash
php artisan vendor:publish --tag=laratrust-assets --force
```

By default the URL to access the panel is `/laratrust`.

To customize the the URL and other available settings in the panel please go to the `panel` section in the `config/laratrust.php` file.

## Screenshots

Here are some screenshots of the admin panel.
<div class="admin-panel-screenshots">
<img src="/multiple-users.png" alt="Edit group view">

<img src="/group-assign.png" alt="Edit group view">

<img src="/group-assign-user.png" alt="Edit group view">

<img src="/edit-group.png" alt="Edit group view">
</div>