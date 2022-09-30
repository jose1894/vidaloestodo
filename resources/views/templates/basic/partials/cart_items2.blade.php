

@forelse ($data as $key => $item)

    

    <div class="single-product-item item-argo">

        @if($item->product->is_plan != 1)

        <div class="cart-plus-minus quantity cart-add-qty" data-id="{{$item->id}}" data-product_id="{{$item->product_id}}"  >                                               

            <input  type="number" id="quantity_qty{{ __($item->product->id) }}" name="quantity" step="1" min="1" value="{{ $item->quantity }}" class="integer-validation">                                        

        </div>

        @endif

        <div class="thumb">

            @if($item->product->is_plan == 1)

                @if($item->product->planDetails->meses <= 10) 

                    <img class="card-img-top" src="assets/images/nosotros/women-p1.jpg" alt="Card image cap">

                @else

                    <img class="card-img-top" src="assets/images/nosotros/women-p2.jpg" alt="Card image cap">

                @endif

            @else

            <img src="{{ getImage(imagePath()['product']['path'].'/'.@$item->product->main_image, imagePath()['product']['size']) }}" alt="@lang('shop')">

            @endif

            

        </div>

    <div class="content x-force-flex">

    <div class="x-content-t-p">

            <h4 class="title"><a class="cl-white" href="{{route('product.detail', ['id'=>$item->product->id, 'slug'=>slug($item->product->name)])}}">{{ __($item->product->name) }}</a></h4>

            <div class="price">

                <span class="price">

                    @if(session('moneda') == 'Dolares')

                        {{$general->cur_sym}}

                    @else  

                        Bs

                    @endif

                     

                    @php

                        if($item->attributes != null){

                            $s_price = priceAfterAttribute($item->product, $item->attributes);

                            echo getAmount($s_price, 2);

                        }else{

                            if($item->product->offer && $item->product->offer->activeOffer){

                                if($item->product->offer){

                                    if($item->is_prime == 1){

                                        $s_price = ($item->product->precioPrimeIva??$item->product->precioBaseIva) - calculateDiscount($item->product->offer->activeOffer->amount, $item->product->offer->activeOffer->discount_type, ($item->product->precioPrimeIva??$item->product->precioBaseIva) );

                                    }

                                    else{

                                        $s_price = $item->product->precioBaseIva - calculateDiscount($item->product->offer->activeOffer->amount, $item->product->offer->activeOffer->discount_type, $item->product->precioBaseIva);

                                    }

                                    

                                }

                                if(session()->get('moneda') == 'Dolares'){

                                    echo getAmount($s_price, 2);

                                }

                                else{

                                    $rate = session()->get('rate');

                                    echo getAmount(($s_price * $rate), 2);

                                }

                            }

                            else{

                                if($item->is_prime == 1){

                                    $s_price = $item->product->precioPrimeIva ?? $item->product->precioBaseIva;

                                    if(session()->get('moneda') == 'Dolares'){

                                        echo getAmount($s_price, 2);

                                    }

                                    else{

                                        $rate = session()->get('rate');

                                        echo getAmount(($s_price * $rate), 2);

                                    }

                                    

                                    

                                }

                                else{

                                    $s_price = $item->product->precioBaseIva;

                                    if(session()->get('moneda') == 'Dolares'){

                                        echo getAmount($s_price, 2);

                                    }

                                    else{

                                        $rate = session()->get('rate');

                                        echo getAmount(($s_price * $rate), 2);

                                    }

                                }

                                

                            }

                            

                        }

                    @endphp

                    x {{ $item->quantity }}

                </span>

            </div>

        </div>

            <div class="text-white">

                @if($item->attributes != null)

                    @php echo cartAttributesShow($item->attributes) @endphp

                @endif

            </div>



            <a href="javascript:void(0)" class="remove-cart remove-item-button-cart" data-id="{{$item->id}}" data-pid="{{$item->product->id}}">

                

                <img src="{{ getImage('assets/images/icos/delete-ico.png', '50x50') }}" alt="Eliminar Producto">

            </a>



        

        </div>

    </div>



@empty

    <div class="single-product-item no_data">

        <div class="no_data-thumb w-50 ml-auto mr-auto mb-4 text-white">

            <i class="la la-shopping-basket la-10x"></i>

        </div>

        <h6 class="cl-white">{{__($empty_message)}}</h6>

    </div>

@endforelse







<div class="x-down-force">

<div class="delete-all-p">

    <a href="javascript:void(0)" class="remeve-all-btn" >Borrar Lista de Compra</a>

</div>





<!-- subtotal -->

@if($subtotal > 0)

    <div class="d-flex justify-content-between mt-3 x-finalizar">

        @if(session('moneda') == 'Dolares')

            <span class="text-argo-total"> @lang('Subtotal') {{ $general->cur_sym }}{{ getAmount($subtotal, 2) }}</span>

        @else  

            <span class="text-argo-total"> @lang('Subtotal') {{ session('moneda') == 'Euros' ? '€. ' : 'Bs. ' }} {{ getAmount($subtotal * $rate, 2) }}</span>

        @endif

        @if($data->count()>0)

            <div class="text-center xforbtn">

                <a href="#" onclick="FinishBuy()" class="cmn-btn-argo">

                    @if($more> 0)

                        Y {{$more}} Más

                    @else

                        Finalizar Compra

                    @endif

                </a>

            </div>

        @endif

    </div>

    @if($tasa->count() > 0)  

        <div class="d-flex justify-content-between">

            <span class="text-argo-total"> @lang('Tasa') {{ getAmount($tasa->tasa_del_dia, 2)}} {{ session('moneda') == 'Euros' ? '€. ' : 'Bs. ' }} </span>

        </div>

    @endif  

    <div class="d-flex justify-content-between">

        <span class="text-argo-total"> @lang('Total') {{ getAmount($total, 2) }} {{ session('moneda') == 'Euros' ? '€. ' : 'Bs. ' }} </span>

    </div>           



    @if($coupon)



        <div class="coupon-wrapper">

            <div class="d-flex mt-1 text-white">

                <span class="mr-2 text-danger remove-coupon"><i class="la la-times-circle"></i></span>

                <span>@lang('Coupon') (<b class="couponCode1">{{$coupon['code']}}</b>) </span>

                <div class="ml-auto">

                    <span class="amount">{{$general->cur_sym}}<span class="couponAmount"> {{ getAmount($coupon['amount'], 2) }}</span> </span>

                </div>

            </div>



            <div class="d-flex justify-content-between mt-1 text-white border-top-1">

                <span class="text-white"> @lang('Total Amount') </span>

                <span class="text-white">{{ $general->cur_sym }}{{ getAmount($subtotal - $coupon['amount'], 2) }}</span>

            </div>

        </div>



    @endif

@endif

</div>



