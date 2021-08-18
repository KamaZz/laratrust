<?php

use Illuminate\Support\Facades\Route;

Route::resource('/permissions', 'PermissionsController', ['as' => 'laratrust'])
    ->only(['index', 'edit', 'update']);

Route::resource('/groups', 'GroupsController', ['as' => 'laratrust']);

Route::resource('/groups-assignment', 'GroupsAssignmentController', ['as' => 'laratrust'])
    ->only(['index', 'edit', 'update']);
