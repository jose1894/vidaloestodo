@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-4 categories-tree">
        <div class="card b-radius--10">
            <div class="card-body">
                <button class="btn btn--dark btn--shadow-default close-tree mx-3 mb-3" type="button" value="1">Desplegar todo</button>

                <div class="file-tree-wrapper">
                    <ul class="file-tree d-none">
                        @foreach ($categories as $category)
                            <li class="folder-root @if($category->allSubcategories->count()>0) open @endif" >
                                @if($category->allSubcategories->count()>0)
                                <i class="las la-minus-circle file-opener-i"></i>
                                @endif

                                <a href="javascript:void(0)" class="parent" data-category="{{ $category }}" data-image="{{ getImage(imagePath()['category']['path']. '/' .$category->image, imagePath()['category']['size']) }}">
                                    {{ __($category->name) }} <small><strong>Pos. {{ $category->position}}</strong> </small>
                                </a>

                                <span type="button" class="p-0 ml-3 delete-btn bg-danger d-none" data-id="{{ $category->id }}"><i class="las la-times-circle"></i></span>

                                    @foreach ($category->allSubcategories as $subcategory)
                                    <ul>
                                        @include('admin.categories.subcategory', ['subcategory' => $subcategory])
                                    </ul>
                                    @endforeach
                                </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card b-radius--10 ">
            <div class="card-body">
                <form action="{{ route('admin.category-store', 0) }}" method="POST" enctype="multipart/form-data" id="addForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="parent_id">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Imagen</label>
                            </div>
                            <div class="col-md-10">
                                <div class="payment-method-item">
                                    <div class="payment-method-header d-flex flex-wrap">
                                        <div class="thumb">
                                            <div class="avatar-preview">
                                                <div class="profilePicPreview" style="background-image: url('{{ getImage(null, imagePath()['category']['size']) }}')"></div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" name="image_input" class="profilePicUpload" id="image" accept=".png, .jpg, .jpeg"/ required>
                                                <label for="image" class="bg--primary"><i class="la la-pencil"></i></label>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mb-2"> Archivos soportados:
                                        <b>@lang('jpeg, jpg, png')</b>.
                                        @lang("La imagen cambiará de tamaño a ". imagePath()['category']['size'] ."px")</b>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Nombre</label>
                            </div>

                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Nombre de la Categoría" value="{{ old('name') }}" name="name" required/>
                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-md-2">
                                <label class="font-weight-bold">Ícono</label>
                            </div>
                            <div class="col-md-10">
                                <div class="input-group has_append">
                                    <input type="text" class="form-control icon-name" name="icon" value="{{ old('icon') }}" placeholder="Ícono" required>

                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary iconPicker" data-icon="fas fa-home" role="iconpicker"></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Meta Título</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title') }}" placeholder="Meta Título">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Meta Descripción</label>
                            </div>

                            <div class="col-md-10">
                                <textarea name="meta_description" rows="5" class="form-control">{{ old('meta_description') }} </textarea>
                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-md-2">
                                <label class="font-weight-bold">Palabras Clave</label>
                            </div>
                            <div class="col-md-10">
                                <select name="meta_keywords[]" class="form-control select2-auto-tokenize"  multiple="multiple"></select>
                                <small class="form-text text-muted">
                                    <i class="las la-info-circle"></i>
                                    Escriba o presione Intro para separar las palabras clave
                                </small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Posición</label>
                            </div>
                            <div class="col-md-10">
                                <input type="number" class="form-control" name="position" value="{{ old('position') }}" placeholder="Posición Categoría">
                            </div>
                        </div>
                        <div class="form-group row">

                            <div class="col-md-2">
                                <label class="font-weight-bold">Resaltar en</label>
                            </div>
                            <div class="col-md-10">
                                <div class="custom-control custom-checkbox form-check-primary">
                                    <input type="checkbox" name="top_category" value="1" class="custom-control-input" id="top_category">
                                    <label class="custom-control-label" for="top_category">Categoría superior</label>
                                </div>

                                <div class="custom-control custom-checkbox form-check-primary">
                                    <input type="checkbox" name="special_category" value="1" class="custom-control-input" id="special_category">
                                    <label class="custom-control-label" for="special_category">Categoría Especial</label>
                                </div>

                                <div class="custom-control custom-checkbox form-check-primary">
                                    <input type="checkbox" name="filter_menu" value="1" class="custom-control-input" id="filter_menu">
                                    <label class="custom-control-label" for="filter_menu">Menú de filtro</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block btn--success mr-2">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- REMOVE METHOD MODAL --}}

