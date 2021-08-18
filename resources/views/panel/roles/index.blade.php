@extends('laratrust::panel.layout')

@section('title', 'Groups')

@section('content')
  <div class="flex flex-col">
    <a
      href="{{route('laratrust.groups.create')}}"
      class="self-end bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded"
    >
      + New Group
    </a>
    <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="mt-4 align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="th">Id</th>
              <th class="th">Display Name</th>
              <th class="th">Name</th>
              <th class="th"># Permissions</th>
              <th class="th"></th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @foreach ($groups as $group)
            <tr>
              <td class="td text-sm leading-5 text-gray-900">
                {{$group->id}}
              </td>
              <td class="td text-sm leading-5 text-gray-900">
                {{$group->display_name}}
              </td>
              <td class="td text-sm leading-5 text-gray-900">
                {{$group->name}}
              </td>
              <td class="td text-sm leading-5 text-gray-900">
                {{$group->permissions_count}}
              </td>
              <td class="flex justify-end px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                @if (\Laratrust\Helper::groupIsEditable($group))
                <a href="{{route('laratrust.groups.edit', $group->id)}}" class="text-blue-600 hover:text-blue-900">Edit</a>
                @else
                <a href="{{route('laratrust.groups.show', $group->id)}}" class="text-blue-600 hover:text-blue-900">Details</a>
                @endif
                <form
                  action="{{route('laratrust.groups.destroy', $group->id)}}"
                  method="POST"
                  onsubmit="return confirm('Are you sure you want to delete the record?');"
                >
                  @method('DELETE')
                  @csrf
                  <button
                    type="submit"
                    class="{{\Laratrust\Helper::groupIsDeletable($group) ? 'text-red-600 hover:text-red-900' : 'text-gray-600 hover:text-gray-700 cursor-not-allowed'}} ml-4"
                    @if(!\Laratrust\Helper::groupIsDeletable($group)) disabled @endif
                  >Delete</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  {{ $groups->links('laratrust::panel.pagination') }}
@endsection
