<?php

namespace Laratrust;

use Illuminate\Support\Facades\Blade;

/**
 * This class is the one in charge of registering
 * the blade directives making a difference
 * between the version 5.2 and 5.3
 */
class LaratrustRegistersBladeDirectives
{
    /**
     * Handles the registration of the blades directives.
     *
     * @param  string  $laravelVersion
     * @return void
     */
    public function handle($laravelVersion = '5.3.0')
    {
        if (version_compare(strtolower($laravelVersion), '5.3.0-dev', '>=')) {
            $this->registerWithParenthesis();
        } else {
            $this->registerWithoutParenthesis();
        }

        $this->registerClosingDirectives();
    }

    /**
     * Registers the directives with parenthesis.
     *
     * @return void
     */
    protected function registerWithParenthesis()
    {
        // Call to Laratrust::hasGroup.
        Blade::directive('group', function ($expression) {
            return "<?php if (app('laratrust')->hasGroup({$expression})) : ?>";
        });

        // Call to Laratrust::permission.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('laratrust')->isAbleTo({$expression})) : ?>";
        });

        // Call to Laratrust::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('laratrust')->ability({$expression})) : ?>";
        });

        // Call to Laratrust::isAbleToAndOwns.
        Blade::directive('isAbleToAndOwns', function ($expression) {
            return "<?php if (app('laratrust')->isAbleToAndOwns({$expression})) : ?>";
        });

        // Call to Laratrust::hasGroupAndOwns.
        Blade::directive('hasGroupAndOwns', function ($expression) {
            return "<?php if (app('laratrust')->hasGroupAndOwns({$expression})) : ?>";
        });
    }

    /**
     * Registers the directives without parenthesis.
     *
     * @return void
     */
    protected function registerWithoutParenthesis()
    {
        // Call to Laratrust::hasGroup.
        Blade::directive('group', function ($expression) {
            return "<?php if (app('laratrust')->hasGroup{$expression}) : ?>";
        });

        // Call to Laratrust::isAbleTo.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('laratrust')->isAbleTo{$expression}) : ?>";
        });

        // Call to Laratrust::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('laratrust')->ability{$expression}) : ?>";
        });

        // Call to Laratrust::isAbleToAndOwns.
        Blade::directive('isAbleToAndOwns', function ($expression) {
            return "<?php if (app('laratrust')->isAbleToAndOwns{$expression}) : ?>";
        });

        // Call to Laratrust::hasGroupAndOwns.
        Blade::directive('hasGroupAndOwns', function ($expression) {
            return "<?php if (app('laratrust')->hasGroupAndOwns{$expression}) : ?>";
        });
    }

    /**
     * Registers the closing directives.
     *
     * @return void
     */
    protected function registerClosingDirectives()
    {
        Blade::directive('endgroup', function () {
            return "<?php endif; // app('laratrust')->hasGroup ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; // app('laratrust')->permission ?>";
        });

        Blade::directive('endability', function () {
            return "<?php endif; // app('laratrust')->ability ?>";
        });

        Blade::directive('endOwns', function () {
            return "<?php endif; // app('laratrust')->hasGroupAndOwns or isAbleToAndOwns ?>";
        });
    }
}
