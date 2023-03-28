@extends(activeTemplate() .'layouts.master')

@section('content')
    <!-- Checkout Section Starts Here -->
    <div class="checkout-section padding-bottom padding-top">
        <div class="container">
            <div class="checkout-area section-bg">
                <div class="row flex-wrap-reverse">
                    <div class="col-md-6 col-lg-7 col-xl-8">
                        <div class="checkout-wrapper">
                            <h4 class="title text-center check-title">Dirección de Envío</h4>
                            <ul class="nav-tabs nav justify-content-center">
                                <li>
                                    <a href="#self" data-toggle="tab">
                                        <div class="">
                                            <div class="row">
                                                <div class="col">
                                                    <img src="../assets/images/fogolar-20.svg">
                                                        </div>
                                                <div class="col">
                                                        &nbsp;Para ti
                                                    </div>
                                            </div>
                                        </a>
                                </li>
                                <li>
                                    <a href="#guest" data-toggle="tab">
                                        <div class="">
                                                    <div class="row">
                                                        <div class="col">
                                                                <img src="../assets/images/fogolar-21.svg">
                                                        </div>
                                                <div class="col">
                                                                &nbsp;Como regalo
                                                                     </div>
                                                                    </div>
                                        </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane show fade active" id="self">
                                    <form action="{{route('user.checkout-to-payment', 1)}}" method="post" class="billing-form mb--20">
                                        @csrf

                                        <div class="row">


                                            <div class="col-lg-12 mb-20">
                                                <label for="shipping-method" class="billing-label">Método de envío</label>
                                                <div class="billing-select">
                                                    <select name="shipping_method" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach ($shipping_methods as $sm)
                                                            <option data-shipping="{{$sm->description}}" data-charge="{{$sm->charge}}" value="{{$sm->id}}">{{$sm->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-6 mb-20">
                                                <label for="fname" class="billing-label">@lang('First Name')</label>
                                                <input class="form-control custom--style" id="fname" type="text" name="firstname" value="{{auth()->user()->firstname?? old('firstname')}}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="lname" class="billing-label">@lang('Last Name')</label>
                                                <input class="form-control custom--style" id="lname" name="lastname" type="text" value="{{auth()->user()->lastname?? old('lastname')}}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="phone" class="billing-label">@lang('Mobile')</label>
                                                <input class="form-control custom--style" id="phone" name="mobile" type="text" value="{{auth()->user()->mobile?? old('mobile')}}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="email" class="billing-label">@lang('Email')</label>
                                                <input class="form-control custom--style" id="email" name="email" type="text" value="{{auth()->user()->email?? old('mobile')}}" required>
                                            </div>

                                            <div class="col-lg-6 mb-20">
                                                <label for="country" class="billing-label">@lang('Country')</label>
                                                <div class="billing-select">
                                                    <select name="country" id="country" class="select-bar" required>
                                                        @include('partials.country')
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-20">
                                                <label for="city" class="billing-label">@lang('City')</label>
                                                <input class="form-control custom--style" id="city" name="city" type="text" value="{{auth()->user()->address->city?? old('city')}}" required>
                                            </div>

                                            <div class="col-md-6 mb-20">
                                                <label for="state" class="billing-label">@lang('State')</label>
                                                <input class="form-control custom--style" id="state" name="state" type="text" value="{{auth()->user()->address->state?? old('state')}}" required>
                                            </div>

                                            <div class="col-md-6 mb-20">
                                                <label for="zip" class="billing-label">@lang('Zip/Post Code')</label>
                                                <input class="form-control custom--style" id="zip" name="zip" type="text" value="{{auth()->user()->address->zip?? old('zip')}}" required>
                                            </div>

                                            <div class="col-md-12 mb-20">
                                                <label for="address" class="billing-label">@lang('Address')</label>
                                                <textarea class="form-control custom--style" name="address" id="address" required>{{auth()->user()->address->address??old('address')}}</textarea>
                                            </div>

                                            <input min="0" step="any" type="number" id="propina_form" name="propina_form" value="{{ old('propina_form') }}" style="display:none">
                                        </div>


                                        <div class="row justify-content-end">
                                            <!-- @if($general->cod)
                                            <div class="col-lg-6 mb-20">
                                                <button type="submit" name="cash_on_delivery" value="1" class="bill-button">@lang('Cash On Delivery')</abbr></button>
                                            </div>
                                            @endif -->
                                            <div class="col-lg-12 mb-20" style="text-align: center;">
                                                <button type="" name="payment" value="1" class="bill-button">Continuar con el Pago</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="coupon_amount" id="coupon_amount2">
                                        <input type="hidden" name="id_coupon" id="id_coupon">
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="guest">
                                    <form action="{{route('user.checkout-to-payment', 2)}}" method="post" class="guest-form mb--20">
                                        @csrf

                                        <div class="row">
                                            <div class="col-lg-12 mb-20">
                                                <label for="shipping-method-2" class="billing-label">Método de envío</label>
                                                <div class="billing-select">
                                                    <select name="shipping_method" id="shipping-method-2" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach ($shipping_methods as $sm)
                                                            <option data-shipping="{{$sm->description}}" data-charge="{{$sm->charge}}" value="{{$sm->id}}">{{$sm->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="firstname" class="billing-label">@lang('First Name')</label>
                                                <input class="form-control custom--style" id="firstname" type="text" name="firstname" value="{{ old('firstname') }}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="lastname" class="billing-label">@lang('Last Name')</label>
                                                <input class="form-control custom--style" id="lastname" name="lastname" type="text" value="{{ old('lastname')}}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="mobile" class="billing-label">@lang('Mobile')</label>
                                                <input class="form-control custom--style" id="mobile" name="mobile" type="text" value="{{ old('mobile')}}" required>
                                            </div>
                                            <div class="col-lg-6 mb-20">
                                                <label for="e-mail" class="billing-label">@lang('Email')</label>
                                                <input class="form-control custom--style" id="e-mail" name="email" type="text" required>
                                            </div>

                                            <div class="col-lg-6 mb-20">
                                                <label for="country-2" class="billing-label">@lang('Country')</label>
                                                <div class="billing-select">
                                                    <select name="country" id="country-2" class="select-bar" required>
                                                        @include('partials.country')
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-20">
                                                <label for="city-2" class="billing-label">@lang('City')</label>
                                                <input class="form-control custom--style" id="city-2" name="city" type="text" value="{{ old('city') }}" required>
                                            </div>

                                            <div class="col-md-6 mb-20">
                                                <label for="state-2" class="billing-label">@lang('State')</label>
                                                <input class="form-control custom--style" id="state-2" name="state" type="text" value="{{ old('state') }}" required>
                                            </div>

                                            <div class="col-md-6 mb-20">
                                                <label for="zip-2" class="billing-label">@lang('Zip/Post Code')</label>
                                                <input class="form-control custom--style" id="zip-2" name="zip" type="text" value="{{ old('zip') }}" required>
                                            </div>

                                            <div class="col-md-12 mb-20">
                                                <label for="address-2" class="billing-label">@lang('Address')</label>
                                                <textarea class="form-control custom--style" id="address-2" name="address" required>{{ old('address')}}</textarea>
                                            </div>

                                            <input min="0" step="any" type="number" id="propina_form" name="propina_form" value="{{ old('propina_form') }}" style="display:none">
                                        </div>

                                        <div class="row justify-content-end">
                                            @if($general->cod)
                                                <div class="col-lg-6 mb-20">
                                                    <button type="submit" name="cash_on_delivery" value="1" class="bill-button">@lang('Cash On Delivery')</abbr></button>
                                                </div>
                                            @endif
                                            <div class="col-lg-6 mb-20">
                                                <button type="" name="payment" value="1" class="bill-button">Continuar con el Pago</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="coupon_amount" id="coupon_amount">
                                        <input type="hidden" name="id_coupon" id="id_coupon2">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 col-xl-4">
                        <div class="payment-details">
                            <h4 class="title text-center">@lang('Payment Details')</h4>
                            <div align="center"><img src="../assets/images/fogolar-26.svg" style="text-align:center;" width="70" height="auto"></div>

                        <div class="payment-details-prev">
                            <ul>
                                <li>
                                    <span class="subtitle">@lang('Subtotal')</span>
                                    <span class="text-success" id="cartSubtotal">{{$general->cur_sym}}0</span>
                                </li>
                                @if(session()->has('coupon'))
                                    <li>
                                        <span class="subtitle">@lang('Coupon') ({{session('coupon')['code']}})</span>
                                        <span class="text-success" id="couponAmount">{{$general->cur_sym}}{{ getAmount(session('coupon')['amount'], 2)}}</span>
                                    </li>

                                    <li>
                                        <span class="subtitle">(<i class="la la-minus"></i>)</span>
                                        <span class="text-success" id="afterCouponAmount">{{$general->cur_sym}}0</span>
                                    </li>
                                @endif
                                <li>
                                    <span class="subtitle">@lang('Shipping Charge')</span>
                                    <span class="text-danger" id="shippingCharge">{{$general->cur_sym}}0</span>
                                </li>
                                <li class="border-0">
                                    <span class="subtitle bold">@lang('Total')</span>
                                    <span class="cl-title" id="cartTotal">{{$general->cur_sym}}0</span>
                                </li>

                                <li>
                                    <span class="subtitle bold">Propina (Opcional)

                                    </span>
                                    <!-- <input id="propina_check" type="checkbox" style="height: calc(1em + .50rem + 1px) !important;    width: 6% !important;">                                                    -->
                                    <input min="0" step="any" type="number" id="propina" name="propina" value="{{ isset($propina) ? $propina : 0 }}"  class="mt-2">

                                    <span class="subtitle bold">Código del Cupón</span>
                                    <br>
                                    <span id="viewCoupon" class="mt-2"></span>
                                    <div class="row justify-content-center search-coupon">
                                        <div class="col-md-12">
                                            <input type="text" name="code" class="form-control" placeholder="Código">
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <button class="coupon-button" id="coupon-button" style="height: 40px;
                                                color: #ffffff;
                                                background: var(--blue-argo);
                                                width: 100%;"
                                            >Aplicar Cupón</button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                            <p id="shipping-details">

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Checkout Section Ends Here -->

@endsection

@push('script')
    <script>


        // 'use strict';
        (function($){
            var total = 0;
            var charge = 0;
            var sub_total = parseFloat({{$subtotal}});
            
            $('#cartSubtotal').text(`{{$general->cur_sym}}`+ parseFloat(sub_total).toFixed(2));

            var couponAmount = @if(session()->has('coupon')) {{session('coupon')['amount']}} @else 0 @endif ;

            var afterCouponAmount = (sub_total - couponAmount).toFixed(2);

            var coupon_amount = 0;


            $('#afterCouponAmount').text(afterCouponAmount)
            total = parseFloat(sub_total - couponAmount).toFixed(2);
            $('#cartTotal').text(`{{$general->cur_sym}}`+ total);

            $('select[name=country]').val("{{isset(auth()->user()->address->country)?auth()->user()->address->country:''}}");

            $('select[name=shipping_method]').on('change', function(){
                charge = parseFloat($('option:selected',this).data("charge"));
                var detail = $('option:selected',this).data('shipping');
                var propina = parseFloat($('#propina').val());
                var coupon_amount = $('#coupon_amount').val();

                if(isNaN(charge)){
                    charge = 0;
                }
                if(isNaN(coupon_amount)){
                    coupon_amount = 0;
                };
                if(detail){
                    $('#shipping-details').html(detail);
                }else{
                    $('#shipping-details').html('');
                }
                if(isNaN(propina)){
                    propina = 0;
                }

                $('#shippingCharge').text(`{{$general->cur_sym}}` + parseFloat(charge).toFixed(2));
                total = (parseFloat(afterCouponAmount) + charge + propina - coupon_amount).toFixed(2);
                $('#cartTotal').text(`{{$general->cur_sym}}` + total);
            }).change();

            var totalCheckeds = 0;
            $("input:checkbox").click(function() {
                totalCheckeds = $("input:checkbox:checked").length;
                if (totalCheckeds > 0) {
                    $('#propina').show();
                } else {
                    $('#propina').hide();
                }
            });

            $('#propina').on('input', function(){
                var propina = parseFloat($('#propina').val());
                var coupon_amount = $('#coupon_amount').val();
                if(isNaN(propina)){
                    propina = 0;
                }
                total = (parseFloat(afterCouponAmount) + charge + propina - coupon_amount).toFixed(2);
                $('#cartTotal').text(`{{$general->cur_sym}}` + total);
                $('#propina_form').val(parseFloat(propina));
            }).change();
            $("#coupon-button").click(function() {
                
                var code = $('input[name=code]').val();
                // $('.search-coupon').show();

                if (code.length > 0) {
                    $('#coupon-button').attr('disabled',true);
                    var data        = {code:code};
                    $.ajax({
                        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                        url: "{{route('getCoupon')}}",
                        method:"post",
                        data: data,
                        success: function(response){
                            if(response.error){
                                // $('.quantity input[type=number]').val(response.qty)
                                notify('error', response.error);
                            }else{


                                if (response.coupons.discount_type == 1) {
                                    var coupon_amount2 = parseFloat(response.coupons.coupon_amount);
                                    var show_coupon_amount = response.coupons.coupon_amount+' {{$general->cur_sym}}';
                                }else{
                                    var coupon_amount2 = parseFloat(sub_total * 100 / response.coupons.coupon_amount);
                                    var show_coupon_amount = response.coupons.coupon_amount+'%';
                                }
                                
                                $('#coupon_amount').val(coupon_amount2);
                                $('#coupon_amount2').val(coupon_amount2);

                                $('#id_coupon').val(response.coupons.id);
                                $('#id_coupon2').val(response.coupons.id);

                                coupon_amount = $('#coupon_amount').val();

                                $('#viewCoupon').append(
                                    '<span style="color:green">'+response.coupons.coupon_code+' - '+response.coupons.coupon_name+' <strong>'+show_coupon_amount+'</strong></span><br>'
                                );

                                
                                var propina = parseFloat($('#propina').val());

                                if(isNaN(coupon_amount)){
                                    coupon_amount = 0;
                                };
                                // charge = parseFloat($('option:selected',this).data("charge"));
                                if(isNaN(charge)){
                                    charge = 0;
                                }
                                // parseFloat(charge).toFixed(2))
                                if(isNaN(propina)){
                                    propina = 0;
                                }


                                total = (parseFloat(afterCouponAmount) + charge + propina - coupon_amount).toFixed(2);
                                $('#cartTotal').text(`{{$general->cur_sym}}` + total);

                                notify('success', 'Cupón Agregado con Éxito');
                                $('.search-coupon').hide();
                            }
                        }
                    });

                }else{
                    notify('error', 'Especifique el código de un cupón');
                }
            });

        })(jQuery)
    </script>
@endpush

@push('breadcrumb-plugins')
    <li><a href="{{route('home')}}">@lang('Home')</a></li>
    <li><a href="{{route('products')}}">@lang('Products')</a></li>
    <li><a href="{{route('shopping-cart')}}">@lang('Cart')</a></li>
@endpush


@push('meta-tags')
    @include('partials.seo')
@endpush
