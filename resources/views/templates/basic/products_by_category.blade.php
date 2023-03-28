@extends($activeTemplate .'layouts.master')
@section('styles')
@endsection
@section('content')
    <!-- Category Section Starts Here -->
    <div class="category-section padding-bottom padding-top">
        <div class="container">
            <span id="idCategory" data-id="{{$category->id}}"></span>


            @if ($products->count() == 0)
                <div class="col-lg-12 mb-30">
                    @include($activeTemplate . 'partials.empty_message', ['message' => __($empty_message)])
                </div>
            @else
               
        </div>
        <div style="margin-left: 20px;">

            <div id="categories-products" class="contenido">
                <div class="container pb-3 pt-3">
                    <div class="row prod-list">
                        <div class="owl-carousel-category owl-carousel owl-theme">
                        @forelse ($subcategory as $k => $key)
                        <div class=" pb-3">
                            <app-producto-item>
                                <div class="item">
                                    <div class="category-item">
                                        <a href="{{route('products.category', ['id'=>$key->id, 'slug'=>slug($key->name)])}}">
    
                                            <img class="img-fluid" src="{{ getImage('assets/images/category/'. @$key->image) }}" />
                                            <h6>{{ $key->name }}</h6>
                                            {{-- <p>{{count($key->products)}} Items</p> --}}
                                        </a>
                                    </div>
                                </div>
                            </app-producto-item>
                        </div>
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>

           {{-- <section class="top-category section-padding">
                <div class="container">
                    <div class="owl-carousel owl-carousel-category">
                        @forelse ($subcategory as $k => $key)
                            <div class="item">
                                <div class="category-item">
                                    <a href="{{route('products.category', ['id'=>$key->id, 'slug'=>slug($key->name)])}}">

                                        <img class="img-fluid" src="{{ getImage('assets/images/category/'. @$key->image) }}" />
                                        <h6>{{ $key->name }}</h6>
                                       
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>--}}

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
                    <div id="show_products_for_categories"></div>
                </div>
                
                <center>
                    <div id="msg_loading" style="display: none;padding: 30px;">Cargando...</div>
                </center>
            </div>


            {{-- <div class="position-relative">
                        <div id="overlay" >
                            <div class="cv-spinner">
                                <span class="spinner"></span>
                            </div>
                        </div>
                        <div class="overlay-2" id="overlay2"></div>
                        <div class="page-main-content">
                            <div class="row mb-30-none page-main-content" id="grid-view">

                                @foreach ($products as $item)
                                    @php $quantity = $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : 0 @endphp

                                    @if ($quantity > 0)
                                        @php
                                            if($item->offer && $item->offer->activeOffer){
                                                $discount = calculateDiscount($item->offer->activeOffer->amount, $item->offer->activeOffer->discount_type, $item->base_price);
                                            }else $discount = 0;
                                            $wCk = checkWishList($item->id);
                                            $cCk = checkCompareList($item->id);
                                        @endphp

                                        <div class="col-mb-4 grid-categories">
                                           <div id="app-{{$item->id}}" >   
                                                <div class="product-item-2">
                                                    <div class="product-item-2-inner wish-buttons-in">
                                                        @if (isset($item->offer))
                                                            @if ($item->offer['activeOffer'])
                                                                @if ($item->offer['activeOffer']['discount_type'] == 2)
                                                                    <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}% </span>
                                                                @else 
                                                                    <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}$ </span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                        <div class="product-thumb ">
                                                            <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">
                                                                <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->main_image, imagePath()['product']['size']) }}" alt="@lang('flash')">
                                                            </a>
                                                        </div>
                                                        <div style="display: none;" class="item-prod-argo badgeProduct{{$item->id}}"></div>
                                                        <div class="product-content">
                                                            <div class="product-before-content">
                                                                <h6 class="product-title">
                                                                    <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">{{ __($item->name) }}</a>
                                                                </h6>
                                                                <div class="stock-argo">
                                                                ({{ $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : '0' }} @lang('product avaliable') )</div>
                                                                <div class="argo-tag-category">
                                                                    @if (isset($item['categories']) && $item['categories']->count() > 0) 
                                                                        @foreach ($item['categories'] as $category)
                                                                        <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{ __($category->name) }}</a>
                                                                            @if (!$loop->last)
                                                                            /
                                                                            @endif                                 
                                                                        @endforeach
                                                                    @else

                                                                    @endif
                                                                </div>
                                                                <div class="iva-argo">{{ $item->iva==1 ? 'Precio incluye IVA' : 'Exento'}}</div>
                                                                
                                                            </div>
                                                        </div>
                                                        @php
                                                            $rate = session()->get('rate');
                                                            $moneda = session()->get('moneda');
                                                        @endphp
                                                        <div class="product-argo">
                                                            <div class="price">
                                                                @if ($moneda == 'Dolares' || $moneda == '')
                                                                    @if ($discount > 0)
                                                                    {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount, 2) }}
                                                                    <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                        @endif 
                                                                    @else
                                                                    {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                        @endif 
                                                                    @endif
                                                                @else 
                                                                    @if ($discount > 0)
                                                                    {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva - $discount * $rate, 2)  }}
                                                                    <del>{{ getAmount($item->precioBaseIva * $rate, 2)  }}</del>
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2)  }}
                                                                        @endif 
                                                                    @else
                                                                    {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva * $rate, 2)  }}
                                                                        @if (!is_null($item->prime_price))
                                                                            <br>
                                                                            Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2)  }}
                                                                        @endif 
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="argo-count">  
                                                                <button @click="isShow = true" type="submit" class="cmn-btn-argo cart-add-btn showProduct{{ $item['id'] }}" data-id="{{ $item['id'] }}">@lang('Agregar')</button>

                                                                <div class="cart-plus-minus quantity">
                                                                    <div class="cart-decrease qtybutton dec">
                                                                        <i class="las la-minus"></i>
                                                                    </div>
                                                                    <select style="display: none;width: 80px;height: 40px;" 
                                                                    onchange="QuantityValue(this.value,'{{ $item->id }}')" 
                                                                    type="number" id="quantity{{ $item['id'] }}" name="quantity" step="1" min="1" class="integer-validation quantity{{ $item['id'] }} form-control">
                                                                        @if ($quantity > 0)
                                                                            @for ($i = 1; $i < $quantity + 1; $i++)
                                                                                <option value="{{$i}}">{{$i}}</option>
                                                                            @endfor
                                                                        @endif
                                                                    </select>
                                                                    <div class="cart-increase qtybutton inc">
                                                                        <i class="las la-plus"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @push('vue')
                                            <script>
                                                var app3 = new Vue({
                                                    el: '#app-{{$item->id}}',
                                                    data: {
                                                        BackTheme: null,
                                                        bagde: 1,
                                                        isHidden: true,
                                                        isShow: false
                                                    }
                                                })

                                            console.log(app3.BackTheme)
                                            </script>
                                            @endpush
                                        </div>
                                    @endif
                                @endforeach

                            </div>

                            @forelse ($subcategory as $k => $key)
                                @php $num=0 @endphp
                                @foreach ($key->products as $item)
                                    @php $quantity = $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : 0 @endphp
                                    @if ($quantity > 0)
                                        @php $num++ @endphp
                                    @endif
                                @endforeach
                                @if ($num > 0)
                                    <div class="container-fluid mt-5">
                                        <div class="section-header-2">
                                            <h3 class="title">{{ $key->name }}</h3>
                                        </div>
                                        <div class="row mb-30-none page-main-content" id="grid-view">

                                            @foreach ($key->products as $item)
                                                @php $quantity = $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : 0 @endphp

                                                @if ($quantity > 0)
                                                    @php
                                                        if($item->offer && $item->offer->activeOffer){
                                                            $discount = calculateDiscount($item->offer->activeOffer->amount, $item->offer->activeOffer->discount_type, $item->base_price);
                                                        }else $discount = 0;
                                                        $wCk = checkWishList($item->id);
                                                        $cCk = checkCompareList($item->id);
                                                    @endphp

                                                    <div class="col-mb-4 grid-categories">
                                                       <div id="app-{{$item->id}}" >   
                                                            <div class="product-item-2">
                                                                <div class="product-item-2-inner wish-buttons-in">
                                                                    @if (isset($item->offer))
                                                                        @if ($item->offer['activeOffer'])
                                                                            @if ($item->offer['activeOffer']['discount_type'] == 2)
                                                                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}% </span>
                                                                            @else 
                                                                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}$ </span>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                    <div class="product-thumb ">
                                                                        <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">
                                                                            <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->main_image, imagePath()['product']['size']) }}" alt="@lang('flash')">
                                                                        </a>
                                                                    </div>
                                                                    <div style="display: none;" class="item-prod-argo badgeProduct{{$item->id}}"></div>
                                                                    <div class="product-content">
                                                                        <div class="product-before-content">
                                                                            <h6 class="product-title">
                                                                                <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">{{ __($item->name) }}</a>
                                                                            </h6>
                                                                            <div class="stock-argo">
                                                                            ({{ $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : '0' }} @lang('product avaliable') )</div>
                                                                            <div class="argo-tag-category">
                                                                                @if (isset($item['categories']) && $item['categories']->count() > 0) 
                                                                                @foreach ($item['categories'] as $category)
                                                                                <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{ __($category->name) }}</a>
                                                                                @if (!$loop->last)
                                                                                /
                                                                                @endif                                 
                                                                                @endforeach
                                                                                @else

                                                                                @endif
                                                                            </div>
                                                                            <div class="iva-argo">{{ $item->iva==1 ? 'Precio incluye IVA' : 'Exento'}}</div>
                                                                            <!--    <div class="ratings-area justify-content-between">
                                                                            <div class="ratings">
                                                                            @php echo __(display_avg_rating($item->reviews)) @endphp
                                                                            </div>
                                                                            <span class="ml-2 mr-auto">({{ __($item->reviews->count()) }})</span>
                                                                            <div class="price">
                                                                            @if ($discount > 0)
                                                                            {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount, 2) }}
                                                                            <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                                            @else
                                                                            {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                                            @endif
                                                                            </div>  -->
                                                                        </div>
                                                                    </div>
                                                                    @php
                                                                        $rate = session()->get('rate');
                                                                        $moneda = session()->get('moneda');
                                                                    @endphp
                                                                    <div class="product-argo">
                                                                        <div class="price">
                                                                            @if ($moneda == 'Dolares' || $moneda == '')
                                                                                @if ($discount > 0)
                                                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount, 2)}}
                                                                                <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                                                    @if (!is_null($item->prime_price))
                                                                                        <br>
                                                                                        Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                                    @endif 
                                                                                @else
                                                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                                                    @if (!is_null($item->prime_price))
                                                                                        <br>
                                                                                        Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                                    @endif 
                                                                                @endif
                                                                            @else 
                                                                                @if ($discount > 0)
                                                                                {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva - $discount * $rate, 2) }}
                                                                                <del>{{ getAmount($item->precioBaseIva * $rate, 2) }}</del>
                                                                                    @if (!is_null($item->prime_price))
                                                                                        <br>
                                                                                        Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                                                    @endif 
                                                                                @else
                                                                                {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva * $rate, 2) }}
                                                                                    @if (!is_null($item->prime_price))
                                                                                        <br>
                                                                                        Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                                                    @endif 
                                                                                @endif
                                                                            @endif
                                                                        </div>

                                                                        <div class="argo-count">  
                                                                            <button @click="isShow = true" type="submit" class="cmn-btn-argo cart-add-btn showProduct{{ $item['id'] }}" data-id="{{ $item['id'] }}">@lang('Agregar')</button>

                                                                            <div class="cart-plus-minus quantity">
                                                                                <div class="cart-decrease qtybutton dec">
                                                                                    <i class="las la-minus"></i>
                                                                                </div>
                                                                                <select style="display: none;width: 80px;height: 40px;" 
                                                                                onchange="QuantityValue(this.value,'{{ $item->id }}')" 
                                                                                type="number" id="quantity{{ $item['id'] }}" name="quantity" step="1" min="1" class="integer-validation quantity{{ $item['id'] }} form-control">
                                                                                    @if ($quantity > 0)
                                                                                        @for ($i = 1; $i < $quantity + 1; $i++)
                                                                                            <option value="{{$i}}">{{$i}}</option>
                                                                                        @endfor
                                                                                    @endif
                                                                                </select>
                                                                                <div class="cart-increase qtybutton inc">
                                                                                    <i class="las la-plus"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @push('vue')
                                                        <script>
                                                            var app3 = new Vue({
                                                                el: '#app-{{$item->id}}',
                                                                data: {
                                                                    BackTheme: null,
                                                                    bagde: 1,
                                                                    isHidden: true,
                                                                    isShow: false
                                                                }
                                                            })

                                                        console.log(app3.BackTheme)
                                                        </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                            @endforeach

                                        </div>
                                    </div>
                                @endif
                            @empty
                                <h3 style="display: none;">Sin Productos</h3>
                            @endforelse
                        </div>
                    </div> --}}
        </div>
    </div>
    @endif
    </div>
    </div>
    <!-- Category Section Ends Here -->

