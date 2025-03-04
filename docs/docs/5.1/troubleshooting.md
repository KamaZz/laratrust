# Troubleshooting
---
If you make changes directly to the Laratrust tables and when you run your code the changes are not reflected, please clear your application cache using::

```bash
php artisan cache:clear
```

Remember that Laratrust uses cache in the groups and permissions checks.

---
If you want to use the `Authorizable` trait you have to do:

```php
use Authorizable {
    Authorizable::can insteadof LaratrustUserTrait;
    LaratrustUserTrait::can as laratrustCan;
}
```

And then replace all the uses of `can` with `hasPermission` or `isAbleTo`.

::: tip NOTE
If you use the `Laratrust::can` facade method you don't have to change this method because it calls the `hasPermission` method.
:::

---

If you encounter an error when doing the migration that looks like::
```log
SQLSTATE[HY000]: General error: 1005 Can't create table 'laravelbootstrapstarter.#sql-42c_f8' (errno: 150)
    (SQL: alter table `group_user` add constraint group_user_user_id_foreign foreign key (`user_id`)
    references `users` (`id`)) (Bindings: array ())
```

Then it is likely that the `id` column in your user table does not match the `user_id` column in `group_user`.
Make sure both are `INT(10)`.

---

When trying to use the LaratrustUserTrait methods, you encounter the error which looks like::

    Class name must be a valid object or a string

Then probably you do not have published Laratrust assets or something went wrong when you did it.
First of all check that you have the `laratrust.php` file in your `app/config` directory.
If you don't, then try `php artisan vendor:publish` and, if it does not appear, manually copy the `/vendor/santigarcor/laratrust/src/config/config.php` file in your config directory and rename it `laratrust.php`.

---

