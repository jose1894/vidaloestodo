@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">

                <div class="row justify-content-end">
                    <div class="col-lg-4 mb-3">
                        <form action="{{ request()->routeIs('admin.carrier-office.trashed')?route('admin.carrier-office.trashed.search'):route('admin.carrier-office.search') }}"
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
                                <th>@lang('Carrier')</th>
                                <th>@lang('State')</th>
                                <th>@lang('City')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($carrierOffices as $office)
                                <tr>
                                    <td data-label="@lang('S.N.')">
                                        {{ ($carrierOffices->currentPage()-1) * $carrierOffices->perPage() + $loop->iteration }}
                                    </td>
                                    
                                    <td data-label="@lang('Carrier')">
                                       {{ $office->carrier->name }}
                                    </td>
                                    <td data-label="@lang('State')">
                                       {{ $office->state->name }}
                                    </td>
                                    <td data-label="@lang('City')">
                                       {{ $office->city->name }}
                                    </td>

                                    <td data-label="@lang('Name')">
                                       {{ $office->name }}
                                    </td>                                                     
                                    <td data-label="@lang('Action')">
                                        <a href="javascript:void(0)"
                                            data-toggle="tooltip" data-placement="top" title="Editar"
                                            data-carrieroffice="{{ $office }}" data-toggle="modal"
                                            class="icon-btn {{ $office->trashed() ? '' : 'edit-btn' }}">
                                            <i class="la la-pencil"></i>
                                        </a>

                                        <button type="button" data-toggle="tooltip" data-placement="top" title="{{ $office->trashed()?'Restaurar':'Eliminar' }}"
                                            class="icon-btn btn--{{ $office->trashed()?'success':'danger' }} delete-btn ml-1"
                                            data-type="{{ ($office->trashed() ? 'Restaurar':'Eliminar') }}"
                                            data-id='{{ $office->id }}'>
                                            <i class="las la-{{ $office->trashed()?'trash-restore':'trash' }}"></i>
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
                {{ $carrierOffices->appends(['search'=>request()->search ?? null])->links('admin.partials.paginate') }}
            </div>
        </div>
    </div>
</div>
@include('admin.carrier-office.create-modal')
@include('admin.carrier-office.edit-modal')
@include('admin.carrier-office.remove-modal')

@endsection

@push('breadcrumb-plugins')
    @if(request()->routeIs('admin.carrier-office.index'))
        <button data-toggle="modal" data-target="#addModal" class="btn btn-sm btn--success box--shadow1 text--small"> <i class="las la-plus"></i> Agregar Nuevo</button>
    @else
        @if(request()->routeIs('admin.carrier-office.trashed.search'))
            <a href="{{ route('admin.carrier-office.trashed') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @else
            <a href="{{ route('admin.carrier-office.index') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @endif
    @endif

    @if(request()->routeIs('admin.carrier-office.index'))
        <a href="{{ route('admin.carrier-office.trashed') }}" class="btn btn-sm btn--danger box--shadow1 text--small"><i class="las la-trash-alt"></i>Borrados</a>
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

            $('.edit-btn').on('click', async function () {
                var modal = $('#editModal');
                var carrierOffice    = $(this).data('carrieroffice');
                modal.find('select[id=edit-carrier_id]').val(carrierOffice.carrier_id).trigger('change');                
                modal.find('select[id=edit-state_id]').val(carrierOffice.state_id).trigger('change');  
                modal.find('select[id=edit-city_id]').val(carrierOffice.city_id).trigger('change');
                modal.find('input[id=edit-name]').val(carrierOffice.name);                
                modal.find('input[id=edit-code]').val(carrierOffice.code);                
                modal.find('textarea[id=edit-address]').val(carrierOffice.address);
                var form = document.getElementById('editForm');
                form.action = '{{ route('admin.carrier-office.store', '') }}' + '/' + carrierOffice.id;
                modal.modal('show');
            });

            $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var type    = $(this).data('type');
                var form    = document.getElementById('deletePostForm');

                if(type == 'Eliminar'){
                    modal.find('.modal-title').text('{{ trans("Borrar oficina") }}');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de eliminar este oficina ?") }}');
                }else{
                    modal.find('.modal-title').text('{{ trans("Restaurar oficina") }}');
                    modal.find('.btn--danger').removeClass('btn--danger').addClass('btn--success');
                    modal.find('.modal-body').text('{{ trans("¿Estás seguro de restaurar este oficina ?") }}');
                }

                form.action = '{{ route('admin.carrier-office.delete', '') }}' + '/' + id;
                modal.modal('show');
            });            

        })(jQuery)
    </script>

@endpush
