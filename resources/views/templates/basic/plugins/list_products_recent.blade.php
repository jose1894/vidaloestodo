@extends($activeTemplate .'layouts.master')

@section('content')
<!-- Category Section Starts Here -->
<div class="category-section padding-bottom padding-top">
    <div class="container">

        @if($products->count() == 0)
        <div class="col-lg-12 mb-30">
            @include($activeTemplate.'partials.empty_message', ['message' => __($empty_message)])
        </div>
        @else
        <!--
        <div class="row">
            <div class="col-xl-3">
                <aside class="category-sidebar">
                    <div class="widget d-xl-none">
                        <div class="d-flex justify-content-between">
                            <h5 class="title border-0 pb-0 mb-0">@lang('Filter')</h5>
                            <div class="close-sidebar"><i class="las la-times"></i></div>
                        </div>
                    </div>

                    <div class="widget">
                        <h5 class="title">
                            {{-- @lang('Filter by Price') --}}
                            Filtrar Por Precio
                        </h5>
                        <div class="widget-body">
                            <div id="slider-range"></div>
                            <div class="price-range">
                                <label for="amount">@lang('Price') :</label>
                                <input type="text" id="amount" readonly>
                                <input type="hidden" name="min_price" value="{{$min_price}}">
                                <input type="hidden" name="max_price" value="{{$max_price}}">
                            </div>
                        </div>
                    </div>


                    @isset($brands)
                    <div class="widget">
                        <h5 class="title">
                            {{-- @lang('Filter by Brand') --}}
                            Filtrar Por Marca
                        </h5>

                        <div class="widget-body">
                            <div class="widget-check-group">
                                <input type="checkbox" value="0" name="brand" id="all-brand" @if (in_array(0, $brand)) checked @endif>
                                <label for="all-brand">
                                    {{-- @lang('All Brand') --}}
                                    Todas Las Marcas
                                </label>
                            </div>

                            @foreach ($brands as $key=>$item)
                                @if( !is_null($brands[$key]) )
                                <div class="widget-check-group brand-filter">
                                    <input type="checkbox" value="{{$item->id}}" name="brand" id="brand-{{$loop->iteration}}" @if (in_array($item->id, $brand)) checked @endif>
                                    <label for="brand-{{$loop->iteration}}" >{{__($item->name)}}</label>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endisset

                    <div class="widget">
                        <h5 class="title">
                            {{-- @lang('Filter by Category') --}}
                            Filtrar Por Categorías
                        </h5>
                        <div class="widget-body">
                            <ul class="filter-category">
                                <li>
                                    <a href="javascript:void(0)" data-id="0" class="@if($category_id == 0)) active @endif"><i class="las la-angle-right"></i> 
                                        {{-- {{__('All Category')}} --}}
                                        Todas Las Categorías
                                    </a>
                                </li>
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="javascript:void(0)" data-id="{{$category->id}}" class="@if($category_id == $category->id)) active @endif" ><i class="las la-angle-right"></i> {{__($category->name)}} </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>-->
            </div>

            <div style="margin-left: 20px;">
                <div id="categories-products" class="contenido">
                    <div class="container-fluid pb-3 pt-3">
                        <div class="row prod-list">
                            @foreach ($products as $item)
                                @php $quantity = $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : 0 @endphp
                                @php
                                    if ($item->offer && $item->offer->activeOffer) {
                                        $discount = calculateDiscount($item->offer->activeOffer->amount, $item->offer->activeOffer->discount_type, $item->base_price);
                                    } else {
                                        $discount = 0;
                                    }
                                    $wCk = checkWishList($item->id);
                                    $cCk = checkCompareList($item->id);
                                @endphp
                                <div class="col-6 col-sm-4 cols-md-3 col-lg-2 pb-3" id="app-{{ $item->id }}">
                                    <app-producto-item>
                                        <div class="item-prod">
                                            <div class="item-bord item-bord">
        
                                                <a
                                                    href="{{ route('product.detail', ['id' => $item->id, 'slug' => slug($item->name)]) }}">
                                                    <div class="item-img">
        
                                                        @if (isset($item->offer))
                                                            @if ($item->offer['activeOffer'])
                                                                @if ($item->offer['activeOffer']['discount_type'] == 2)
                                                                    <span class="text-white bg-danger tag-discount discount-products"> -{{$item->offer['activeOffer']['amount']}}% </span>
                                                                @else 
                                                                    <span class="text-white bg-danger tag-discount discount-products"> -{{$item->offer['activeOffer']['amount']}}$ </span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                        
                                                        <img src="{{ getImage(imagePath()['product']['path'] . '/thumb_' . @$item->main_image, imagePath()['product']['size']) }}"
                                                            alt="@lang('flash')" class="img-prin img-fluid">
                                                    </div>
                                                </a>
                                                <div class="item-descp">
        
                                                    
                                                    <span class="screenReaderOnlyText"></span>
                                                    <h3 class="item-nomb">
                                                        <a href="{{ route('product.detail', ['id' => $item->id, 'slug' => slug($item->name)]) }}"
                                                            class="mr-2 mb-2">{{ __($item->name) }}</a>
                                                        </a>
                                                    </h3>
                                                    <p><span class="item-disp stock-argo">({{ $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : '0' }}
                                                            @lang('product avaliable') )</span></p>
                                                    <p class="producto-categ">
                                                        @if (isset($item['categories']) && $item['categories']->count() > 0)
                                                            @foreach ($item['categories'] as $category)
                                                                <a
                                                                    href="{{ route('products.category', ['id' => $category->id, 'slug' => slug($category->name)]) }}">{{ __($category->name) }}</a>
                                                                @if (!$loop->last)
                                                                    /
                                                                @endif
                                                            @endforeach
                                                        @else
                                                        @endif
                                                    </p>
                                                    <p class="producto-categ">
                                                        <span data-automation-id="price-per-unit">{{ $item->iva == 1 ? 'IVA Incluido' : 'Exento'}}</span>
                                                    </p>
                                                </div>
                                                <div style="display: none;"
                                                    class="item-prod-argo badgeProduct{{ $item->id }}"></div>
                                                <div class="item-final">
                                                    <div class="prec-area">
                                                        <span class="prec-vent">
                                                            @php
                                                                $rate = session()->get('rate');
                                                                $moneda = session()->get('moneda');
                                                            @endphp
                                                            <span>
                                                                @if ($moneda == 'Dolares' || $moneda == '')
                                                                    @if ($discount > 0)
                                                                        {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount, 2) }}
                                                                        <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva ?? $item->prime_price, 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva ?? $item->prime_price, 2) }}
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    @if ($discount > 0)
                                                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva - $discount * $rate, 2) }}
                                                                        <del>{{ getAmount($item->precioBaseIva * $rate, 2) }}</del>
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva ?? $item->prime_price * $rate, 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva * $rate, 2) }}
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva ?? $item->prime_price * $rate, 2) }}
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="btn-area">
        
                                                        <button @click="isShow = true" type="submit"
                                                            class="cmn-btn-argo-item cart-add-btn showProduct{{ $item['id'] }}"
                                                            data-id="{{ $item['id'] }}">@lang('Agregar')</button>
        
                                                        <div class="cart-plus-minus quantity">
                                                            {{-- <div class="cart-decrease qtybutton dec">
                                                                    <i class="las la-minus"></i>
                                                                </div>
                                                                <select style="display: none;width: 80px;height: 40px;" 
                                                                onchange="QuantityValue(this.value,'{{ $item->id }}')" 
                                                                type="number" id="quantity{{ $item['id'] }}" name="quantity" step="1" min="1" class="custom-select integer-validation quantity{{ $item['id'] }} form-control">
                                                                    @if ($quantity > 0)
                                                                    @for ($i = 1; $i < $quantity + 1; $i++)
                                                                    <option value="{{$i}}">{{$i}}</option>
                                                                    @endfor
                                                                    @endif
                                                                </select> --}}
                                                            {{-- <div class="cart-increase qtybutton inc">
                                                                    <i class="las la-plus"></i>
                                                                </div> --}}
                                                        </div>
        
                                                        <form style="display: none;" novalidate="" name="formSelect"
                                                            class="ng-pristine ng-valid ng-touched quantity{{ $item['id'] }}">
                                                            <span class="ng-star-inserted" style="">
                                                                <i class="fas fa-check"></i>&nbsp;Agregado</span>
                                                            <!---->
                                                            <select onchange="QuantityValue(this.value,'{{ $item->id }}')"
                                                                formcontrolname="cantidad" class="custom-select" style=""
                                                                id="quantity{{ $item['id'] }}" name="quantity">
                                                                @if ($quantity > 0)
                                                                    @for ($i = 1; $i < $quantity + 1; $i++)
                                                                        <option value="{{ $i }}">
                                                                            {{ $i }}</option>
                                                                    @endfor
                                                                @endif
                                                            </select>
                                                        </form>
        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </app-producto-item>
                                </div>
                            @endforeach
                        </div>                        
                    </div>
                </div>
            </div>


            
        </div>
        @endif
    </div>
