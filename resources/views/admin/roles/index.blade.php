@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-lg-4 mb-3">
                            <form
                                action="{{ request()->routeIs('admin.roles.trashed') ? route('admin.roles.trashed.search') : route('admin.roles.search') }}"
                                method="GET">
                                <div class="input-group has_append">
                                    <input type="text" name="search" class="form-control" placeholder="Buscar..."
                                        value="{{ request()->search ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn--success" id="search-btn" type="submit"><i
                                                class="la la-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($roles as $rol)
                                    <tr>
                                        <td data-label="@lang('S.N.')">
                                            {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                                        </td>
                                        <td data-label="@lang('Name')">
                                           {{$rol->name}}
                                        </td>
                                        <td data-label="@lang('Action')">
                                            @if (!$rol->trashed())
                                                
                                            <a href="javascript:void(0)"
                                                data-toggle="tooltip" data-placement="top" title="Editar"
                                                data-rol="{{ $rol }}" data-toggle="modal"
                                                class="icon-btn {{ $rol->trashed()?'':'edit-btn' }}">
                                                <i class="la la-pencil"></i>
                                            </a>
                                            @endif

                                            <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $rol->trashed()?'Restaurar':'Eliminar' }}"
                                                class="icon-btn btn--{{ $rol->trashed()?'success':'danger' }} delete-btn ml-1"
                                                data-type="{{ $rol->trashed()?'Restaurar':'delete' }}"
                                                data-id='{{ $rol->id }}'>
                                                <i class="las la-{{ $rol->trashed()?'trash-restore':'trash' }}"></i>
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
                    {{ $roles->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
                </div>
            </div>
        </div>
    </div>

    <div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal modal-dialog-centered" role="document">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.roles.store', 0) }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="row">
                            
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Nombre del Rol</label>
                                    <input type="text" class="form-control" placeholder="Nombre del Rol"
                                        value="{{ old('name') }}" name="name" required />
                                    <small class="form-text text-muted"><i class="las la-info-circle"></i>Debe Ser
                                        Único</small>
                                </div>                               
                            </div>
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Descripci&oacute;n</label>
                                    <input type="text" class="form-control" placeholder="Descripci&oacute;n del Rol"
                                        value="{{ old('description') }}" name="description" required />
                                </div>                               
                            </div>
                        </div>
                        <button type="submit" class="btn btn-block btn--success mr-2">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" action="" method="POST">
                    <div class="modal-body">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nombre del Rol</label>
                                    <input type="text" class="form-control" placeholder="Nombe del Rol"
                                        name="name" />
                                    <small class="form-text text-muted"><i class="las la-info-circle"></i>Debe Ser Único</small>
                                </div>
                            </div>
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Descripci&oacute;n</label>
                                    <input type="text" class="form-control" placeholder="Descripci&oacute;n del Rol"
                                        value="{{ old('description') }}" name="description" required />
                                </div>                               
                            </div>
                        </div>
                        <button type="submit" class="btn btn-block btn--success mr-2">Grabar</button>
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
                        <button type="button" class="btn btn-sm btn--dark"
                            data-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn-sm btn--danger">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection




@push('breadcrumb-plugins')

    @if (request()->routeIs('admin.roles.all'))
        <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i
                class="las la-plus"></i> Agregar Nuevo</button>
    @else
        @if (request()->routeIs('admin.roles.trashed.search'))
            <a href="{{ route('admin.roles.trashed') }}" class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @else
            <a href="{{ route('admin.roles.all') }}" class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @endif
    @endif

    @if (request()->routeIs('admin.roles.all'))
        <a href="{{ route('admin.roles.trashed') }}" class="btn btn-sm btn--danger box--shadow1 text--small"><i
                class="las la-trash-alt"></i>Borrados</a>
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
                var rol    = $(this).data('rol');
                modal.find('input[name=name]').val(rol.name);
                modal.find('input[name=description]').val(rol.description);
                
                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.roles.store', '') }}' + '/' + rol.id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'delete'){
                    modal.find('.modal-title').text('{{ trans("Borrar rol") }}');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de eliminar este rol ?") }}');
                }else{
                    modal.find('.modal-title').text('{{ trans("Restaurar rol") }}');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de restaurar este rol ?") }}');
                }

                form.action = '{{ route('admin.roles.delete', '') }}' + '/' + id;
                modal.modal('show');
            });

            $('.top_brand').on('change', function () {
                var id = $(this).data('id');
                var mode = $(this).prop('checked');

                var data = {
                    'id': id
                };
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    url: "{{ route('admin.brand.settop') }}",
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
