@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">
                <div class="row justify-content-end">
                    <div class="col-lg-4 mb-3">
                        <form action="{{ route('admin.users.search') }}"
                            method="GET">
                            <div class="input-group has_append">
                                <input type="text" name="search" class="form-control" placeholder="@lang('Search')..." value="{{ request()->search ?? '' }}">
                                <div class="input-group-append">
                                    <button class="btn btn--success" id="search-btn" type="submit"><i class="la la-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive--md  table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Role')</th>
                                <th>@lang('Modulos')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($users as $user)
                                <tr>
                                    <td data-label="#">
                                        {{ ($users->currentPage()-1) * $users->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="@lang('Name')">
                                       {{ $user->firstname ? $user->firstname : $user->name }} {{ $user->lastname }}
                                    </td>
                                    <td data-label="@lang('Email')">
                                        {{ $user->email }}
                                    </td>
                                    <td data-label="@lang('Role')">
                                        {{ $user['roles']['name'] }}
                                    </td>
                                    <td data-label="@lang('Modulos')">
                                        @if(isset($user['roles']['modules']) && ($user['roles']['modules']->count() > 0 ) ) 
                                            @foreach($user['roles']['modules'] as $module)
                                                {{$module->name }} </br>                                  
                                            @endforeach
                                        @else
                                            @lang('not_modules')
                                        @endif
                                    </td>

                                    <td data-label="@lang('Action')">
                                        
                                        @if(!is_null($user->plan_users) && count($user->plan_users) > 0)
                                            <button
                                                title="Usuario Prime"
                                                data-toggle="tooltip" 
                                                data-placement="top" 
                                                
                                                class="icon-btn btn--warning"
                                            >
                                                <i class="la la-star"></i>
                                            </button>
                                        @endif

                                        <button 
                                            data-user="{{ $user }}" 
                                            data-toggle="modal" 
                                            title="@lang('Edit')" 
                                            data-target="#editModal" 
                                            data-id='{{ $user->id }}'
                                            class="icon-btn {{ 'edit-btn' }}"
                                        >
                                            <i class="la la-pencil"></i>
                                        </button>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $user['status'] == 1 ? trans('Desactivar') : trans('Activar') }}"
                                            class="icon-btn btn--{{ $user['status'] == 1 ? 'danger' : 'success' }} delete-btn ml-1"
                                            data-type="{{ 'delete' }}"
                                            data-id='{{ $user->id }}'>                                          
                                            <i class="las la-{{ $user['status'] == 1 ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($empty_message) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="card-footer py-4">
                {{ $users->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">@lang('add_user')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.register.admin', 0) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="is_moderador" class="form-control" name="is_moderador" value="is_moderador" >
                    <div class="row">                      

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="firstname">@lang('First Name')</label>
                                <input id="firstname" type="text" class="form-control" name="firstname"
                                    value="{{ old('firstname') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lastname">@lang('Last Name')</label>
                                <input id="lastname" type="text" class="form-control" name="lastname"
                                     value="{{ old('lastname') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username">@lang('Username')</label>
                                <input id="username" type="text" class="form-control" name="username"
                                    value="{{ old('username') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">@lang('Email')</label>
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>                           
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">@lang('Role')</label>
                                <select class="form-control" id="role_id" name="role_id"> 
                                    @forelse($roles as $rol)
                                        @if($rol->name == 'User')
                                            <option value="{{$rol->id}}"> Usuario </option>
                                        @else
                                            <option value="{{$rol->id}}"> {{$rol->name}} </option>
                                        @endif
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">No hay roles registrados.</td>
                                        </tr>
                                    @endforelse
                                </select>
                            </div>                           
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">@lang('Password')</label>
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">
                            </div>
                            
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password-confirm">@lang('Confirm Password')</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>                      
                        </div>

                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">@lang('Add')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Editar Usuario')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="{{ route('admin.register.admin', isset($user->id) ? $user->id : '') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf

                    <div class="row">                      

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="firstname">@lang('First Name')</label>
                                <input id="firstname" type="text" class="form-control" name="firstname"
                                    value="{{ old('firstname') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lastname">@lang('Last Name')</label>
                                <input id="lastname" type="text" class="form-control" name="lastname"
                                     value="{{ old('lastname') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username">@lang('Username')</label>
                                <input id="username" type="text" class="form-control" name="username"
                                    value="{{ old('username') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">@lang('Email')</label>
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>                           
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">@lang('Role')</label>
                                <select class="form-control" id="role_id_edit" name="role_id"> 
                                    @forelse($roles as $rol)
                                        <option value="{{$rol->id}}" {{ old('role_id') == $rol->id ? 'selected' : '' }}> {{$rol->name}} </option>
                                        {{-- @if($rol->name == 'User')
                                            <option value="{{$rol->id}}" {{ old('role_id') == $rol->id ? 'selected' : '' }}> Usuario </option>
                                        @else
                                            <option value="{{$rol->id}}" {{ old('role_id') == $rol->id ? 'selected' : '' }}> {{$rol->name}} </option>
                                        @endif --}}
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">No hay roles registrados.</td>
                                        </tr>
                                    @endforelse
                                </select>
                            </div>                           
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">@lang('Password')</label>
                                <input id="password" type="password" class="form-control" name="password" autocomplete="new-password">
                            </div>
                            
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password-confirm">@lang('Confirm Password')</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                            </div>                      
                        </div>

                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">@lang('Update')</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- REMOVE METHOD MODAL --}}

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="" method="POST" id="deletePostForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn--dark" data-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn btn-sm btn--danger">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('breadcrumb-plugins')
    @if(request()->routeIs('admin.users.all'))
        <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i class="las la-plus"></i> @lang('Add New')</button>

        <a class="btn btn-sm btn--warning box--shadow1 text--small" href="{{route('admin.users.all.prime')}}">
            <i class="la la-star"></i> Usuarios Prime
        </a>
    @else
            <a href="{{ route('admin.users.all') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> @lang('Go Back')
            </a>
    @endif
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-iconpicker.min.css') }}">
@endpush

@push('script')

    <script>
        'use strict';
        (function($){

            $('.image-popup').magnificPopup({
                type: 'image'
            });

            $('#addModal, #editModal').on('shown.bs.modal', function (e) {
                $(document).off('focusin.modal');
            });

            $('.edit-btn').on('click', function () {
                var modal = $('#editModal');
                var user    = $(this).data('user');
                $('#role_id_edit').empty();

                modal.find('input[name=firstname]').val(user.firstname ? user.firstname : user.name.split(' ')[0] ? user.name.split(' ')[0] : '');
                modal.find('input[name=lastname]').val(user.lastname ? user.lastname : user.name.split(' ')[1] ? user.name.split(' ')[1] : '');
                modal.find('input[name=username]').val(user.username);
                modal.find('input[name=email]').val(user.email);
                modal.find('select[name=role_id]').val(user.role_id);
                let $option = $('<option />', {
                    text: user.roles.name,
                    value: user.role_id,
                });
                $('#role_id_edit').prepend($option);

                modal.find('input[name=password]').val(user.password); 
                modal.find('input[name=password_confirmation]').val(user.password_confirmation); 

                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.register.admin', '') }}' + '/' + user.id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'delete'){
                    modal.find('.modal-title').text('{{ trans("Desactivar usuario") }}');
                    modal.find('.modal-body').text('{{ trans("¿Seguro de desactivar este usuario?") }}');
                }else{
                    modal.find('.modal-title').text('{{ trans("Restore user") }}');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('{{ trans("Are you sure to restore this user?") }}');
                }

                form.action = '{{ route('admin.users.desactivate', '') }}' + '/' + id;
                modal.modal('show');
            });

            $('.top_module').on('change', function () {
                var id = $(this).data('id');
                var mode = $(this).prop('checked');

                var data = {
                    'id': id
                };
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    url: "{{ route('admin.modules.settop') }}",
                    method: 'POST',
                    data: data,
                    success: function (result) {
                        notify('success', result.success);
                    }
                });
            });

        })(jQuery)
    </script>

@endpush
