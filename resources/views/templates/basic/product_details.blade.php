@extends($activeTemplate .'layouts.master')

@section('content')

<!-- Product Single Section Starts Here -->
<div class="category-section oh argo-details">
    <div class="container">
        <div class="row product-details-wrapper argo-details-content">
            <div class="col-lg-5 variant-images">
                @if(@$product->offer['activeOffer'])
                                                        @if(@$product->offer['activeOffer']['discount_type'] == 2)
                                                            <span class="text-white bg-danger tag-discount"> -{{@$product->offer['activeOffer']['amount']}}% </span>
                                                        @else 
                                                            <span class="text-white bg-danger tag-discount"> -{{@$product->offer['activeOffer']['amount']}}$ </span>
                                                        @endif
                                                    @endif
                <div class="sync1 owl-carousel owl-theme">
                    @if($images->count() == 0)
                    <div class="thumbs">
                        <img class="zoom_img"
                            src="{{ getImage(imagePath()['product']['path'].'/'.@$product->main_image, imagePath()['product']['size']) }}"
                            alt="@lang('products-details')">
                    </div>
                    @else
                    @foreach ($images as $item)

                    <div class="thumbs">
                        <img class="zoom_img"
                            src="{{ getImage(imagePath()['product']['path'].'/'.@$item->image, imagePath()['product']['size']) }}"
                            alt="@lang('products-details')">
                    </div>
                    @endforeach
                    @endif
                </div>

                <div class="sync2 owl-carousel owl-theme mt-2">
                    @if($images->count() > 1)
                    @foreach ($images as $item)
                    <div class="thumbs">
                        <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->image, imagePath()['product']['size']) }}"
                            alt="@lang('products-details')">
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>


            <div class="col-lg-7">
                <div class="product-details-content product-details">
                    <h4 class="title">{{__($product->name)}}</h4>

                    <div class="xdes-argo">
                        <div class="description-item">
                            @if($product->description)
                            <p>
                                @lang($product->description)
                            </p>

                            @else
                            <div class="alert cl-title alert--base" role="alert">
                                No Hay Descripción Para Este Producto
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- 
                    <div class="ratings-area justify-content-between">
                        <div class="ratings">
                            @php echo __(display_avg_rating($product->reviews)) @endphp
                        </div>
                        <span class="ml-2 mr-auto">({{__($product->reviews->count())}})</span>
                    </div> -->
                    @if($product->show_in_frontend && $product->track_inventory)
                    @php $quantity = $product->stocks->sum('quantity'); @endphp
                    <div class="badge badge--{{$quantity>0?'success':'danger'}} stock-status">Existencias (<span
                            class="stock-qty">{{$quantity}}</span>)</div>
                    @endif



                    <p>
                        @php echo __($product->summary) @endphp
                    </p>

                    @forelse ($attributes as $attr)

                    @php $attr_data = getProuductAttributes($product->id, $attr->product_attribute_id); @endphp
                    @if($attr->productAttribute->type==1)
                    <div class="product-size-area attr-area">
                        <span class="caption">{{ __($attr->productAttribute->name_for_user) }}</span>
                        @foreach ($attr_data as $data)
                        <div class="product-single-size attribute-btn" data-type="1" data-discount={{$discount}}
                            data-ti="{{$product->track_inventory}}" data-attr_count="{{$attributes->count()}}"
                            data-id="{{$data->id}}" data-product_id="{{$product->id}}"
                            data-price="{{$data->extra_price}}" data-base_price="{{ $product->precioBaseIva}}">
                            {{$data->value}}</div>
                        @endforeach
                    </div>
                    @endif
                    @if($attr->productAttribute->type==2)
                    <div class="product-color-area attr-area">
                        <span class="caption">{{__($attr->productAttribute->name_for_user)}}</span>
                        @foreach ($attr_data as $data)
                        <div class="product-single-color attribute-btn" data-type="2"
                            data-ti="{{$product->track_inventory}}" data-discount={{$discount}}
                            data-attr_count="{{$attributes->count()}}" data-id="{{$data->id}}"
                            data-product_id="{{$product->id}}" data-bg="{{$data->value}}"
                            data-price="{{$data->extra_price}}" data-base_price="{{ $product->precioBaseIva}}"></div>
                        @endforeach
                    </div>

                    @endif
                    @if($attr->productAttribute->type==3)
                    <div class="product-color-area attr-area">
                        <span class="caption">{{__($attr->productAttribute->name_for_user)}}</span>
                        @foreach ($attr_data as $data)
                        <div class="product-single-color attribute-btn bg_img" data-type="3"
                            data-ti="{{$product->track_inventory}}" data-discount={{$discount}}
                            data-attr_count="{{$attributes->count()}}" data-id="{{$data->id}}"
                            data-product_id="{{$product->id}}" data-price="{{$data->extra_price}}"
                            data-base_price="{{ $product->precioBaseIva}}"
                            data-background="{{ getImage(imagePath()['attribute']['path'].'/'. @$data->value) }}">
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endforeach

                    <div class="cart-and-coupon mt-3">

                        <div class="attr-data">
                        </div>

                        <div class="cart-plus-minus quantity ">
                            <!-- <div class="cart-decrease qtybutton dec">
                                <i class="las la-minus"></i>
                            </div> -->

                            <select onchange="QuantityValue(this.value,'{{ $product->id }}')" type="number"
                                id="quantity{{ $product->id }}" name="quantity" step="1" min="1"
                                class="integer-validation quantity{{ $product->id }} form-control">
                                @if($quantity > 0)
                                @for ($i = 1; $i < $quantity+1; $i++) <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                    @endif
                            </select>
                            <!--                             <div class="cart-increase qtybutton inc">
                                <i class="las la-plus"></i>
                            </div> -->
                        </div>

                    </div>

                    <div>

                        <p>{{ $product->iva==1 ? 'Precio incluye IVA' : 'Exento'}}</p>

                        <p class="c-link">

                            @lang('Categories'):

                            @foreach ($product->categories as $category)
                            <a
                                href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{
                                __($category->name) }}</a>
                            @if(!$loop->last)
                            /
                            @endif
                            @endforeach
                        </p>
                        {{--
                        <!--         <p>
                            <b>@lang('Model'):</b> {{ __($product->model) }}
                        </p>
                        <p>
                            <b>@lang('Brand'):</b> {{ __($product->brand->name) }}
                        </p>

                        <p>
                            <b>@lang('SKU'):</b> <span class="product-sku">{{$product->sku??__('Not Available')}}</span>
                        </p>

                        <p class="product-share">
                            <b>@lang('Share'):</b>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" title="@lang('Facebook')">

                                <i class="fab fa-facebook"></i>
                            </a>

                            <a href="http://pinterest.com/pin/create/button/?url={{urlencode(url()->current()) }}&description={{ __($product->name) }}&media={{ getImage('assets/images/product/'. @$product->main_image) }}" title="@lang('Pinterest')">

                                <i class="fab fa-pinterest-p"></i>
                            </a>

                            <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{urlencode(url()->current()) }}&amp;title=my share text&amp;summary=dit is de linkedin summary" title="@lang('Linkedin')">

                                <i class="fab fa-linkedin"></i>
                            </a>

                            <a href="https://twitter.com/intent/tweet?text={{ __($product->name) }}%0A{{ url()->current() }}" title="@lang('Twitter')">

                                <i class="fab fa-twitter"></i>
                            </a>
                        </p>
                        @php
                            $wCk = checkWishList($product->id);
                        @endphp
                        <p class="product-details-wishlist">
                            <b>@lang('Add To Wishlist'): </b>
                            <a href="javascript:void(0)" title="@lang('Add To Wishlist')" class="add-to-wish-list {{$wCk?'active':''}}" data-id="{{$product->id}}"><span class="wish-icon"></span></a>
                        </p> -->
                        --}}
                        @if($product->meta_keywords)
                        <p>
                            <b>
                                @lang('Tags'):
                            </b>
                            @foreach ($product->meta_keywords as $tag)
                            <a href="">{{ __($tag) }}</a>@if(!$loop->last),@endif
                            @endforeach
                        </p>
                        @endif
                        @php
                            $rate = session()->get('rate');
                            $moneda = session()->get('moneda');
                        @endphp
                        <div class="price-btn">
                            <div class="price">
                                @if($moneda == 'Dolares' || $moneda == '')
                                    @if($discount > 0)
                                    {{ $general->cur_sym }}<span class="special_price">{{ getAmount($product->precioBaseIva-
                                        $discount, 2) }}</span>
                                    <del>{{ $general->cur_sym }}</del><del class="price-data">{{
                                        getAmount($product->precioBaseIva, 2) }}</del>
                                    @if(!is_null($product->prime_price) )
                                    <br>
                                    Prime: {{ $general->cur_sym }}{{ getAmount($product->precioPrimeIva??$product->prime_price, 2) }}
                                    @endif
                                    @else
                                    {{ $general->cur_sym }}<span class="price-data">{{ getAmount($product->precioBaseIva, 2)
                                        }}</span>
                                    @if(!is_null($product->prime_price) )
                                    <br>
                                    Prime: {{ $general->cur_sym }}{{ getAmount($product->precioPrimeIva??$product->prime_price, 2) }}
                                    @endif
                                    @endif
                                @else
                                    @if($discount > 0)
                                    {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}<span class="special_price">{{ getAmount(($product->precioBaseIva - $discount) * $rate, 2)  }}</span>
                                    <del>{{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}</del><del class="price-data">{{getAmount($product->precioBaseIva* $rate, 2) }}</del>
                                    @if(!is_null($product->prime_price) )
                                    <br>
                                    Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($product->precioPrimeIva??$product->prime_price * $rate, 2) }}
                                    @endif
                                    @else
                                    {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}<span class="price-data">{{ getAmount($product->precioBaseIva * $rate, 2)
                                        }}</span>
                                    @if(!is_null($product->prime_price) )
                                    <br>
                                    Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($product->precioPrimeIva??$product->prime_price * $rate, 2) }}
                                    @endif
                                    @endif   
                                @endif
                            </div>

                            <div class="add-cart">
                                <button type="submit" class="cmn-btn cart-add-btn"
                                    data-id="{{ $product->id }}">Agregar</button>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
