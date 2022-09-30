@extends('admin.layouts.app')

@section('panel')
<div class="row">

    <div class="col-lg-12">
        <div class="card">
            <div class="card">
                <div class="card-body p-0">
                            <div class="table-responsive--md table-responsive">
                                <table class="table table--light">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Porcentaje</th>
                                            <th>Status</th>
                                            <th class="text-center">@lang('Action')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($data as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            
                                            <td>{{ $item->percentage }} %</td>

                                            <td>
                                                @if($item->status == 1)
                                                    Activo                                                  
                                                @else
                                                    Inactivo
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <a href="javascript:void(0)" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-percentage="{{ $item->percentage }}" class="icon-btn btn--primary mr-1 editBtn" data-toggle="tooltip" data-title="Editar">
                                                    <i class="la la-pencil-alt"></i>
                                                </a>

                                                @if($item->status==1)    
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="Desactivar"
                                                        class="icon-btn btn--danger delete-btn ml-1"
                                                        data-id='{{ $item->id }}' data-status='{{$item->status}}'>                                          
                                                        <i class="las la-ban"></i>
                                                    </button>
                                                @else 
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="Activar"
                                                        class="icon-btn btn--success delete-btn ml-1"
                                                        data-id='{{ $item->id }}' data-status='{{$item->status}}'>                                          
                                                        <i class="las la-check"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                </div>
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="editModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">IVA</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>

                    <form  action="" method="POST" name="editModal" id="editModal">
                        @csrf
                        <div class="modal-body">
                            
                            <div class="form-group name">
                                <label for="">Nombre</label>
                                <input type="text" name="nombre" id="" class="form-control" placeholder="Nombre"/>
                            </div>

                            <label>Porcentaje</label>
                            <div class="input-group">                              
                                <input type="text" class="form-control integer-validation" name="percentage" placeholder="Porcentaje">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn--primary">@lang('Save')</button>
                        </div>
                    </form>
                </div>
            </div>
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
<a href="{{ route('admin.products.all') }}" class="btn btn-sm btn--primary box--shadow1 text--small"><i class="las la-backward"></i>@lang('Go Back')</a>
@endpush

@push('breadcrumb-plugins')
    <a href="#" class="btn btn-sm btn--primary box--shadow1 text-white text--small editBtn">
        <i class="la la-plus"></i> @lang('Add New')
    </a>
@endpush 

@push('script')
<script>
    'use strict';
    (function($){

        $('.editBtn').on('click', function(){
            var modal       = $('#editModal');

            if($(this).data('name') || $(this).data('percentage')){
                modal.find('input[name=nombre]').val($(this).data('name'));
                modal.find('input[name=percentage]').val($(this).data('percentage'));
            }
            else{
                modal.find('input[name=nombre]').val('');
                modal.find('input[name=percentage]').val('');
            }
            
            var form = document.getElementById('editModal');
            if($(this).data('id')){
                form.action = '{{ route('admin.products.iva.add', '') }}' + '/' + $(this).data('id') ;
                document.editModal.action = '{{ route('admin.products.iva.add', '') }}' + '/' + $(this).data('id') ;
            }
            else{
                form.action = '{{ route('admin.products.iva.add', '') }}' + '/' + 0 ;
                document.editModal.action = '{{ route('admin.products.iva.add', '') }}' + '/' + 0 ;
            }
            
            modal.modal('show');
        });

        $('.delete-btn').on('click', function ()
            {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');
                var status      = $(this).data('status');
                var form    = document.getElementById('deletePostForm');

                if(status==1){
                    modal.find('.modal-title').text('Desactivar IVA');
                    modal.find('.modal-body').text('{{ trans("¿Seguro de desactivar este IVA?") }}');
                }
                else{
                    modal.find('.modal-title').text('Activar IVA');
                    modal.find('.modal-body').text('{{ trans("¿Seguro de activar este IVA?") }}');
                }
                

                form.action = '{{ route('admin.products.iva.desactivate', '') }}' + '/' + id;
                modal.modal('show');
            });
    })(jQuery)

</script>
@endpush
