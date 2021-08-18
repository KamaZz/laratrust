# User

Next, use the `LaratrustUserTrait` trait in your existing user models. For example:

```php
<?php

use Laratrust\Traits\LaratrustUserTrait;

class User extends Model
{
    use LaratrustUserTrait; // add this trait to your user model

    ...
}
```

This will enable the relation with `Group` and `Permission`, and add the following methods `groups()`, `hasGroup($name)`, `hasPermission($permission)`, `isAbleTo($permission)`, `can($permission)`, `ability($groups, $permissions, $options)`, and `groupsTeams()` within your `User` model.

Do not forget to dump composer autoload

```bash
    composer dump-autoload
```

::: tip IMPORTANT
At this point you are ready to go
:::
