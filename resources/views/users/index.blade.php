@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Users Management</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success mb-2" href="{{ route('users.create') }}"><i class="fa fa-plus"></i> Create New User</a>
        </div>
    </div>
</div>

<table class="table table-bordered">
   <tr>
       <th>No</th>
       <th>Name</th>
       <th>Email</th>
       <th>Roles</th>
       <th width="280px">Action</th>
   </tr>
   @foreach ($data as $key => $user)
    <tr>
        <td>{{ ++$i }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
          @if(!empty($user->roles_info))
          @foreach ($user->roles_info as $role)
               <label class="badge bg-success">{{ $role['role_name'] }} ({{ $role['guard_name'] }})</label> 

            @endforeach
          @endif
        </td>
        <td>
        @can('user-show')  
        <a class="btn btn-info btn-sm" href="{{ route('users.show',$user->id) }}"><i class="fa-solid fa-list"></i> Show</a>
            @else
            <a class="btn btn-info btn-sm disable" href="{{ route('users.show',$user->id) }}"><i class="fa-solid fa-list"></i> Show</a>
            @endcan
            @can('user-edit')  
            <a class="btn btn-primary btn-sm" href="{{ route('users.edit',$user->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
            @else
            <a class="btn btn-primary btn-sm disable" href="{{ route('users.edit',$user->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
            @endcan
            @can('user-delete')  
            <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-md">Delete</button>
            </form>
            @else
            <button type="submit" class="btn btn-danger btn-md" disabbled>Delete</button>
             @endcan

        </td>
    </tr>
 @endforeach
</table>

{!! $data->links('pagination::bootstrap-5') !!}

 @endsection
