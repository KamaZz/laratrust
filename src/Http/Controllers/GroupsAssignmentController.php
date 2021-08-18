<?php

namespace Laratrust\Http\Controllers;

use Laratrust\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class GroupsAssignmentController
{
    protected $groupsModel;
    protected $permissionModel;
    protected $assignPermissions;

    public function __construct()
    {
        $this->groupsModel = Config::get('laratrust.models.group');
        $this->permissionModel = Config::get('laratrust.models.permission');
        $this->assignPermissions = Config::get('laratrust.panel.assign_permissions_to_user');
    }

    public function index(Request $request)
    {
        $modelsKeys = array_keys(Config::get('laratrust.user_models'));
        $modelKey = $request->get('model') ?? $modelsKeys[0] ?? null;
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            abort(404);
        }

        return View::make('laratrust::panel.groups-assignment.index', [
            'models' => $modelsKeys,
            'modelKey' => $modelKey,
            'users' => $userModel::query()
                ->withCount(['groups', 'permissions'])
                ->simplePaginate(10),
        ]);
    }

    public function edit(Request $request, $modelId)
    {
        $modelKey = $request->get('model');
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            Session::flash('laratrust-error', 'Model was not specified in the request');
            return redirect(route('laratrust.groups-assignment.index'));
        }

        $user = $userModel::query()
            ->with(['groups:id,name', 'permissions:id,name'])
            ->findOrFail($modelId);

        $groups = $this->groupsModel::orderBy('name')->get(['id', 'name', 'display_name'])
            ->map(function ($group) use ($user) {
                $group->assigned = $user->groups
                ->pluck('id')
                    ->contains($group->id);
                $group->isRemovable = Helper::groupIsRemovable($group);

                return $group;
            });
        if ($this->assignPermissions) {
            $permissions = $this->permissionModel::orderBy('name')
                ->get(['id', 'name', 'display_name'])
                ->map(function ($permission) use ($user) {
                    $permission->assigned = $user->permissions
                        ->pluck('id')
                        ->contains($permission->id);

                    return $permission;
                });
        }


        return View::make('laratrust::panel.groups-assignment.edit', [
            'modelKey' => $modelKey,
            'groups' => $groups,
            'permissions' => $this->assignPermissions ? $permissions : null,
            'user' => $user,
        ]);
    }

    public function update(Request $request, $modelId)
    {
        $modelKey = $request->get('model');
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            Session::flash('laratrust-error', 'Model was not specified in the request');
            return redirect()->back();
        }

        $user = $userModel::findOrFail($modelId);
        $user->syncGroups($request->get('groups') ?? []);
        if ($this->assignPermissions) {
            $user->syncPermissions($request->get('permissions') ?? []);
        }

        Session::flash('laratrust-success', 'Groups and permissions assigned successfully');
        return redirect(route('laratrust.groups-assignment.index', ['model' => $modelKey]));
    }
}
