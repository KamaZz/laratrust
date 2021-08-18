# Upgrade from 5.0 to 5.1

::: tip IMPORTANT
Laratrust 5.1 requires Laravel >= 5.2.32 and php >= 5.6.0.
:::

In order to upgrade from Laratrust 5.0 to 5.1 you have to follow these steps:

1. Change your `composer.json` to require the 5.1 version of Laratrust:
```json
"santigarcor/laratrust": "5.1.*"
```

2. Run `composer update` to update the source code.

3. Run `php artisan config:clear` and `php artisan cache:clear`.

4. Update your `config/laratrust.php`:

    4.1. Backup your `config/laratrust.php` configuration values.

    4.2. Delete the `config/laratrust.php` file.

    4.3. Run `php artisan vendor:publish --tag=laratrust`.

    4.4. Update the `config/laratrust.php` file with your old values.

5. Delete the `LaratrustSeeder.php` file and run `php artisan laratrust:seeder`.

6. If you use the method `cachedGroups` or `cachedPermissions` you should stop using them and change the parts where you use them because they were deleted from the `LaratrustUserTrait` and `LaratrustGroupTrait`.

6. Run `composer dump-autoload`.

Now you can use the 5.1 version without any problem.