<!-- Product Single Section Ends Here -->

<!-- Product Single Section Starts Here -->



<div class="shop-category-container">
    <div class="shop-category-products">      
        <div class="container-fluid">
            <h3 class="carrusel-titulo">
                <span> @lang('Related Products') </span>
                
            </h3>
        </div>
        <div class="shop-category-products">
            <div class="product-slider-2 owl-carousel owl-theme">
                @foreach ($related_products as $item)
                    @php $quantity = $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : 0 @endphp

                    @php
                        if($item->offer && $item->offer->activeOffer){
                            $discount = calculateDiscount($item->offer->activeOffer->amount, $item->offer->activeOffer->discount_type, $item->base_price);
                        }else $discount = 0;
                        $wCk = checkWishList($item->id);
                        $cCk = checkCompareList($item->id);
                    @endphp
                     <div class="item" style="padding-bottom: 10px;">
                        <div class="item-prod" id="app-{{$item->id}}" style="margin:0px !important;">
                            <div class="item-bord">                                
                                <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">
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
                                        <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->main_image, imagePath()['product']['size']) }}" alt="@lang('flash')" class="img-prin img-fluid">
                                    </div>
                                </a> 
                                <div class="item-descp">
                                        
                                        <span class="screenReaderOnlyText"></span>                                                    
                                        <h3 class="item-nomb">                                                         
                                            <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}" class="mr-2 mb-2">{{ __($item->name) }}</a>
                                            
                                        </h3>
                                        <p><span class="item-disp stock-argo">({{ $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : '0' }} @lang('product avaliable') )</span></p>
                                        <p class="producto-categ">
                                            @if(isset($item['categories']) && ($item['categories']->count() > 0 ) ) 
                                                @foreach($item['categories'] as $category)
                                                <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{ __($category->name) }}</a>
                                                    @if(!$loop->last)
                                                    /
                                                    @endif                                 
                                                @endforeach
                                            @endif
                                        </p>
                                        <p class="producto-categ">
                                            <span data-automation-id="price-per-unit">{{ $item->iva == 1 ? 'IVA Incluido' : 'Exento'}}</span>
                                        </p>
                                </div> 
                                <div style="display: none;" class="item-prod-argo badgeProduct{{$item->id}}"></div>
                                <div class="item-final">
                                    <div class="prec-area">
                                        <span class="prec-vent">
                                            @php
                                                $rate = session()->get('rate');
                                                $moneda = session()->get('moneda');
                                            @endphp
                                            <span>
                                            @if($moneda=='Dolares' || $moneda == '')
                                                @if($discount > 0)
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount, 2)}}
                                                <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                    @if(!is_null($item->prime_price) )
                                                        <br>
                                                        Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                    @endif 
                                                @else
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                    @if(!is_null($item->prime_price) )
                                                        <br>
                                                        Prime: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                    @endif 
                                                @endif
                                            @else 
                                                @if($discount > 0)
                                                {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva - $discount * $rate, 2) }}
                                                <del>{{ getAmount($item->precioBaseIva * $rate, 2) }}</del>
                                                    @if(!is_null($item->prime_price) )
                                                        <br>
                                                        Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                    @endif 
                                                @else
                                                {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva * $rate, 2) }}
                                                    @if(!is_null($item->prime_price) )
                                                        <br>
                                                        Prime: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                    @endif 
                                                @endif
                                            @endif
                                            </span>
                                        </span>
                                    </div> 
                                    <div class="btn-area">
                                                    
                                        <button @click="isShow = true" type="submit" class="cmn-btn-argo-item cart-add-btn showProduct{{ $item['id'] }}" data-id="{{ $item['id'] }}">@lang('Agregar')</button>
                                        
                                        <div class="cart-plus-minus quantity">
                                            {{--<div class="cart-decrease qtybutton dec">
                                                <i class="las la-minus"></i>
                                            </div>
                                            <select style="display: none;width: 80px;height: 40px;" 
                                            onchange="QuantityValue(this.value,'{{ $item->id }}')" 
                                            type="number" id="quantity{{ $item['id'] }}" name="quantity" step="1" min="1" class="custom-select integer-validation quantity{{ $item['id'] }} form-control">
                                                @if($quantity > 0)
                                                @for ($i = 1; $i < $quantity+1; $i++)
                                                <option value="{{$i}}">{{$i}}</option>
                                                @endfor
                                                @endif
                                            </select>--}}
                                            {{--<div class="cart-increase qtybutton inc">
                                                <i class="las la-plus"></i>
                                            </div>--}}
                                        </div> 

                                        <form style="display: none;"  novalidate="" name="formSelect" class="ng-pristine ng-valid ng-touched quantity{{ $item['id'] }}">
                                            <span class="ng-star-inserted" style="">
                                            <i class="fas fa-check"></i>&nbsp;Agregado</span><!---->
                                            <select onchange="QuantityValue(this.value,'{{ $item->id }}')" formcontrolname="cantidad" class="custom-select" style="" id="quantity{{ $item['id'] }}" name="quantity">
                                            @if($quantity > 0)
                                            @for ($i = 1; $i < $quantity+1; $i++)
                                            <option value="{{$i}}">{{$i}}</option>
                                            @endfor
                                            @endif
                                            </select>
                                        </form>
                                        
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
                            });
                        </script>
                    @endpush
                @endforeach
            </div>
        </div>
    </div>
