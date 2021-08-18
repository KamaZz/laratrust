# User

```php
<?php

use Laratrust\Traits\LaratrustUserTrait;

class User extends Model
{
    use LaratrustUserTrait; // add this trait to your user model

    ...
}
```

This class uses the `LaratrustUserTrait` to enable the relationships with `Group` and `Permission`.It also adds the following methods `groups()`, `hasGroup($name)`, `hasPermission($permission)`, `isAbleTo($permission)`, `ability($groups, $permissions, $options)`, and `groupsTeams()` to the model.