</div>
<!-- Category Section Ends Here -->
@endsection

@push('breadcrumb-plugins')
    <li><a href="{{route('home')}}">@lang('Home')</a></li>
@endpush

@push('meta-tags')
    @include('partials.seo')
@endpush

@push('script')
    <script>
        (function($){

            $("input[type='checkbox'][name='brand']").on('click', function(){
                var brand = [];
                var min = $('input[name="min_price"]').val();
                var max = $('input[name="max_price"]').val();

                if($('#all-brand').is(':checked')){
                    $("input[type='checkbox'][name='brand']").not(this).prop('checked', false);
                }
                $('.brand-filter input:checked').each(function() {
                    brand.push(parseInt($(this).attr('value')));
                });

                var category_id = $(document).find('.filter-category li a.active').data('id');
                getFilteredData(brand, min, max, category_id);

            });

            function getFilteredData(brand, min=null, max=null, category_id=null, perpage=`{{ $perpage }}`){
                $("#overlay, #overlay2").fadeIn(300);
                $.ajax({
                    url: "{{ route('products.filter') }}",
                    method: "get",
                    data: {'brand':brand, 'perpage':perpage, 'min':min,  'max':max, 'category_id': category_id},
                    success: function(result){
                        $('.ajax-preloader').addClass('d-none');
                        $('.page-main-content').html(result);

                    }
                }).done(function() {
                    setTimeout(function(){
                        $("#overlay, #overlay2").fadeOut(300);
                    },500);
                });
            }


            $(document).on('change', '.product-page-per-view select', function(){
                var perpage = $(this).val();
                var brand = [];

                var min = $('input[name="min_price"]').val();
                var max = $('input[name="max_price"]').val();

                $('.brand-filter input:checked').each(function() {
                    brand.push(parseInt($(this).attr('value')));
                });
                var category_id = $(document).find('.filter-category li a.active').data('id');
                getFilteredData(brand, min, max, category_id, perpage);
            });

            $("#slider-range").slider({
                range: true,
                min: {{$min_price}},
                max: {{$max_price}},
                values: [ {{$min_price}}, {{$max_price}} ],
                slide: function( event, ui ) {
                    $( "#amount" ).val( "{{$general->cur_sym}}" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
                    $('input[name=min_price]').val(ui.values[ 0 ]);
                    $('input[name=max_price]').val(ui.values[ 1 ]);
                },
                change: function(){
                    var brand = [];
                    var min = $('input[name="min_price"]').val();
                    var max = $('input[name="max_price"]').val();
                    $('.brand-filter input:checked').each(function() {
                        brand.push(parseInt($(this).attr('value')));
                    });

                    var category_id = $(document).find('.filter-category li a.active').data('id');
                    getFilteredData(brand, min, max, category_id)
                }
            });


            $( "#amount" ).val( "{{$general->cur_sym}}" + $( "#slider-range" ).slider( "values", 0 ) + " - {{$general->cur_sym}}" + $( "#slider-range" ).slider( "values", 1 ) );

            $('.filter-category li a').on('click', function(){

                $(document).find('.filter-category li a').removeClass('active');
                $(this).addClass('active');
                var category_id = $(this).data('id');
                var brand = [];
                var min = $('input[name="min_price"]').val();
                var max = $('input[name="max_price"]').val();

                $('.brand-filter input:checked').each(function() {
                    brand.push(parseInt($(this).attr('value')));
                });

                getFilteredData(brand, min, max, category_id);

            });

    })(jQuery)
    </script>
@endpush