<div class="modal fade" id="deleteModal">
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
                    <button type="button" class="btn btn-sm btn--dark" data-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-sm btn--danger">Si</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('breadcrumb-plugins')
    @if(request()->routeIs('admin.category.all'))
        <button class="btn btn-sm btn--primary add-parent mb-xl-0 mb-2 box--shadow1 text--small"> <i class="las la-plus"></i>Agregar Padre</button>
        <button class="btn btn-sm btn--success add-chlid mb-xl-0 mb-2 box--shadow1 text--small" disabled> <i class="las la-plus"></i> Agregar Hijo</button>
    @else
        @if(request()->routeIs('admin.category.trashed.search'))
            <a href="{{ route('admin.category.trashed') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @else
            <a href="{{ route('admin.category.all') }}"
                class="btn btn-sm btn--primary box--shadow1 text--small">
                <i class="la la-fw la-backward"></i> Regresar
            </a>
        @endif
    @endif

    @if(request()->routeIs('admin.category.all'))
        <a href="{{ route('admin.category.trashed') }}" class="btn btn-sm btn--danger mb-xl-0 mb-2 box--shadow1 text--small"><i class="las la-trash-alt"></i>Borrados</a>
    @endif
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/file-explore.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-iconpicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/file-explore.css') }}">
@endpush

