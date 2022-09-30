<div class="xcat-main">
    <div class="container-fluid">
        @foreach ($f_categories as $f_category)
            <div class="shop-category-container" id="cat-{{ $f_category->id }}">
                <div class="shop-category-products">      
                    <div class="container-fluid">
                        <h3 class="carrusel-titulo">
                            <span> {{ __($f_category->name) }} </span>
                            <span class="carrusel-link ng-star-inserted">
                                <a href="{{route('products.category', ['id'=>$f_category->id, 'slug'=>slug($f_category->name)])}}"> Ver más 
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </span>
                        </h3>
                    </div>              
                    
                    <div class="shop-category-products">
                        <div class="product-slider-2 owl-carousel owl-theme">
                            
                            @foreach ($f_category->specialProuducts as $item)
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
                                                        </a>
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
                                        })

                                    </script>
                                @endpush
                            @endforeach
                        </div>
                    </div>                
                </div>
            </div>
        @endforeach

        <div id="show_categories_products"></div>
    </div>
</div>          