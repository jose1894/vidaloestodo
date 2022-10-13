@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">

                <div class="row justify-content-end">
                    <div class="col-lg-4 mb-3">
                        <form action="{{ request()->routeIs('admin.carrier.trashed')?route('admin.carrier.trashed.search'):route('admin.carrier.search') }}"
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
                                <th>@lang('S.N.')</th>
                                <th>@lang('Logo')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('Carrier offices')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($carriers as $carrier)
                                <tr>
                                    <td data-label="@lang('S.N.')">
                                        {{ ($carriers->currentPage()-1) * $carriers->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="@lang('Logo')">
                                        <div class="thumbnails d-inline-block">
                                            <div class="thumb">
                                                <a href="{{ getImage(imagePath()['carrier']['path'].  '/'. @$carrier->logo, imagePath()['carrier']['size']) }}" class="image-popup">
                                                    <img src="{{ getImage(imagePath()['carrier']['path'].  '/'. @$carrier->logo, imagePath()['carrier']['size']) }}" alt="@lang('image')">
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="@lang('Name')">
                                       {{ $carrier->name }}
                                    </td> 
                                    <td data-label="@lang('Carrier offices')">{{ $carrier->offices->count() }}</td>                                                    
                                    <td data-label="@lang('Action')">
                                        <a href="javascript:void(0)"
                                            data-toggle="tooltip" data-placement="top" title="Editar"
                                            data-carrier="{{ $carrier }}" data-toggle="modal"
                                            data-image="{{ getImage('assets/images/carrier/'. $carrier->logo, imagePath()['carrier']['size']) }}"
                                            class="icon-btn {{ $carrier->trashed() ? '' : 'edit-btn' }}">
                                            <i class="la la-pencil"></i>
                                        </a>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $carrier->trashed()?'Restaurar':'Eliminar' }}"
                                            class="icon-btn btn--{{ $carrier->trashed()?'success':'danger' }} delete-btn ml-1"
                                            data-type="{{ ($carrier->trashed() ? 'Restaurar':'Eliminar') }}"
                                            data-id='{{ $carrier->id }}'>
                                            <i class="las la-{{ $carrier->trashed()?'trash-restore':'trash' }}"></i>
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
                {{ $carriers->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Transportista</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.carrier.store', 0) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="payment-method-item">
                                <label>@lang('Logo')</label>
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="profilePicPreview" style="background-image: url({{ getImage(null, imagePath()['carrier']['size']) }})">
                                                <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" name="image_input" id="profilePicUpload1" accept=".png, .jpg, .jpeg" required>
                                            <label for="profilePicUpload1" class="bg--primary">Subir Logo</label>
                                            <small class="form-text text-muted mb-3"> Archivos Soportados
                                                <b>@lang('jpeg, jpg, png')</b>.
                                                La imagen cambiará de tamaño a {{imagePath()['carrier']['size']}} px</b>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label>Nombre del Transportista</label>
                                <input type="text" class="form-control" placeholder="Nombre del Transportista" value="{{ old('name') }}" name="name" required/>
                                <small class="form-text text-muted"><i class="las la-info-circle"></i>Debe ser &uacute;nico</small>
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
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Marca</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">

                            <div class="payment-method-item">
                                <label>@lang('Logo')</label>
                                <div class="image-upload">
                                    <div class="thumb">
                                        <div class="avatar-preview">
                                            <div class="profilePicPreview" style="background-image: url({{ getImage(null, imagePath()['carrier']['size']) }})">
                                                <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" class="profilePicUpload" name="image_input"
                                                id="profilePicUpload" accept=".png, .jpg, .jpeg" >
                                            <label for="profilePicUpload1" class="bg--primary">Subir Logo</label>
                                            <small class="form-text text-muted mb-3"> Archivos Soportados
                                                <b>@lang('jpeg, jpg, png')</b>.
                                                La imagen cambiará de tamaño a {{imagePath()['carrier']['size']}} px</b>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre del Transportista</label>
                                <input type="text" class="form-control" placeholder="Nombe del transportista" name="name" />
                                <small class="form-text text-muted"><i class="las la-info-circle"></i>Debe Ser Única</small>
                            </div>

                            
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">Modificar</button>
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
    @if(request()->routeIs('admin.carrier.index'))
        <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i class="las la-plus"></i> Agregar Nuevo</button>
    @else
        @if(request()->routeIs('admin.carrier.trashed.search'))
            <a href="{{ route('admin.carrier.trashed') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @else
            <a href="{{ route('admin.carrier.index') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @endif
    @endif

    @if(request()->routeIs('admin.carrier.index'))
        <a href="{{ route('admin.carrier.trashed') }}" class="btn btn-sm btn--danger box--shadow1 text--small"><i class="las la-trash-alt"></i>Borrados</a>
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
                var carrier    = $(this).data('carrier');
                var image       = $(this).data('image');
                
                modal.find('.profilePicPreview').css('background-image', `url(${image})`);
                modal.find('input[name=name]').val(carrier.name);                

                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.carrier.store', '') }}' + '/' + carrier.id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'Eliminar'){
                    modal.find('.modal-title').text('{{ trans("Borrar transportista") }}');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de eliminar este transportista ?") }}');
                }else{
                    modal.find('.modal-title').text('{{ trans("Restaurar transportista") }}');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de restaurar este transportista ?") }}');
                }

                form.action = '{{ route('admin.carrier.delete', '') }}' + '/' + id;
                modal.modal('show');
            });

            

        })(jQuery)
    </script>

@endpush
