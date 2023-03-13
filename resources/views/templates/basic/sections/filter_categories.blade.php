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
                                                        <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}" class="mr-2 mb-2" style="font-size:13px">{{ __($item->name) }}</a>
                                                        </a>
                                                    </h3>
                                                    <p><span class="item-disp stock-argo">({{ $item['stocks']->count() > 0 ? $item['stocks'][0]->quantity : '0' }} @lang('product avaliable') )</span></p>
                                                    <p style="font-size:12px" class="producto-brand">
                                                        <span data-automation-id="brand">Marca:</span> 
                                                        <span style="color:black">{{ $item->brand ? $item->brand->name : 'No definida'}}</span>
                                                    </p>
                                                    <p style="font-size:12px" class="producto-cod_int">
                                                        <span data-automation-id="cod_int">Codigo:</span>
                                                        <span style="color:black">{{ $item->codigo_int }}</span>
                                                    </p>
                                                    {{-- <p class="producto-categ">
                                                        @if(isset($item['categories']) && ($item['categories']->count() > 0 ) ) 
                                                            @foreach($item['categories'] as $category)
                                                            <a href="{{ route('products.category', ['id'=>$category->id, 'slug'=>slug($category->name)]) }}">{{ __($category->name) }}</a>
                                                                @if(!$loop->last)
                                                                /
                                                                @endif                                 
                                                            @endforeach
                                                        @endif
                                                    </p>--}}
                                                    <p class="producto-categ">
                                                        <span style="font-size:10px" data-automation-id="price-per-unit">{{ $item->iva == 1 ? 'IVA Incluido' : 'Exento'}}</span>
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
                                                                @if($item->prime_price > 0 )
                                                                    <br>
                                                                    Premiunm {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                @endif 
                                                            @else
                                                            {{ $general->cur_sym }}{{ getAmount($item->precioBaseIva, 2) }}
                                                                @if ($item->prime_price > 0 && $item->precioBaseIva !== $item->precioPrimeIva)
                                                                    <br>
                                                                    Premium: {{ $general->cur_sym }}{{ getAmount($item->precioPrimeIva??$item->prime_price, 2) }}
                                                                @endif 
                                                            @endif
                                                        @else 
                                                            @if($discount > 0)
                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva - $discount * $rate, 2) }}
                                                            <del>{{ getAmount($item->precioBaseIva * $rate, 2) }}</del>
                                                                @if($item->prime_price > 0 )
                                                                    <br>
                                                                    Premium: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                                @endif 
                                                            @else
                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioBaseIva * $rate, 2) }}
                                                                @if($item->prime_price > 0 )
                                                                    <br>
                                                                    Premium: {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->precioPrimeIva??$item->prime_price * $rate, 2) }}
                                                                @endif 
                                                            @endif
                                                        @endif
                                                        </span>
                                                    </span>
                                                </div> 
                                                <div class="btn-area">
                                                    
                                                    <button @click="isShow = true" type="submit" class="cmn-btn-argo-item cart-add-btn showProduct{{ $item['id'] }}" data-id="{{ $item['id'] }}">@lang('Agregar')</button>
                                                    
                                                    

                                                    <form style="display: none;"  novalidate="" name="formSelect" class="ng-pristine ng-valid ng-touched quantity{{ $item['id'] }}">
                                                        <span class="ng-star-inserted" style="">
                                                        <i class="fas fa-check"></i>&nbsp;Agregado</span><!---->
                                                        @if(!$item->usa_gramaje)
                                                            <select onchange="QuantityValue(this.value,'{{ $item->id }}')" formcontrolname="cantidad" class="custom-select" style="" id="quantity{{ $item['id'] }}" name="quantity">
                                                            @if($quantity > 0)
                                                            @for ($i = 1; $i < $quantity+1; $i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                            @endif
                                                            </select>
                                                        @else        
                                                            <div class="container-fluid">
                                                                <div class="row justify-content-center">
                                                                    <div class="col-3" style="padding:0">
                                                                        
                                                                        <button 
                                                                        class="cmn-btn-argo-item btn btn-sm" 
                                                                        style="margin:5px auto;padding:.375rem 0"
                                                                        onclick="addAndSubstract('{{ $item->id }}','-'); return false;"> <i class="fa fa-minus"></i> </button>
                                                                        
                                                                    </div>
                                                                    <div class="col-6" style="padding-left: 0px;padding-right: 0px;">
                                                                        <input 
                                                                            class="form-control my-1 gramaje" 
                                                                            value="0.25" 
                                                                            type="number" 
                                                                            readonly 
                                                                            onblur="QuantityValue(this.value, '{{ $item->id }}')" 
                                                                            formcontrolname="cantidad"
                                                                            id="quantity{{ $item['id'] }}" 
                                                                            name="quantity"
                                                                            style="text-align:right;font-size: 0.8rem;">
                                                                    </div>
                                                                    <div class="col-3" style="padding:0">
                                                                        <button 
                                                                            class="cmn-btn-argo-item btn btn-sm" 
                                                                            style="margin:5px auto;padding:.375rem 0"
                                                                            onclick="addAndSubstract('{{ $item->id }}','+'); return false;"> <i class="fa fa-plus"></i> </button>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        @endif
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