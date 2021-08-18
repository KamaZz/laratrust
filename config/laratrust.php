<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Use MorphMap in relationships between models
    |--------------------------------------------------------------------------
    |
    | If true, the morphMap feature is going to be used. The array values that
    | are going to be used are the ones inside the 'user_models' array.
    |
    */
    'use_morph_map' => false,

    /*
    |--------------------------------------------------------------------------
    | Which permissions and group checker to use.
    |--------------------------------------------------------------------------
    |
    | Defines if you want to use the groups and permissions checker.
    | Available:
    | - default: Check for the groups and permissions using the method that Laratrust
                 has always used.
    | - query: Check for the groups and permissions using direct queries to the database.
    |           This method doesn't support cache yet.
    |
     */
    'checker' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Manage Laratrust's cache configurations. It uses the driver defined in the
    | config/cache.php file.
    |
    */
    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Use cache in the package
        |--------------------------------------------------------------------------
        |
        | Defines if Laratrust will use Laravel's Cache to cache the groups and permissions.
        | NOTE: Currently the database check does not use cache.
        |
        */
        'enabled' => env('LARATRUST_ENABLE_CACHE', true),

        /*
        |--------------------------------------------------------------------------
        | Time to store in cache Laratrust's groups and permissions.
        |--------------------------------------------------------------------------
        |
        | Determines the time in SECONDS to store Laratrust's groups and permissions in the cache.
        |
        */
        'expiration_time' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust User Models
    |--------------------------------------------------------------------------
    |
    | This is the array that contains the information of the user models.
    | This information is used in the add-trait command, for the groups and
    | permissions relationships with the possible user models, and the
    | administration panel to attach groups and permissions to the users.
    |
    | The key in the array is the name of the relationship inside the groups and permissions.
    |
    */
    'user_models' => [
        'users' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust Models
    |--------------------------------------------------------------------------
    |
    | These are the models used by Laratrust to define the groups, permissions and teams.
    | If you want the Laratrust models to be in a different namespace or
    | to have a different name, you can do it here.
    |
    */
    'models' => [

        'group' => \App\Models\Group::class,

        'permission' => \App\Models\Permission::class,

        /**
         * Will be used only if the teams functionality is enabled.
         */
        'team' => \App\Models\Team::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust Tables
    |--------------------------------------------------------------------------
    |
    | These are the tables used by Laratrust to store all the authorization data.
    |
    */
    'tables' => [

        'groups' => 'groups',

        'permissions' => 'permissions',

        /**
         * Will be used only if the teams functionality is enabled.
         */
        'teams' => 'teams',

        'group_user' => 'group_user',

        'permission_user' => 'permission_user',

        'permission_group' => 'permission_group',
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust Foreign Keys
    |--------------------------------------------------------------------------
    |
    | These are the foreign keys used by laratrust in the intermediate tables.
    |
    */
    'foreign_keys' => [
        /**
         * User foreign key on Laratrust's group_user and permission_user tables.
         */
        'user' => 'user_id',

        /**
         * Group foreign key on Laratrust's group_user and permission_group tables.
         */
        'group' => 'group_id',

        /**
         * Group foreign key on Laratrust's permission_user and permission_group tables.
         */
        'permission' => 'permission_id',

        /**
         * Group foreign key on Laratrust's group_user and permission_user tables.
         */
        'team' => 'team_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust Middleware
    |--------------------------------------------------------------------------
    |
    | This configuration helps to customize the Laratrust middleware behavior.
    |
    */
    'middleware' => [
        /**
         * Define if the laratrust middleware are registered automatically in the service provider
         */
        'register' => true,

        /**
         * Method to be called in the middleware return case.
         * Available: abort|redirect
         */
        'handling' => 'abort',

        /**
         * Handlers for the unauthorized method in the middlewares.
         * The name of the handler must be the same as the handling.
         */
        'handlers' => [
            /**
             * Aborts the execution with a 403 code and allows you to provide the response text
             */
            'abort' => [
                'code' => 403,
                'message' => 'User does not have any of the necessary access rights.'
            ],

            /**
             * Redirects the user to the given url.
             * If you want to flash a key to the session,
             * you can do it by setting the key and the content of the message
             * If the message content is empty it won't be added to the redirection.
             */
            'redirect' => [
                'url' => '/home',
                'message' => [
                    'key' => 'error',
                    'content' => ''
                ]
            ]
        ]
    ],

    'teams' => [
        /*
        |--------------------------------------------------------------------------
        | Use teams feature in the package
        |--------------------------------------------------------------------------
        |
        | Defines if Laratrust will use the teams feature.
        | Please check the docs to see what you need to do in case you have the package already configured.
        |
        */
        'enabled' => false,

        /*
        |--------------------------------------------------------------------------
        | Strict check for groups/permissions inside teams
        |--------------------------------------------------------------------------
        |
        | Determines if a strict check should be done when checking if a group or permission
        | is attached inside a team.
        | If it's false, when checking a group/permission without specifying the team,
        | it will check only if the user has attached that group/permission ignoring the team.
        |
        */
        'strict_check' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Laratrust Magic 'isAbleTo' Method
    |--------------------------------------------------------------------------
    |
    | Supported cases for the magic is able to method (Refer to the docs).
    | Available: camel_case|snake_case|kebab_case
    |
    */
    'magic_is_able_to_method_case' => 'kebab_case',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Permissions as Gates
    |--------------------------------------------------------------------------
    |
    | Determines if you can check if a user has a permission using the "can" method.
    |
    */
    'permissions_as_gates' => false,

    /*
    |--------------------------------------------------------------------------
    | Laratrust Panel
    |--------------------------------------------------------------------------
    |
    | Section to manage everything related with the admin panel for the groups and permissions.
    |
    */
    'panel' => [
        /*
        |--------------------------------------------------------------------------
        | Laratrust Panel Register
        |--------------------------------------------------------------------------
        |
        | This manages if routes used for the admin panel should be registered.
        | Turn this value to false if you don't want to use Laratrust admin panel
        |
        */
        'register' => false,

        /*
        |--------------------------------------------------------------------------
        | Laratrust Panel Path
        |--------------------------------------------------------------------------
        |
        | This is the URI path where Laratrust panel for groups and permissions
        | will be accessible from.
        |
        */
        'path' => 'laratrust',

        /*
        |--------------------------------------------------------------------------
        | Laratrust Panel Path
        |--------------------------------------------------------------------------
        |
        | The route where the go back link should point
        |
        */
        'go_back_route' => '/',

        /*
        |--------------------------------------------------------------------------
        | Laratrust Panel Route Middleware
        |--------------------------------------------------------------------------
        |
        | These middleware will get attached onto each Laratrust panel route.
        |
        */
        'middleware' => ['web'],

        /*
        |--------------------------------------------------------------------------
        | Enable permissions assignment
        |--------------------------------------------------------------------------
        |
        | Enable/Disable the permissions assignment to the users.
        |
        */
        'assign_permissions_to_user' => true,

        /*
        |--------------------------------------------------------------------------
        | Add restriction to groups in the panel
        |--------------------------------------------------------------------------
        |
        | Configure which groups can not be editable, deletable and removable.
        | To add a group to the restriction, use name of the group here.
        |
        */
        'groups_restrictions' => [
            // The user won't be able to remove groups already assigned to users.
            'not_removable' => [],

            // The user won't be able to edit the group and the permissions assigned.
            'not_editable' => [],

            // The user won't be able to delete the group.
            'not_deletable' => [],
        ],
    ]
];
