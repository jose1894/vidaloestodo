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
                                <th>@lang('Tasa del día')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($rates as $rate)
                                <tr>
                                    <td data-label="#">
                                        {{ ($rates->currentPage()-1) * $rates->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="@lang('Tasa')">
                                       {{ $rate->tasa_del_dia }}
                                    </td>
                                    <td data-label="@lang('Status')">
                                        @if($rate->status == 1)
                                            Activo
                                        @else
                                            Inactivo
                                        @endif
                                    </td>

                                    <td data-label="@lang('Action')">

                                        <button 
                                            data-rate="{{ $rate }}"
                                            data-toggle="modal" 
                                            title="@lang('Edit')" 
                                            data-target="#editModal" 
                                            data-id='{{ $rate->id }}'
                                            class="icon-btn {{ $rate->trashed()?'':'edit-btn' }}"
                                        >
                                            <i class="la la-pencil"></i>
                                        </button>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $rate['status'] == 1 ? trans('Desactivar') : trans('Activar') }}"
                                            class="icon-btn btn--{{ $rate['status'] == 1 ? 'danger' : 'success' }} delete-btn ml-1"
                                            data-type="{{ $rate->trashed()?'restore':'delete' }}"
                                            data-id='{{ $rate->id }}'>
                                            <i class="las la-{{ $rate['status'] == 1 ? 'ban' : 'check' }}"></i>
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
                {{ $rates->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
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
            <form action="{{ route('admin.rates.store', 0) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        
                        <div class="col-md-4">

                            <div class="form-group">
                                <label>Tasa del día</label>                             
                                <input step="any" type="number" name="tasa_del_dia" value="{{ old('tasa_del_dia') }}" required>
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
                <h5 class="modal-title">Editar Tasa del Día</h5>
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
                                <label>Tasa del día</label>                             
                                <input step="any" type="number" name="tasa_del_dia" value="{{ old('tasa_del_dia') }}" required>
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
    @if($rates->isEmpty())
        @if(request()->routeIs('admin.rates.index'))
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
                var rate    = $(this).data('rate');

                modal.find('input[name=tasa_del_dia]').val(rate.tasa_del_dia);

                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.rates.store', '') }}' + '/' + rate.id;

                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'delete'){
                    modal.find('.modal-title').text('Desactivar/activar Tasa');
                    modal.find('.modal-body').text('¿Seguro de descativar/activar esta Tasa?');
                }else{
                    modal.find('.modal-title').text('Restaurar Tasa');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('¿Está Seguro que desea restaurar esta Tasa?');
                }

                form.action = '{{ route('admin.rates.delete', '') }}' + '/' + id;
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
