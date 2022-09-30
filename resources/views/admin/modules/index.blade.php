@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">

                <div class="row justify-content-end">
                    <div class="col-lg-4 mb-3">
                        <form action="{{ route('admin.modules.search') }}"
                            method="GET">
                            <div class="input-group has_append">
                                <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request()->search ?? '' }}">
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
                                <th>@lang('Status')</th>
                                <th>@lang('Role')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($modules as $module)
                                <tr>
                                    <td data-label="#">
                                        {{ ($modules->currentPage()-1) * $modules->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="@lang('Name')">
                                       {{ $module->name }}
                                    </td>
                                    <td data-label="@lang('Status')">
                                        @if($module->status == 1)
                                            Activo
                                        @else
                                            Inactivo
                                        @endif
                                    </td>
                                    <td data-label="@lang('Role')">
                                        @forelse($module['roles'] as $role)
                                            {{$role['name']}} <br>
                                        @empty
                                            No hay rol asignado
                                        @endforelse
                                    </td>

                                    <td data-label="@lang('Action')">

                                        <button 
                                            data-module="{{ $module }}" 
                                            data-toggle="modal" 
                                            title="@lang('Edit')" 
                                            data-target="#editModal" 
                                            data-id='{{ $module->id }}'
                                            class="icon-btn {{ 'edit-btn' }}"
                                        >
                                            <i class="la la-pencil"></i>
                                        </button>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $module['status'] == 1 ? trans('Desactivar') : trans('Activar') }}"
                                            class="icon-btn btn--{{ $module['status'] == 1 ? 'danger' : 'success' }} delete-btn ml-1"
                                            data-type="{{ 'delete' }}"
                                            data-id='{{ $module->id }}'>                                          
                                            <i class="las la-{{ $module['status'] == 1 ? 'ban' : 'check' }}"></i>
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
                {{ $modules->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">@lang('add_module')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.modules.store', 0) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        
                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Name')</label>
                                <input type="text" class="form-control" placeholder="@lang('enter_name')" value="{{ old('name') }}" name="name" required/>
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Path')</label>
                                <input type="text" class="form-control" placeholder="@lang('Path')" value="{{ old('path') }}" name="path" />
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Rol')</label>
                                @if($roles->count() > 0)
                                    <select multiple class="form-control" name="roles_id[]" required>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option> 
                                        @endforeach
                                    </select>
                                @else
                                <input type="text" value="No hay roles registrados" disabled/> 
                                @endif
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
                <h5 class="modal-title">@lang('Editar Modulo')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        
                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Name')</label>
                                <input type="text" class="form-control" placeholder="@lang('enter_name')" value="{{ old('name') }}" name="name" required readonly/>
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Path')</label>
                                <input type="text" class="form-control" placeholder="@lang('Path')" value="{{ old('path') }}" name="path" />
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">
                                <label>@lang('Rol')</label>
                                @if($roles->count() > 0)
                                    <select multiple class="form-control" name="roles_id[]" required>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option> 
                                        @endforeach
                                    </select>
                                @else
                                <input type="text" value="No hay roles registrados" disabled/> 
                                @endif
                            </div>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">@lang('Add')</button>
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
    @if(request()->routeIs('admin.modules.index'))
        <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i class="las la-plus"></i> Añadir Nuevo</button>
    @else
        @if(request()->routeIs('admin.modules.trashed.search'))
            <a href="{{ route('admin.modules.trashed') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> @lang('Go Back')
            </a>
        @else
            <a href="{{ route('admin.modules.index') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> @lang('Go Back')
            </a>
        @endif
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
                var modules    = $(this).data('module');
                console.log(modules)

                modal.find('input[name=name]').val(modules.name);
                modal.find('input[name=path]').val(modules.path);

                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.modules.store', '') }}' + '/' + modules.id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'delete'){
                    modal.find('.modal-title').text('{{ trans("Desactivar Módulo") }}');
                    modal.find('.modal-body').text('{{ trans("¿Está Seguro de querer Desactivar este Módulo?") }}');
                }else{
                    modal.find('.modal-title').text('{{ trans("Restaurar Módulo") }}');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('{{ trans("¿Está Seguro de querer Restaurar este Módulo?") }}');
                }

                form.action = '{{ route('admin.modules.delete', '') }}' + '/' + id;
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