@push('script')

    <script>
        'use strict';
        (function($){
            $(document).on('click', '.close-tree' ,function(){
                var val = this.value;
                if(val ==1) {
                    $(this).text("@lang('Expand All')")
                    $(this).val(2);
                    $(document).find('.folder-root.open').removeClass('open').addClass('closed');
                }else{
                    $(this).text("@lang('Collapse All')")
                    $(this).val(1);
                    $(document).find('.folder-root.closed').removeClass('closed').addClass('open');
                }
            });

            $(".file-tree").filetree().removeClass('d-none').hide().slideDown('slow');

            $(document).on('click', '.parent', function(){
                $('#image').removeAttr('required');
                var parent = $(document).find('.parent.active');
                var length = parent.length;
                var form    = $('#addForm');
                var data    = $(this).data('category');
                var image   = $(this).data('image');
                var action  = `{{ route('admin.category-store', '') }}/${data.id}`
                form.attr('action', action);

                $(document).find('.delete-btn').addClass('d-none');

                $(this).siblings('.delete-btn').removeClass('d-none');

                if(length > 0){
                    parent.first().removeClass('active');
                }
                form.find('.select2-auto-tokenize').text('');
                form.find('input[name=parent_id]').val(data.parent_id);
                form.find('input[name=name]').val(data.name);
                form.find('input[name=icon]').val(data.icon);
                form.find('input[name=meta_title]').val(data.meta_title);
                form.find('textarea[name=meta_description]').val(data.meta_description);
                form.find('input[name=position]').val(data.position);
                
                if(data.is_top == 1){
                    form.find('input[name=top_category]').prop('checked', true);
                }else{
                    form.find('input[name=top_category]').prop('checked', false);
                }

                if(data.is_special == 1){
                    form.find('input[name=special_category]').prop('checked', true);
                }else{
                    form.find('input[name=special_category]').prop('checked', false);
                }

                if(data.in_filter_menu == 1){
                    form.find('input[name=filter_menu]').prop('checked', true);
                }else{
                    form.find('input[name=filter_menu]').prop('checked', false);
                }

                $.each(data.meta_keywords, function (i, item) {
                    form.find('.select2-auto-tokenize').append($('<option>', {
                        value: item,
                        text : item,
                    }));
                });

                form.find('.select2-auto-tokenize').val(data.meta_keywords);
                form.find('.profilePicPreview').css('background-image', `url(${image})`);

                $('.add-chlid').removeAttr('disabled');

                $(this).toggleClass('active');
            });


            $(document).on('click','.add-chlid' ,function(){
                $('#image').attr('required', 'required');
                var form        = $('#addForm');
                var parent = $(document).find('.parent.active');
                var parent_id   = $(parent).data('category').id;
                var action  = `{{ route('admin.category-store', 0) }}`
                form.attr('action', action);
                form.find("input[type=text], textarea, select").val("");
                form.find('input[name=parent_id]').val(parent_id);
                form.find('.select2-auto-tokenize').text('');

                form.find('input[name=top_category]').prop('checked', false);
                form.find('input[name=special_category]').prop('checked', false);
                form.find('input[name=filter_menu]').prop('checked', false);

                form.find('.profilePicPreview').css('background-image', `url('{{ getImage(imagePath()['category']['path']. '/' .null, imagePath()['category']['size']) }}')`);
            });

            $(document).on('click', '.add-parent' ,function(){
                $('#image').attr('required', 'required');
                $('.add-chlid').attr('disabled', 'disabled');
                var form        = $('#addForm');
                var action  = `{{ route('admin.category-store', 0) }}`;

                var parent = $(document).find('.parent.active');

                var length = parent.length;

                if(length > 0){
                    parent.first().removeClass('active');
                    parent.parents('li').find('.delete-btn').addClass('d-none');
                }

                form.attr('action', action);
                form.find("input[type=text], textarea, select").val("");
                form.find('input[name=parent_id]').val('');
                form.find('.select2-auto-tokenize').text('');
                form.find('input[name=top_category]').prop('checked', false);
                form.find('input[name=special_category]').prop('checked', false);
                form.find('input[name=filter_menu]').prop('checked', false);
                form.find('.profilePicPreview').css('background-image', `url('{{ getImage(imagePath()['category']['path']. '/' .null, imagePath()['category']['size']) }}')`);
            });


            $('#addModal, #editModal').on('shown.bs.modal', function (e) {
                $(document).off('focusin.modal');
            });

            $('.iconPicker').iconpicker().on('change', function (e) {
                $(this).parent().siblings('.icon-name').val(`<i class="${e.icon}"></i>`);
            });

            $(document).on('click', '.delete-btn' ,function () {
                var modal   = $('#deleteModal');
                var id      = $(this).data('id');

                console.log(id);
                var form    = document.getElementById('deletePostForm');
                modal.find('.modal-title').text('{{ trans("Eliminar Categoría") }}');
                modal.find('.modal-body').text('{{ trans("¿Estás seguro de eliminar esta categoría?") }}?');
                form.action = '{{ route('admin.category.delete', '') }}' + '/' + id;
                modal.modal('show');
            });

            $('#addForm').on('submit', function(e){
                e.preventDefault();
                var url = this.action;
                var formData = new FormData(this);
                var btn     = $(this).find('button[type=submit]')
                btn.attr('disabled', 'disabled');
                $.ajax({
                    url: url,
                    type : "POST",
                    cache: false,
                    contentType : false, // you can also use multipart/form-data replace of false
                    processData: false,
                    data: formData,
                    success: function (response) {
                        if(response.status == 'error'){
                            notify('error', response.message);
                        }else{
                            console.log(response.data);
                            notify('success', response.message);
                        }
                        if(response.reload == true){
                            location.reload();
                        }
                        btn.removeAttr('disabled');
                    }
                });
            });

        })(jQuery)
    </script>

@endpush
