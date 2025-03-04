# Soft Deleting

The default migration takes advantage of `onDelete('cascade')` clauses within the pivot tables to remove relations when a parent record is deleted. If for some reason you can not use cascading deletes in your database, the LaratrustGroup and LaratrustPermission classes, and the HasGroup trait include event listeners to manually delete records in relevant pivot tables.

In the interest of not accidentally deleting data, the event listeners will **not** delete pivot data if the model uses soft deleting. However, due to limitations in Laravel's event listeners, there is no way to distinguish between a call to `delete()` versus a call to `forceDelete()`. For this reason, **before you force delete a model, you must manually delete any of the relationship data** (unless your pivot tables uses cascading deletes). For example:

```php
$group = Group::findOrFail(1); // Pull back a given group

// Regular Delete
$group->delete(); // This will work no matter what

// Force Delete
$group->users()->sync([]); // Delete relationship data
$group->permissions()->sync([]); // Delete relationship data

$group->forceDelete(); // Now force delete will work regardless of whether the pivot table has cascading delete
```