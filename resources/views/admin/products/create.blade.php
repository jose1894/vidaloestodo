@extends('admin.layouts.app')

@section('panel')


    <div class="row justify-content-center">

        <div class="loader-container text-center d-none">
            <span class="loader">
                <i class="fa fa-circle-notch fa-spin" aria-hidden="true"></i>
            </span>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('admin.products.product-store', isset($product) ? $product->id : 0) }}" id="addForm"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card p-2 has-select2">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Product Information') --}}
                            Información del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Product Name') --}}
                                    Nombre del Producto
                                </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Nombre"
                                    value="{{ isset($product) ? $product->name : old('name') }}" name="name" required />
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Product Model') --}}
                                    Modelo del Producto
                                </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Modelo"
                                    value="{{ isset($product) ? $product->model : old('model') }}" name="model" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">@lang('Brand')</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control select2-basic" name="brand_id">
                                    <option selected disabled value="">
                                        {{-- @lang('Select One') --}}
                                        Seleccionar Uno
                                    </option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ @$brand->id }}"
                                            {{ isset($product) ? ($brand->id == $product->brand_id ? 'selected' : '') : '' }}>
                                            {{ __($brand->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-2 col-md-3">
                                <label class="font-weight-bold" for="categories">@lang('Categories')</label>
                            </div>
                            <div class="col-lg-10 col-md-9">
                                <select class="select2-multi-select form-control" name="categories[]" id="categories"
                                    multiple required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">@lang($category->name)</option>
                                        @php
                                            $prefix = '--';
                                        @endphp
                                        @foreach ($category->allSubcategories as $subcategory)
                                            @include('admin.partials.subcategories', [
                                                'subcategory' => $subcategory,
                                                'prefix' => $prefix,
                                            ])
                                            <option value="{{ $subcategory->id }}">
                                                {{ $prefix }}@lang($subcategory->name)
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-2 col-md-3">
                                <label class="font-weight-bold" for="tags">@lang('Tags')</label>
                            </div>
                            <div class="col-lg-10 col-md-9">
                                <select class="select2-multi-select form-control" name="tags[]" id="tags" multiple
                                    required>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}">@lang($tag->name)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Base Price') --}}
                                    Precio Base
                                </label>
                            </div>
                            <div class="col-md-10">

                                <div class="input-group">
                                    <input type="text" class="form-control numeric-validation" name="base_price"
                                        placeholder="Precio Base" value="{{ $product->base_price ?? 0 }}" required />
                                    <div class="input-group-append">
                                        <span class="input-group-text">@lang($general->cur_sym)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    Precio Prime
                                </label>
                            </div>
                            <div class="col-md-10">

                                <div class="input-group">
                                    <input type="text" class="form-control numeric-validation" name="prime_price"
                                        placeholder="Precio Prime" value="{{ $product->prime_price ?? '' }}" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">@lang($general->cur_sym)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    IVA
                                </label>
                            </div>
                            <div class="col-md-10">
                                <label class="switch">
                                    <input type="checkbox" name="iva" value="1"
                                        @if (isset($product)) {{ @$product->iva == 1 ? 'checked' : '' }} @endif>
                                    <span class="slider round"></span>
                                </label>

                            </div>

                        </div>

                        @if (isset($product) && $product->iva == 1 && !is_null($product->productIva))
                            <div class="form-group row">
                                <select name="iva_selected" class="form-control">
                                    @if (!is_null($ivas))
                                        <option selected disabled>Seleccione un IVA</option>
                                        @foreach ($ivas as $option)
                                            <option value="{{ $option->id }}"
                                                {{ $product->iva_id == $option->id ? 'selected' : '' }}>
                                                {{ __($option->name) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @else
                            <div class="form-group row">
                                <select name="iva_selected" class="form-control">
                                    @if (!is_null($ivas))
                                        <option selected disabled>Seleccione un IVA</option>
                                        @foreach ($ivas as $option)
                                            <option value="{{ $option->id }}">{{ __($option->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Productos del Combo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold" for="categoria">Categoría</label>
                                <select id="categoria" class="form-control" name="categoria">
                                    <option selected>Selecciona una Categoría</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">@lang($category->name)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold" for="subcategoria">Sub-Categoria</label>
                                <select id="subcategoria" class="form-control" name="subcategoria">
                                    <option selected>Selecciona una Sub-Categoría</option>
                                    <option>...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold" for="producto">Productos</label>
                                <select id="producto" class="form-control" name="producto">
                                    <option selected>Selecciona un Productos</option>
                                    <option>...</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label for="formGroupExampleInput">Cantidad</label>
                                    <input type="text" class="form-control" id="cantidad"
                                        placeholder="Cantidad de producto" name="cantidad">
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <div class="p-4">
                                    <button type="button" class="btn btn-outline--success add-products mt-2"><i
                                            class="la la-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                
                                <h4>Lista de Productos</h4>
                                <ul class="listItem">
                                    @if(isset($product->productcombo))
                                    @foreach ($product->productcombo as $productcombo)
                                        <li><b>{{ $productcombo->cantidad }}</b> {{ $productcombo->productcombo->name }}</li>
                                    @endforeach
                                    @endif
                                </ul>
                                
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Product Description') --}}
                            Descripción del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Description') --}}
                                    Descripción
                                </label>
                            </div>
                            <div class="col-md-10">
                                <!--<textarea rows="5" class="form-control nicEdit" name="description">@php echo ($product->description)??'' @endphp</textarea>-->
                                <textarea rows="5" class="form-control" name="description">{{ $product->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Summary') --}}
                                    Resumen
                                </label>
                            </div>
                            <div class="col-md-10">
                                <textarea rows="5" class="form-control" name="summary">{{ $product->summary ?? '' }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Extra Descriptions') --}}
                            Descripción Extra
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="extras">
                            @if (isset($product) && $product->extra_descriptions != null)
                                @foreach ($product->extra_descriptions as $item)
                                    <div class="extra">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button type="button"
                                                class="btn btn-outline--danger float-right  remove-extra"><i
                                                    class="la la-minus"></i></button>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <label class="font-weight-bold">@lang('Name')</label>
                                            </div>
                                            <div class="col-md-10">
                                                <input type="text" class="form-control"
                                                    name="extra[{{ $loop->iteration }}][key]"
                                                    value="{{ $item['key'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <label class="font-weight-bold">@lang('Value')</label>
                                            </div>
                                            <div class="col-md-10">
                                                <textarea class="form-control nicEdit" name="extra[{{ $loop->iteration }}][value]" rows="3"> @php echo $item['value'] @endphp</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <p class="p-2 extra-info">
                                    {{-- @lang('Add more descriptions as you want by clicking the (+) button on the right side.') --}}
                                    Agregue más descripciones como desee haciendo clic en el botón (+) en el lado derecho
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-end">

                                    <button type="button" class="btn btn-outline--success add-extra"><i
                                            class="la la-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Inventory') --}}
                            Inventario
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Track Inventory') --}}
                                    Seguimiento de inventario
                                </label>
                            </div>
                            <div class="col-md-10">
                                <label class="switch">
                                    <input type="checkbox" name="track_inventory" value="1"
                                        @if (isset($product)) {{ @$product->track_inventory ? 'checked' : '' }} @else checked @endif>
                                    <span class="slider round"></span>
                                </label>

                            </div>

                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Show in Frontend') --}}
                                    Mostrar en Vistas
                                </label>
                            </div>
                            <div class="col-md-10">
                                <label class="switch">
                                    <input type="checkbox" name="show_in_frontend" value="1"
                                        @if (isset($product)) {{ @$product->show_in_frontend ? 'checked' : '' }} @else checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <!-- <div class="form-group row">
                                                                <div class="col-md-2">
                                                                    <label class="font-weight-bold">
                                                                        {{-- @lang('Has Variants') --}}
                                                                        Tiene variantes
                                                                    </label>
                                                                </div>
                                                                <div class="col-md-10">
                                                                    <label class="switch">
                                                                        <input type="checkbox" name="has_variants" value="1" @if (isset($product)) {{ $product->has_variants ? 'checked' : '' }} @endif>
                                                                        <span class="slider round"></span>
                                                                    </label>

                                                                </div>
                                                            </div> -->

                        <div class="form-group row sku-wrapper">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Product SKU') --}}
                                    SKU del producto
                                </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="SKU del Producto"
                                    value="{{ isset($product) ? $product->sku : old('sku') }}" name="sku" />
                            </div>
                        </div>
                    </div>
                    @if (request()->routeIs('admin.products.edit'))
                        <div class="card-footer">
                            <h5 class="ml-3 text-danger font-weight-bold">
                                Si cambia el valor de Seguimiento de inventario o Tiene variantes, se eliminarán sus
                                registros de existencias anteriores para este producto.
                            </h5>
                        </div>
                    @endif
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Product Specifications') --}}
                            Especificación de Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="specifications-wrapper">
                            @if (isset($product) && $product->specification != null)
                                @foreach ($product->specification as $item)
                                    <div class="specifications">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="font-weight-bold">{{ $loop->iteration }}</label>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                name="specification[{{ $loop->iteration }}][name]"
                                                                placeholder="@lang('Type Name Here...')"
                                                                value="{{ @$item['name'] }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div
                                                            class="form-group abs-form-group d-flex justify-content-between flex-wrap">

                                                            <input type="text" class="form-control"
                                                                name="specification[{{ $loop->iteration }}][value]"
                                                                placeholder="@lang('Type Value Here...')"
                                                                value="{{ @$item['value'] }}"">
                                                            <button type="button"
                                                                class="btn btn-outline--danger remove-specification abs-button"><i
                                                                    class="la la-minus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="p-2 specification-info">
                                    {{-- @lang('Add specifications as you want by clicking the (+) button on the right side.') --}}
                                    Agregue especificaciones como desee haciendo clic en el botón (+) en el lado derecho
                                </p>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline--success add-specification "><i
                                            class="la la-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('SEO Contents') --}}
                            Contenidos SEO
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Meta Title') --}}
                                    Título
                                </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="meta_title"
                                    value="{{ isset($product) ? $product->meta_title : old('meta_title') }}"
                                    placeholder="Título">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Meta Description') --}}
                                    Descripción
                                </label>
                            </div>
                            <div class="col-md-10">
                                <textarea name="meta_description" rows="5" class="form-control">{{ isset($product) ? $product->meta_description : old('meta_description') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Meta Keywords') --}}
                                    Palabras Clave
                                </label>
                            </div>
                            <div class="col-md-10">
                                <select name="meta_keywords[]" class="form-control select2-auto-tokenize"
                                    multiple="multiple">
                                    @if (@$product->meta_keywords)
                                        @foreach ($product->meta_keywords as $option)
                                            <option value="{{ $option }}" selected>{{ __($option) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="form-text text-muted">
                                    <i class="las la-info-circle"></i>
                                    {{-- @lang('Type , as seperator or hit enter among keywords') --}}
                                    Separe las plabras con una "," o presionando Enter
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2 my-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{-- @lang('Media Contents') --}}
                            Contenido Multimedia
                        </h5>
                    </div>
                    <div class="card-body">

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Main Image') --}}
                                    Imagen Principal
                                </label>
                            </div>
                            <div class="col-md-10">
                                <div class="payment-method-item">
                                    <div class="payment-method-header d-flex flex-wrap">
                                        <div class="thumb">
                                            <div class="avatar-preview">
                                                <div class="profilePicPreview"
                                                    style="background-image: url('{{ getImage(imagePath()['product']['path'] . '/' . @$product->main_image, imagePath()['product']['size']) }}')">
                                                </div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" name="main_image" class="profilePicUpload"
                                                    id="image" accept=".png, .jpg, .jpeg"
                                                    @if (request()->routeIs('admin.products.create')) required @endif>
                                                <label for="image" class="bg--primary"><i
                                                        class="la la-pencil"></i></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">
                                    {{-- @lang('Additional Images') --}}
                                    Imágenes adicionales
                                </label>
                            </div>
                            <div class="col-md-10">
                                <div class="input-field">
                                    <div class="input-images"></div>
                                    <small class="form-text text-muted">
                                        <i class="las la-info-circle"></i>
                                        {{-- @lang('You can only upload a maximum of 6 images') --}}
                                        Solo puede subir un máximo de 6 imágenes
                                        </label>
                                    </small>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">@lang('Video')</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="video_link" placeholder="Video"
                                    value="{{ $product->video_link ?? '' }}" />
                                <small class="form-text text-muted">
                                    <i class="las la-info-circle"></i>
                                    {{-- @lang('Only youtube embed link is allowed') --}}
                                    Solo se permiten enlaces de Youtube
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="btn btn-block btn--success mt-3">{{ isset($product) ? trans('Update') : trans('Add') }}</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="close ml-auto m-3" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-body text-center">
                    <i class="las la-times-circle f-size--100 text--danger mb-15"></i>
                    <h3 class="text--danger mb-15">@lang('Error: Cannot process your entry!')</h3>
                    <p class="mb-15">@lang('You can\'t add more than 6 image')</p>
                    <button type="button" class="btn btn--danger" data-dismiss="modal">@lang('Continue')</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.products.all') }}" class="btn btn-sm btn--primary box--shadow1 text--small"><i
            class="la la-backward"></i>@lang('Go Back')</a>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/image-uploader.min.js') }}"></script>
@endpush

@push('style-lib')
    <link
        href="https://fonts.googleapis.com/css?family=Lato:300,700|Montserrat:300,400,500,600,700|Source+Code+Pro&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/image-uploader.min.css') }}">
@endpush


@push('script')
    <script>
        'use strict';
        (function($) {
            var productsFilter = [];
            var data = [];

            function getproductfilter(id) {
                var url = "{{ route('admin.products.filters') }}"
                $.ajax({
                    url: url,
                    method: "GET",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        let products = data.products.data;
                        productsFilter = data.products.data
                        var output = `<option value="" selected>Selecciona un Productos</option>`;
                        $.each(products, function(key, val) {
                            output += `<option value="${val.id}">${val.name}</option>`;
                        });
                        $('#producto').html(output);


                    }
                });
            }

            function storecombo(data) {
                var url = "{{ route('admin.products.storecombo') }}"
                $.ajax({
                    url: url,
                    type: "GET",
                    data: data,
                    success: function(response) {
                        console.log(response);
                    }
                });
            }

            var dropdownParent = $('.has-select2');

            @if (isset($images))
                let preloaded = @json($images);
            @else
                let preloaded = [];
            @endif

            $('.input-images').imageUploader({
                preloaded: preloaded,
                imagesInputName: 'photos',
                preloadedInputName: 'old',
                maxFiles: 6
            });

            $(document).on('input', 'input[name="images[]"]', function() {
                var fileUpload = $("input[type='file']");
                if (parseInt(fileUpload.get(0).files.length) > 6) {
                    $('#errorModal').modal('show');
                }
            });

            $('select[name="category_id"]').on('change', function() {
                var subcategories = $(this).find(':selected').data('subcats');
                var output = `<div class="col-md-2">
                            <label class="font-weight-bold">Subcategory</label>
                        </div>
                        <div class="col-md-10">
                            <select class="form-control select2-basic" name="sub_category_id">
                            <option value="" selected disabled>@lang('Select One')</option>
                        </div>
                        `;
                if (subcategories.length != 0) {
                    $.each(subcategories, function(key, val) {
                        output += `<option value="${val.id}">${val.name}</option>`;
                    });
                    output += `</select>`
                    $('#sub-categories-div').html(output);
                }
            });

            $('select[name="categoria"]').on('change', function() {
                var selecct = $(this).find(":selected").val()
                let categories = @json($categories);

                let subcategories = categories.find(function(subcategories) {
                    return subcategories.id == selecct;
                });

                var output = `<option selected>Selecciona una Sub-Categoría</option>`;
                $.each(subcategories.all_subcategories, function(key, val) {
                    output += `<option value="${val.id}">${val.name}</option>`;
                });
                $('#subcategoria').html(output);
                getproductfilter(selecct)
            });

            $('select[name="subcategoria"]').on('change', function() {
                var selecct = $(this).find(":selected").val()
                getproductfilter(selecct)
            });

            $('select[name="producto"]').on('change', function() {
                var selecct = $(this).find(":selected").val()
            });

            @if (request()->routeIs('admin.products.edit'))

                var categories = [];
                @if ($product->categories)
                    categories = @json($product->categories->pluck('id'));
                @endif
                $('#categories').val(categories);

                var tags = [];
                @if ($product->tags)
                    tags = @json($product->tags->pluck('id'));
                @endif
                $('#tags').val(tags);



                $('.select2-multi-select').select2({
                    dropdownParent: dropdownParent,
                    closeOnSelect: false
                });
            @endif

            $('.add-products').on('click', function() {
                var selecct = $('#producto').val();
                var cantidad = $('#cantidad').val();
                let product = productsFilter.find(function(product) {
                    return product.id == selecct;
                });
                var output = `<li><b>${cantidad}</b> ${product.name}</li>`;
                $(".listItem").append(output);

                @if (isset($images))
                let productPadre = @json($product);
            @else
                let productPadre = [];
            @endif

                
                //console.log(productPadre.id);
                

                var datas = {
                    'product_id': productPadre.id,
                    'products_combo': selecct,
                    'cantidad': cantidad
                }

                data.push( datas );

                 console.log(data);

                //var selecct = $("#producto").find(":selected").val("")
                $("#producto").val("");
                $("#cantidad").val("");

                //storecombo(data)
            });

            $('.add-specification').on('click', function() {
                var specifications = $(document).find('.specifications');
                var length = specifications.length;
                $('.specification-info').addClass('d-none');
                var content = `<div class="specifications">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">${length+1}</label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="specification[${length}][name]" placeholder="@lang('Type Name Here...')">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group abs-form-group d-flex justify-content-between flex-wrap">

                                                    <input type="text" class="form-control" name="specification[${length}][value]" placeholder="@lang('Type Value Here...')">
                                                    <button type="button" class="btn btn-outline--danger remove-specification abs-button"><i class="la la-minus"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>`;

                $(content).appendTo('.specifications-wrapper').hide().slideDown('slow');

                specifications = $(document).find('.specifications');
                length = specifications.length;

                if (length > 0) {
                    $('.remove-specification').removeClass('d-none');
                } else {
                    $('.remove-specification').addClass('d-none');
                }
            });

            $(document).on('click', '.remove-specification', function() {

                var parent = $(this).parents('.specifications');

                parent.slideUp('slow', function(e) {
                    this.remove();
                });

            });

            $('.add-extra').on('click', function() {
                var extras = $(document).find('.extra');
                var length = extras.length;

                $('.extra-info').addClass('d-none');

                var content = `<div class="extra">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button type="button" class="btn btn-outline--danger float-right  remove-extra"><i class="la la-minus"></i></button>
                                    </div>
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">@lang('Name')</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="extra[${length + 1}][key]" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label class="font-weight-bold">@lang('Value')</label>
                                    </div>
                                    <div class="col-md-10">
                                        <textarea class="form-control" name="extra[${length + 1}][value]" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>`;


                var elm = $(content).appendTo('.extras').hide().slideDown('slow').find(
                    `textarea[name="extra[${length +1}][value]"]`);
                var curSize = elm.length;

                new nicEditor().panelInstance(elm[0]);

                extras = $(document).find('.extra');
                length = extras.length;

                if (length != 0) {
                    $('.remove-extra').removeClass('d-none');
                } else {
                    $('.remove-extra').addClass('d-none');
                }
            });

            $(document).on('click', '.remove-extra', function() {

                var parent = $(this).parents('.extra');
                parent.slideUp('slow', function() {
                    this.remove();
                });

            });

            $("input[name='base_price']").on('click', function() {
                if ($(this).val() == 0) {
                    $(this).val('');
                }
            });

            $("input[name='prime_price']").on('click', function() {
                if ($(this).val() == 0) {
                    $(this).val('');
                }
            });

            if ($(document).find('input[name="has_variants"]').prop("checked") == true) {
                $(document).find('.sku-wrapper').hide();
            }

            $('input[name="has_variants"]').on('click', function() {
                if ($(this).prop("checked") == true) {
                    $('.sku-wrapper').hide('slow');
                    $(document).find('input[name="sku"]').val('');

                } else if ($(this).prop("checked") == false) {
                    $('.sku-wrapper').show('slow');
                    $(document).find('input[name="sku"]').val('');
                }
            });

            $('#addForm').on('submit', function(e) {
                var loading = $('.loader-container');
                e.preventDefault();
                loading.removeClass('d-none').fadeIn();
                var url = this.action;
                var formData = new FormData(this);
                formData.append('data',JSON.stringify(data));
                var btn = $(this).find('button[type=submit]')
                btn.attr('disabled', 'disabled');
                $.ajax({
                    url: url,
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response) {
                        if (response.status == 'error') {
                            notify('error', response.message);
                        } else {
                            notify('success', response.message);
                        }
                        if (response.reload == true) {
                            location.reload();
                        }
                        btn.removeAttr('disabled');
                        loading.fadeOut();
                    }
                });
            });

        })(jQuery)
    </script>
@endpush
