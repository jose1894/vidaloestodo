@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">

                
                <div class="table-responsive--md  table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('Nombre')</th>
                                <th>@lang('Descripción')</th>
                                <th>@lang('Duración')</th>
                                <th>@lang('Costo')</th>
                                <th>@lang('Acción')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($plans as $plan)
                                <tr>
                                    <td data-label="#">
                                        {{ ($plans->currentPage()-1) * $plans->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="@lang('Nombre')">
                                       {{ $plan->name }}
                                    </td>
                                    <td data-label="@lang('Descripción')">
                                        {{ $plan->description }}
                                    </td>
                                    <td data-label="@lang('Duración')">
                                        {{ $plan->meses }} meses
                                    </td>
                                    <td data-label="@lang('Costo')">
                                        {{ $plan->product->base_price }} {{$general->cur_text}}
                                    </td>

                                    <td data-label="@lang('Action')">

                                        <button 
                                            data-plan="{{ $plan }}"
                                            data-toggle="modal" 
                                            title="@lang('Edit')" 
                                            data-target="#editModal" 
                                            data-id='{{ $plan->id }}'
                                            class="icon-btn {{ $plan->trashed()?'':'edit-btn' }}"
                                        >
                                            <i class="la la-pencil"></i>
                                        </button>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $plan['status'] == 1 ? trans('Desactivar') : trans('Activar') }}"
                                            class="icon-btn btn--{{ $plan['status'] == 1 ? 'danger' : 'success' }} delete-btn ml-1"
                                            data-type="{{ $plan->trashed()?'restore':'delete' }}"
                                            data-id='{{ $plan->id }}'>
                                            <i class="las la-{{ $plan['status'] == 1 ? 'ban' : 'check' }}"></i>
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
                {{ $plans->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Agregar tasa del día</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.plans.store', 0) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input id="name" type="text" class="form-control" name="name"
                                    value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Descripción</label>
                                <input id="description" type="text" class="form-control" name="description"
                                     value="{{ old('description') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meses">Duración en meses</label>
                                <input id="meses" step="any" type="number" min="1" class="form-control" name="meses"
                                     value="{{ old('meses') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_price">Precio</label>
                                <input id="base_price" step="any" type="number" min="1" class="form-control" name="base_price"
                                     value="{{ old('base_price') }}" required>
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
                <h5 class="modal-title">Editar Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input id="name" type="text" class="form-control" name="name"
                                    value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Descripción</label>
                                <input id="description" type="text" class="form-control" name="description"
                                     value="{{ old('description') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meses">Duración en meses</label>
                                <input id="meses" step="any" type="number" min="1" class="form-control" name="meses"
                                     value="{{ old('meses') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_price">Precio</label>
                                <input id="base_price" step="any" type="number" min="1" class="form-control" name="base_price"
                                     value="{{ old('base_price') }}" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">Editar</button>
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
                    <button type="submit" class="btn btn-sm btn--danger">Sí</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('breadcrumb-plugins')
    @if($plans->isEmpty())
        @if(request()->routeIs('admin.plans.index'))
            <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i class="las la-plus"></i> Añadir Nuevo</button>
        @else
            <a href="{{ route('admin.modules.index') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> @lang('Go Back')
            </a>
        @endif
    @endif

    @if(request()->routeIs('admin.modules.index'))
        <a href="{{ route('admin.modules.trashed') }}" class="btn btn-sm btn--danger box--shadow1 text--small"><i class="las la-trash-alt"></i>Destrozado</a>
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
                var plan    = $(this).data('plan');

                modal.find('input[name=name]').val(plan.name);
                modal.find('input[name=description]').val(plan.description);
                modal.find('input[name=meses]').val(plan.meses);
                modal.find('input[name=base_price]').val(plan.product.base_price);

                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.plans.store', '') }}' + '/' + plan.product_id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'delete'){
                    modal.find('.modal-title').text('Desactivar/activar plan');
                    modal.find('.modal-body').text('¿Seguro de descativar/activar este plan?');
                }else{
                    modal.find('.modal-title').text('Restaurar plan');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('¿Está Seguro que desea restaurar este plan?');
                }

                form.action = '{{ route('admin.plans.delete', '') }}' + '/' + id;
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

            //input decimales
            /*
            $("#tasa_del_dia").on({
                "focus": function (event) {
                    $(event.target).select();
                },
                "keyup": function (event) {
                    $(event.target).val(function (index, value ) {
                        return value.replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    });
                }
            }); */

        })(jQuery)
    </script>

@endpush