</div>
{{--<div class="products-description padding-bottom padding-top-half bg-related">
    <div class="container">

        
        
            @if($related_products)
            <div class="related-products">
                <h5 class="title bold mb-3 mb-lg-4">@lang('Related Products')</h5>
                <div class="m--15 oh">
                    <div class="related-products-slider owl-carousel owl-theme">
                        @foreach ($related_products as $item)

                        @php
                        if($item->offer && $item->offer->activeOffer){
                        $discount_amount = calculateDiscount($item->offer->activeOffer->amount,
                        $item->offer->activeOffer->discount_type, $item->base_price);
                        }else $discount_amount = 0;

                        $wCk = checkWishList($item->id);
                        $cCk = checkCompareList($item->id);
                        @endphp
                        <div id="app-{{$item->id}}">
                            <div class="product-item-2">
                                <div class="product-item-2-inner wish-buttons-in">
                                    @if(isset($item->offer))
                                    @if($item->offer['activeOffer'])
                                                            @if($item->offer['activeOffer']['discount_type'] == 2)
                                                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}% </span>
                                                            @else 
                                                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}$ </span>
                                                            @endif
                                    @endif
                                    @endif
                                    <div class="product-thumb">
                                        <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">
                                            <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->main_image, imagePath()['product']['size']) }}"
                                                alt="@lang('flash')">
                                        </a>
                                    </div>
                                    <div style="display: none;" class="item-prod-argo badgeProduct{{$item->id}}"></div>
                                    <div class="product-content">
                                        <div class="product-before-content">
                                            <h6 class="title">
                                                <a
                                                    href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">{{
                                                    $item->name }}</a>
                                            </h6>
                                            <div class="stock-argo">({{ $item['stocks']->count() > 0 ?
                                                $item['stocks'][0]->quantity : '0' }} @lang('product avaliable') )</div>

                                            <div class="argo-tag-category">
                                                @php
                                                    $category_name = '';
                                                    $category_url = '';
                                                @endphp

                                                @foreach ($product->categories as $category)
                                                    @php
                                                        $category_name = $category->name;
                                                        $category_url = route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]);
                                                    @endphp
                                                <a
                                                    href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{
                                                    __($category->name) }}</a>
                                                @if(!$loop->last)
                                                /
                                                @endif
                                                @endforeach
                                            </div>

                                            <!-- 
                                        <div class="ratings-area justify-content-between">
                                            <div class="ratings">
                                                @php echo __(display_avg_rating($item->reviews)) @endphp
                                            </div>
                                            <span class="ml-2 mr-auto">({{ __($item->reviews->count()) }})</span> 
                                        <div class="price">
                                                @if($discount_amount > 0)
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount_amount, 2) }}
                                                <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                @else
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                @endif
                                            </div>
                                        </div> -->

                                        </div>

                                    </div>
                                    <div class="product-argo">
                                        <div class="price">
                                            @if($moneda == 'Dolares' || $moneda == '')
                                                @if($discount_amount > 0)
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva - $discount_amount, 2) }}
                                                <del>{{ getAmount($item->precioBaseIva, 2) }}</del>
                                                @else
                                                {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                @endif
                                            @else
                                                @if($discount_amount > 0)
                                                Bs{{ getAmount(($item->precioBaseIva - $discount_amount) * $rate, 2) }}
                                                <del>{{ getAmount($item->precioBaseIva * $rate, 2) }}</del>
                                                @else
                                                Bs{{ getAmount($item->precioBaseIva * $rate, 2) }}
                                                @endif
                                            @endif
                                        </div>
                                        <div class="argo-count">
                                            <button @click="isShow = true" type="submit"
                                                class="cmn-btn-argo cart-add-btn showProduct{{ $item['id'] }}"
                                                data-id="{{ $item['id'] }}">@lang('Agregar')</button>

                                            <div class="cart-plus-minus quantity">
                                                <div class="cart-decrease qtybutton dec">
                                                    <i class="las la-minus"></i>
                                                </div>
                                                <input v-show="isShow" v-model.number="bagde"
                                                    id="quantity{{ __($item->id) }}" type="number" name="quantity" step="1"
                                                    min="1" value="1"
                                                    class="integer-validation quantity{{ __($item->id) }}">
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
                                    bagde: "1",
                                    isHidden: true,
                                    isShow: false

                                }
                            })

                            $(".cart-add-btn").click(function(){
                                $("#app-{{$item->id}} .cart-add-btn").addClass("appss-c4");

                            });
                            console.log(app3);
                        </script>
                        @endpush
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        
    </div>
</div>--}}
@endsection

@push('script')
<script>
    'use strict';
    (function($){
        var pid = '{{ $product->id }}';
        load_data(pid);
        function load_data(pid, url="{{ route('product_review.load_more') }}") {
            $.ajax({
                url: url,
                method: "GET",
                data: { pid: pid },
                success: function (data) {
                    $('#load_more_button').remove();
                    $('.review-area').append(data);
                }
            });
        }
        $(document).on('click', '#load_more_button', function () {
            var id  = $(this).data('id');
            var url = $(this).data('url');
            $('#load_more_button').html(`<b>{{ __('Loading') }} <i class="fa fa-spinner fa-spin"></i> </b>`);
            load_data(pid, url);
        });

    })(jQuery)

</script>
@endpush

@push('breadcrumb-plugins')
<li><a href="{{route('home')}}">@lang('Home')</a></li>
@if( (isset($category_url) && !is_null($category_url)) && !is_null($category_name))
    <li><a href="{{ $category_url }}">{{ $category_name }}</a></li>
@endif
@endpush


@push('meta-tags')
@include('partials.seo', ['seo_contents'=>@$seo_contents])
@endpush