@endsection

@push('meta-tags')
    @include('partials.seo', ['seo_contents' => @$seo_contents])
@endpush

@push('breadcrumb-plugins')
    <li><a href="{{ route('home') }}">@lang('Home')</a></li>
@endpush


@push('script')
    <script>
        (function($) {

       

            // ===========Category Owl Carousel============
            var objowlcarousel = $(".owl-carousel-category");
            if (objowlcarousel.length > 0) {
                objowlcarousel.owlCarousel({
                    responsive: {
                        0: {
                            items: 3,
                        },
                        600: {
                            items: 5,
                            nav: false
                        },
                        1000: {
                            items: 8,
                        },
                        1200: {
                            items: 8,
                        },
                    },
                    items: 8,
                    lazyLoad: true,
                    pagination: false,
                    loop: false,
                    dots: false,
                    autoPlay: 2000,
                    navigation: true,
                    stopOnHover: true,
                    nav: true,
                    navigationText: ["<i class='mdi mdi-chevron-left'></i>",
                        "<i class='mdi mdi-chevron-right'></i>"
                    ]
                });
            }

            $('select[name=per_page]').val(`{{ $perpage }}`);

            $("input[type='checkbox'][name='brand']").on('click', function() {
                var brand = [];
                var min = $('input[name="min_price"]').val();
                var max = $('input[name="max_price"]').val();

                if ($('#all-brand').is(':checked')) {
                    $("input[type='checkbox'][name='brand']").not(this).prop('checked', false);
                }
                $('.brand-filter input:checked').each(function() {
                    brand.push(parseInt($(this).attr('value')));
                });

                var perpage = $('select[name=per_page]').val();
                var category_id = `{{ $category->id }}`;

                getFilteredData(brand, min, max, category_id, perpage);

            });



            function getFilteredData(brand, min = null, max = null, category_id = `{{ $category->id }}`, perpage =
                `{{ $perpage }}`) {
                $("#overlay, #overlay2").fadeIn(300);
                $.ajax({
                    url: "{{ route('category.filter', [$category->id, slug($category->name)]) }}",
                    method: "get",
                    data: {
                        'brand': brand,
                        'perpage': perpage,
                        'min': min,
                        'max': max,
                        'category_id': category_id
                    },
                    success: function(result) {
                        $('.ajax-preloader').addClass('d-none');
                        $('.page-main-content').html(result);

                    }
                }).done(function() {
                    setTimeout(function() {
                        $("#overlay, #overlay2").fadeOut(300);
                    }, 500);
                });
            }


            $(document).on('change', '.product-page-per-view select', function() {
                var perpage = $(this).val();
                var brand = [];

                var min = $('input[name="min_price"]').val();
                var max = $('input[name="max_price"]').val();

                $('.brand-filter input:checked').each(function() {
                    brand.push(parseInt($(this).attr('value')));
                });
                var category_id = `{{ $category->id }}`;

                getFilteredData(brand, min, max, category_id, perpage);
            });

            $("#slider-range").slider({
                range: true,
                min: {{ $min_price }},
                max: {{ $max_price }},
                values: [{{ $min_price }}, {{ $max_price }}],
                slide: function(event, ui) {
                    $("#amount").val("{{ $general->cur_sym }}" + ui.values[0] + " - $" + ui.values[1]);
                    $('input[name=min_price]').val(ui.values[0]);
                    $('input[name=max_price]').val(ui.values[1]);
                },

                change: function() {
                    var brand = [];
                    var min = $('input[name="min_price"]').val();
                    var max = $('input[name="max_price"]').val();

                    $('.brand-filter input:checked').each(function() {
                        brand.push(parseInt($(this).attr('value')));
                    });

                    var perpage = $('select[name=per_page]').val();
                    var category_id = `{{ $category->id }}`;
                    getFilteredData(brand, min, max, category_id);
                }

            });

            $("#amount").val("{{ $general->cur_sym }}" + $("#slider-range").slider("values", 0) +
                " - {{ $general->cur_sym }}" + $("#slider-range").slider("values", 1));

        })(jQuery)
    </script>
@endpush
