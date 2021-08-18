@extends('laratrust::panel.layout')

@section('title', "Edit {$modelKey}")

@section('content')
  <div>
  </div>
  <div class="flex flex-col">
    <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-32">
      <form
        method="POST"
        action="{{route('laratrust.groups-assignment.update', ['groups_assignment' => $user->id, 'model' => $modelKey])}}"
        class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200 p-8"
      >
        @csrf
        @method('PUT')
        <label class="block">
          <span class="text-gray-700">Name</span>
          <input
            class="form-input mt-1 block w-full bg-gray-200 text-gray-600"
            name="name"
            placeholder="this-will-be-the-code-name"
            value="{{$user->name ?? 'The model doesn\'t have a `name` attribute'}}"
            readonly
            autocomplete="off"
          >
        </label>
        <span class="block text-gray-700 mt-4">Groups</span>
        <div class="flex flex-wrap justify-start mb-4">
          @foreach ($groups as $group)
            <label class="inline-flex items-center mr-6 my-2 text-sm" style="flex: 1 0 20%;">
              <input
                type="checkbox"
                @if ($group->assigned && !$group->isRemovable)
                class="form-checkbox focus:shadow-none focus:border-transparent text-gray-500 h-4 w-4"
                @else
                class="form-checkbox h-4 w-4"
                @endif
                name="groups[]"
                value="{{$group->id}}"
                {!! $group->assigned ? 'checked' : '' !!}
                {!! $group->assigned && !$group->isRemovable ? 'onclick="return false;"' : '' !!}
              >
              <span class="ml-2 {!! $group->assigned && !$group->isRemovable ? 'text-gray-600' : '' !!}">
                {{$group->display_name ?? $group->name}}
              </span>
            </label>
          @endforeach
        </div>
        @if ($permissions)
          <span class="block text-gray-700 mt-4">Permissions</span>
          <div class="flex flex-wrap justify-start mb-4">
            @foreach ($permissions as $permission)
              <label class="inline-flex items-center mr-6 my-2 text-sm" style="flex: 1 0 20%;">
                <input
                  type="checkbox"
                  class="form-checkbox h-4 w-4"
                  name="permissions[]"
                  value="{{$permission->id}}"
                  {!! $permission->assigned ? 'checked' : '' !!}
                >
                <span class="ml-2">{{$permission->display_name ?? $permission->name}}</span>
              </label>
            @endforeach
          </div>
        @endif
        <div class="flex justify-end">
          <a
            href="{{route("laratrust.groups-assignment.index", ['model' => $modelKey])}}"
            class="btn btn-red mr-4"
          >
            Cancel
          </a>
          <button class="btn btn-blue" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
@endsection