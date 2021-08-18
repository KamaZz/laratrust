<?php

namespace Laratrust\Http\Controllers;

use Laratrust\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class GroupsController
{
    protected $groupsModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->groupsModel = Config::get('laratrust.models.group');
        $this->permissionModel = Config::get('laratrust.models.permission');
    }

    public function index()
    {
        return View::make('laratrust::panel.groups.index', [
            'groups' => $this->groupsModel::withCount('permissions')
                ->simplePaginate(10),
        ]);
    }

    public function create()
    {
        return View::make('laratrust::panel.edit', [
            'model' => null,
            'permissions' => $this->permissionModel::all(['id', 'name']),
            'type' => 'group',
        ]);
    }

    public function show(Request $request, $id)
    {
        $group = $this->groupsModel::query()
            ->with('permissions:id,name,display_name')
            ->findOrFail($id);

        return View::make('laratrust::panel.groups.show', ['group' => $group]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:groups,name',
            'display_name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $group = $this->groupsModel::create($data);
        $group->syncPermissions($request->get('permissions') ?? []);

        Session::flash('laratrust-success', 'Group created successfully');
        return redirect(route('laratrust.groups.index'));
    }

    public function edit($id)
    {
        $group = $this->groupsModel::query()
            ->with('permissions:id')
            ->findOrFail($id);

        if (!Helper::groupIsEditable($group)) {
            Session::flash('laratrust-error', 'The group is not editable');
            return redirect()->back();
        }

        $permissions = $this->permissionModel::all(['id', 'name', 'display_name'])
            ->map(function ($permission) use ($group) {
                $permission->assigned = $group->permissions
                    ->pluck('id')
                    ->contains($permission->id);

                return $permission;
            });

        return View::make('laratrust::panel.edit', [
            'model' => $group,
            'permissions' => $permissions,
            'type' => 'group',
        ]);
    }

    public function update(Request $request, $id)
    {
        $group = $this->groupsModel::findOrFail($id);

        if (!Helper::groupIsEditable($group)) {
            Session::flash('laratrust-error', 'The group is not editable');
            return redirect()->back();
        }

        $data = $request->validate([
            'display_name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $group->update($data);
        $group->syncPermissions($request->get('permissions') ?? []);

        Session::flash('laratrust-success', 'Group updated successfully');
        return redirect(route('laratrust.groups.index'));
    }

    public function destroy($id)
    {
        $usersAssignedToGroup = DB::table(Config::get('laratrust.tables.group_user'))
            ->where(Config::get('laratrust.foreign_keys.group'), $id)
            ->count();
        $group = $this->groupsModel::findOrFail($id);

        if (!Helper::groupIsDeletable($group)) {
            Session::flash('laratrust-error', 'The group is not deletable');
            return redirect()->back();
        }

        if ($usersAssignedToGroup > 0) {
            Session::flash('laratrust-warning', 'Group is attached to one or more users. It can not be deleted');
        } else {
            Session::flash('laratrust-success', 'Group deleted successfully');
            $this->groupsModel::destroy($id);
        }

        return redirect(route('laratrust.groups.index'));
    }
}
