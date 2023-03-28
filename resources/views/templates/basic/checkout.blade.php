@extends(activeTemplate() . 'layouts.master')

@section('content')
    {{-- dd($data) --}}
    <!-- Checkout Section Starts Here -->
    <div class="checkout-section padding-bottom padding-top" id="accordion-payment">
        <div class="container-fluid">
            <div class="row mt-4">
                <div class="col-md-4 col-lg-4 col-xl-4">
                    <div class="checkout-wrapper shadow">
                        <div class="shop-category-products">
                            <div class="product-slider-shippin owl-carousel owl-theme">
                                @foreach ($data as $item)
                                    @php
                                        if ($item->product->offer && $item->product->offer->activeOffer) {
                                            $discount = calculateDiscount($item->product->activeOffer->amount, $item->product->activeOffer->discount_type, $item->base_price);
                                        } else {
                                            $discount = 0;
                                        }
                                        $wCk = checkWishList($item->product->id);
                                        $cCk = checkCompareList($item->product->id);
                                    @endphp
                                    <div class="item" style="padding-bottom: 10px;">
                                        <div class="item-prod" id="app-{{ $item->product->id }}"
                                            style="margin:0px !important;">
                                            <div class="item-bord">
                                                <div class="item-img">
                                                    <img src="{{ getImage(imagePath()['product']['path'] . '/thumb_' . @$item->product->main_image, imagePath()['product']['size']) }}"
                                                        alt="@lang('flash')" class="img-prin img-fluid">
                                                </div>
                                                <div class="item-descp">
                                                    <h3 class="item-nomb-1">
                                                        {{ __($item->product->name) }}
                                                    </h3>
                                                    <p class="producto-categ">
                                                        <span
                                                            data-automation-id="price-per-unit">{{ $item->product->iva == 1 ? 'IVA Incluido' : 'Exento' }}</span>
                                                    </p>
                                                </div>
                                                <div class="item-prod-argo badgeProduct{{ $item->id }}">
                                                    {{ $item->quantity }}
                                                </div>
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
                                                                        {{ $general->cur_sym }}{{ getAmount($item->product->precioBaseIva - $discount, 2) }}
                                                                        <del>{{ getAmount($item->product->precioBaseIva, 2) }}</del>
                                                                        @if (!is_null($item->product->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $general->cur_sym }}{{ getAmount($item->product->precioPrimeIva ?? $$item->product->prime_price, 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $general->cur_sym }}{{ getAmount($item->product->precioBaseIva, 2) }}
                                                                        @if (!is_null($item->product->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $general->cur_sym }}{{ getAmount($item->product->precioPrimeIva ?? $item->product->prime_price, 2) }}
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    @if ($discount > 0)
                                                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->product->precioBaseIva - $discount * $rate, 2) }}
                                                                        <del>{{ getAmount($item->product->precioBaseIva * $rate, 2) }}</del>
                                                                        @if (!is_null($item->product->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->product->precioPrimeIva ?? $item->product->prime_price * $rate, 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->product->precioBaseIva * $rate, 2) }}
                                                                        @if (!is_null($item->product->prime_price))
                                                                            <br>
                                                                            Prime:
                                                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->product->precioPrimeIva ?? $item->product->prime_price * $rate, 2) }}
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="payment-details shadow">

                            @php
                                $rate = session()->get('rate');
                                $moneda = session()->get('moneda');
                            @endphp
                            <div class="payment-details-prev">
                                @if ($moneda == 'Dolares')
                                    <ul>
                                        <li>
                                            <span class="subtitle">@lang('Subtotal')</span>
                                            <span class="text-success" id="cartSubtotal">{{ $general->cur_sym }}0</span>
                                        </li>
                                        <li>
                                            <span class="subtitle">@lang('IVA')</span>
                                            <span
                                                class="cl-title">{{ $general->cur_sym }}{{ getAmount($iva, 2) }}</span>
                                        </li>
                                        @if (session()->has('coupon'))
                                            <li>
                                                <span class="subtitle">@lang('Coupon')
                                                    ({{ session('coupon')['code'] }})</span>
                                                <span class="text-success"
                                                    id="couponAmount">{{ $general->cur_sym }}{{ getAmount(session('coupon')['amount'], 2) }}</span>
                                            </li>

                                            <li>
                                                <span class="subtitle">(<i class="la la-minus"></i>)</span>
                                                <span class="text-success" id="afterCouponAmount">{{ $general->cur_sym }}
                                                    @{{ afterCouponAmount }}</span>
                                            </li>
                                        @endif
                                        <li>
                                            <span class="subtitle">Costo de envío</span>
                                            <span class="text-danger"
                                                id="shippingCharge">{{ $general->cur_sym }}0.00</span>
                                        </li>
                                        <li>
                                            <span class="subtitle">Propina</span>
                                            <span class="text-danger" id="propina">{{ $general->cur_sym }}0.00</span>
                                        </li>
                                        <li class="border-0">
                                            <span class="subtitle bold">@lang('Total')</span>
                                            <span class="cl-title" id="cartTotal">{{ $general->cur_sym }}
                                                @{{ total }}</span>
                                        </li>


                                    </ul>
                                @else
                                    <ul>
                                        <li>
                                            <span class="subtitle">@lang('Subtotal')</span>
                                            <span class="text-success" id="cartSubtotal">Bs 0</span>
                                        </li>
                                        <li>
                                            <span class="subtitle">@lang('IVA')</span>
                                            <span class="cl-title">Bs {{ getAmount($iva * $rate, 2) }}</span>
                                        </li>
                                        @if (session()->has('coupon'))
                                            <li>
                                                <span class="subtitle">@lang('Coupon')
                                                    ({{ session('coupon')['code'] }})</span>
                                                <span class="text-success" id="couponAmount">Bs
                                                    {{ getAmount(session('coupon')['amount'] * $rate, 2) }}</span>
                                            </li>

                                            <li>
                                                <span class="subtitle">(<i class="la la-minus"></i>)</span>
                                                <span class="text-success" id="afterCouponAmount">Bs
                                                    @{{ afterCouponAmount }}</span>
                                            </li>
                                        @endif
                                        <li>
                                            <span class="subtitle">Costo de envío</span>
                                            <span class="text-danger" id="shippingCharge">Bs 0.00</span>
                                        </li>
                                        <li>
                                            <span class="subtitle">Propina</span>
                                            <span class="text-danger" id="propina">Bs 0.00</span>
                                        </li>
                                        <li class="border-0">
                                            <span class="subtitle bold">@lang('Total')</span>
                                            <span class="cl-title" id="cartTotal">Bs. @{{ totalbs }}</span>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div v-if="error.length > 0" class="alert alert-danger mt-2" role="alert">
                        <ul>
                            <li v-for="m in error"><i class="fa fa-info" aria-hidden="true"></i> <small>
                                    @{{ m }} </small>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4 col-lg-4 col-xl-4">
                    <div class="checkout-wrapper shadow">
                        <div class="row justify-content-center mt-2">
                            <div class="col-md-6 col-lg-6">
                                <div class="card mb-3 card-envio" id="card_m_envio1" @click="methodEntrega(1)"
                                    onclick="method_entrega(1)">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="checkbox" class="checks-shipping" value="1"
                                                    name="method_entrega" id="method_entrega_1">
                                            </div>
                                            <div class="col-md-9">
                                                <strong>Delivery</strong><br>
                                                <span>
                                                    <small class="textspan">
                                                        Seleccione esta opción si quieres que
                                                        enviemos tu pedido
                                                    </small>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="card mb-3 card-envio" id="card_m_envio2" @click="methodEntrega(2)"
                                    onclick="method_entrega(2)">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="checkbox" class="" value="2"
                                                    name="method_entrega" id="method_entrega_2">
                                            </div>
                                            <div class="col-md-9">
                                                <strong>Pick Up</strong><br>
                                                <span>
                                                    <small class="textspan">
                                                        Seleccione esta opción si quieres buscar
                                                        tu pedido en una de nuestras zonas Pick
                                                        Up
                                                    </small>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-12">

                                <div id="ViewDirection">
                                    <div id="" class="ex1">
                                        <div class="card mb-3 card-shipping" v-for="(item, index) in usershipping"
                                            :key="index">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-2 col-2">
                                                        <input :value="item.id" :id="'checks-shipping-' + item.id"
                                                            v-model="form.shippingUser" type="radio"
                                                            class="form-check-input check-radio-addres"
                                                            name="shippingUser">
                                                    </div>
                                                    <div class="col-md-9 col-10">
                                                        <span>
                                                            <small class="textspan">
                                                                @{{ shipping_address(item.shipping_address) }}
                                                            </small>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <span class="text-danger delete-addres" style="float: right;"
                                                            @click="deleteShippingUser(item.id)">
                                                            <i class="fa fa-trash"></i>
                                                        </span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>



                                    <a href="#" data-toggle="modal" data-target="#addDirection" class="linkadd">
                                        <i class="fa fa-plus"></i> Agregar nueva dirección
                                    </a>
                                </div>
                                <div id="ViewPickup" class="ex1">
                                    @foreach ($shipping_methods_pickup as $key)
                                        <div class="card mb-3 card-shipping" id="card-shipping-{{ $key->id }}">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 col-2">
                                                        <input v-model="form.checkbox_shipping" type="radio"
                                                            class="form-check-input check-radio-addres"
                                                            name="checkbox_shipping" value="{{ $key->id }}"
                                                            id="checks-shipping-{{ $key->id }}">
                                                    </div>
                                                    <div class="col-md-6 col-6">
                                                        <span>
                                                            <small class="textspan">
                                                                {{ $key->name }}<br>
                                                                {!! $key->description !!}
                                                            </small>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-3 text-center col-3">
                                                        <strong>Carga</strong><br>
                                                        <span>{{ $general->cur_sym }}
                                                            {{ getAmount($key->charge, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                       
                                    @endforeach
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="checkout-wrapper shadow mt-2">
                        <input type="hidden" name="shippingChargeM" id="shippingCharge2" value="0">
                        <div class="row">
                            <div class="col-md-7 col-lg-7 col-xl-7">
                                <div class="row">
                                    <div class="col-md-12 col-lg-12">
                                        <label class="texto-correcto ">
                                            <strong>
                                                Seleccione una Fecha
                                            </strong>
                                        </label>
                                    </div>
                                    <div class="col-md-12 col-lg-12">
                                        <div class="row">
                                            @foreach ($fechas as $key => $fe)
                                                <div class="col-lg-4">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="form-check">
                                                                <input v-model="form.order_time"
                                                                    value="{{ $fe['date'] }}"
                                                                    class="form-check-input check-radio-time"
                                                                    type="radio" name="order_time" id="order_time">
                                                                <label class="form-check-label" for="order_time">
                                                                    <strong>{{ Str::limit(ucwords($fe['name']), 3, '') }}</strong>
                                                                </label>
                                                            </div>
                                                            <label for="shipping-method"
                                                                class="text-center billing-label textspan">
                                                                {{ $fe['fecha'] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 col-lg-5 col-xl-5">
                                <div class="row mt-2">
                                    <div class="col-md-12 col-lg-12">
                                        <label class="texto-correcto ">
                                            <strong>
                                                Seleccione un horario de envío o Pick up
                                            </strong>
                                        </label>
                                    </div>
                                    <div class="col-md-12 col-lg-12 mt-1">

                                        <select v-model="form.order_time_horario" id="order_time_horario"
                                            name="order_time_horario" class="form-control">
                                            <option selected disabled value="0"></option>
                                            <option value="1">Mañana</option>
                                            <option value="2">Tarde</option>
                                            <option value="3">Noche</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="checkout-wrapper shadow mt-2">
                        <div class="row justify-content-center ">
                            <div class="col-md-12 col-lg-12">
                                <label class="texto-correcto ">
                                    <strong>Seleccione una Propina (Opcional)</strong>
                                </label>
                            </div>
                            <div class="col-md-12 col-lg-12">
                                <div class="row">
                                    <label class="hora-item cursor btnFormProp text-center" data-amount="0"
                                        onclick="ButtomPropina(0)" @click="tipPrice(0)"> 0,00 $
                                    </label>
                                    <label class="hora-item cursor btnFormProp text-center" data-amount="1"
                                        onclick="ButtomPropina(1)" @click="tipPrice(1)"> 1,00 $
                                    </label>
                                    <label class="hora-item cursor btnFormProp text-center" data-amount="2"
                                        onclick="ButtomPropina(2)" @click="tipPrice(2)"> 2,00 $
                                    </label>
                                    <label class="hora-item cursor btnFormProp text-center" data-amount="5"
                                        onclick="ButtomPropina(5)" @click="tipPrice(5)"> 5,00 $
                                    </label>
                                    <label class="hora-item cursor btnFormProp text-center" data-amount="10"
                                        onclick="ButtomPropina(10)" @click="tipPrice(10)"> 10,00 $
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 mt-1">
                                        <label class="texto-correcto ">
                                            <strong>Propina $ - Puedes editar
                                                este monto</strong>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="float-group">
                                            <input :readonly="order.id" v-model="form.propina_form" min="0"
                                                step="any" type="number" id="propina_form" name="propina_form"
                                                value="{{ old('propina_form') }}" class="form-control">

                                        </div>

                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="float-group">
                                            <label class="texto-correcto-propina">Propina
                                                equivalente en </label>
                                            <span class="texto-info" id="propina2">Bs. 0,00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-12">
                                        <!-- <span>
                                                <small class="textspan">
                                                    El 100% de la propina va a
                                                    nuestros Zoneros. No es
                                                    obligatorio, solo si es de tu
                                                    agrado.
                                                </small>
                                            </span>-->
                                    </div>
                                    {{-- <div class="col-md-12 col-lg-12 mt-2 justify-content-end">
                                        <button @click="submit" type="button" class="btn btn-success">Continuar con el Proceso de Pago</button>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4 col-xl-4">

                    <div class="checkout-wrapper shadow ">
                        <div class="row justify-content-center mt-2">
                            <div class="col-md-6 col-lg-6">
                                <div class="card mb-3 card-envio" id="card_m_payment1" onclick="method_payment(1)">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="checkbox" class="" value="1"
                                                    name="method_payment" id="method_payment_1">
                                            </div>
                                            <div class="col-md-9 mb-4">
                                                <strong>Pago Único</strong><br>
                                                <span>
                                                    <small class="textspan">
                                                        Seleccione esta opción si tu pago es por un solo método de pago
                                                    </small>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="card mb-3 card-envio" id="card_m_payment2" onclick="method_payment(2)">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="checkbox" class="" value="2"
                                                    name="method_payment" id="method_payment_2">
                                            </div>
                                            <div class="col-md-9">
                                                <strong>Multi Pagos</strong><br>
                                                <span>
                                                    <small class="textspan">
                                                        Seleccione esta opción si son varios métodos de pago (hasta 2
                                                        métodos)
                                                    </small>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row row-total">
                            <div class="col-md-12 col-lg-12" v-if="form.method_payment == 1">
                                <div class="card mb-3 card-envio">
                                    <div class="card-body">
                                        <div class="row mt-2">
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p class="bold">Total a Pagar</p>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p id="montoUSD">{{ $general->cur_sym }} @{{ total }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p id="montoBS">Bs. @{{ totalbs }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12" v-else>
                                <div class="card mb-3 card-envio">
                                    <div class="card-body">
                                        <div class="row mt-2">
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p class="bold">
                                                        Total a Pagar
                                                        <span>
                                                            <small class="textspan">
                                                                Bs.@{{ totalbs }} /
                                                                {{ $general->cur_sym }}@{{ total }}
                                                            </small class="textspan">
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p class="bold">Pagado
                                                        <span>
                                                            <small class="textspan bold">
                                                                @{{ totalPagado() }}
                                                            </small class="textspan">
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="container-total">
                                                    <p class="bold">Pendiente
                                                        <span>
                                                            <small class="textspan text-danger bold">
                                                                @{{ totalPendiente() }}
                                                            </small class="textspan">
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p><span><small class="textspan text-primary ">
                                            <!--PARA PAGOS MULTIMUNEDAS-->

                                            INGRESE PRIMERO EL PAGO EN DIVISAS Y LUEGO EN BOLIVARES
                                            <!--, ingrese PRIMERO el
                                                Pago en Divisas y por último en Bs-->
                                        </small></span> </p>
                                <p align="center" v-if="!depositReturn.id">
                                    <small style="font-weight: 900;font-size: 15px;" class="textspan text-danger text-center">INGRESE EL PRIMER METODO
                                        DE PAGO</small></p>
                                <p align="center" v-else>
                                    <small style="font-weight: 900;font-size: 15px;" class="textspan text-danger">INGRESE EL SEGUNDO METODO DE PAGO</small>
                                </p>
                                <!--<p><small class="textspan text-info mb-2">ALGUNOS MÉTODOS DE PAGOS NO SE ENCUENTRAN
                                            DISPONIBLES CON MULTIPAGO</small></p>-->
                            </div>

                            <div class="col-md-12 col-lg-12">
                                <div class="row mt-2">

                                    @foreach ($gateways as $item)
                                        <div class="col-3">
                                            <label id="btngateway{{ $item->id }}"
                                                class="gateway-item cursor btnFormProp text-center" data-amount="0"
                                                onclick="gatewayCurrency({{ $item->id }})"
                                                @click="gatewayCur({{ $item->id }})">
                                                <img class="gateway-img" src="{{ $item->methodImage() }}"
                                                    alt="@lang('gateway-image')">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div id="element-33" class="col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio" id="" onclick="">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Efectivo</h6>

                                                <div class="row">
                                                    <div class="col-md-6 col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="effective_amount"
                                                                class="form-label texto-correcto">Monto
                                                                {{ $general->cur_sym }}:</label>
                                                            <input v-model="formcash.totaldollar"
                                                                :readonly="blockInput == 0" type="text"
                                                                class="form-control" id="effective_amount"
                                                                name="effective_amount" aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="notecash"
                                                                class="form-label texto-correcto">Nota:</label>
                                                            <textarea class="form-control" id="notacash" rows="1" name="notecash" v-model="formcash.nota"></textarea>
                                                            <div class="form-text textspan"><small>Ingresa
                                                                    el monto del billete con el que pagaras, se aplicará
                                                                    redondeo en compras con decimales</small></div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-31" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Pago Movil</h6>
                                                <div class="row">
                                                    <div class="col-3" v-for="(item,index) in mobileList"
                                                        :key="index">
                                                        <label @click="selectBanck(item.id)"
                                                            :class="form.bank_id == item.id ? 'gateway-item-selecc' :
                                                                'gateway-item'"
                                                            class="cursor btnFormProp text-center" data-amount="0"
                                                            onclick="">
                                                            <img class="gateway-img" :src="item.img"
                                                                alt="">
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row" v-if="this.bank.id > 0">
                                                    <div class="col-md-12 col-lg-12">
                                                        <span><small><b>Nombre:</b> @{{ this.bank.name }} </small></span>
                                                        <span><small><b>Banco:</b> @{{ this.bank.name_bank }}
                                                            </small></span><br>
                                                        <span><small><b>RIF:</b> @{{ this.bank.rif }} </small></span>
                                                        <span><small><b>Teléfono:</b> @{{ this.bank.phone }}
                                                            </small></span>
                                                    </div>
                                                </div>
                                                <div class="row" v-else>
                                                    <div class="col-md-12 col-lg-12">
                                                        <div class="alert alert-info" role="alert">
                                                            Debes seleccionar un Banco.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="exampleInputEmail1"
                                                                class="form-label texto-correcto">Información de
                                                                Referencia:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" name="referencia" id="referencia"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montopagomobil"
                                                                class="form-label texto-correcto">Monto Bs.:</label>
                                                            <input v-model="formcash.totalbs" type="text"
                                                                class="form-control" id="montopagomobil"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-24" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Paypal</h6>
                                                <div class="row">
                                                    <div class="col-md-12 col-lg-12">
                                                        <a href="https://paypal.me/alfogolarexpress?country.x=VE&locale.x=es_XC"
                                                            target="_blank"><img
                                                                src="{{ asset('assets/images/gateway/paguepaypal.png') }}"
                                                                alt="Paypal" class="img-prin img-fluid paypalpag"
                                                                width="400">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="nombre"
                                                                class="form-label texto-correcto">Titular
                                                                de la
                                                                cuenta:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" id="nombretitularpaypal"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montopagomobil"
                                                                class="form-label texto-correcto">Monto
                                                                {{ $general->cur_sym }}:</label>
                                                            <input v-model="formcash.totaldollar" type="text"
                                                                class="form-control" id="montopaypal"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-32" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio" id="" onclick="">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Tranferencia Bancaria</h6>
                                                <div class="row">
                                                    <div class="col-3" v-for="(item,index) in accountList"
                                                        :key="index">
                                                        <label @click="selectBanck(item.id)"
                                                            :class="form.bank_id == item.id ? 'gateway-item-selecc' :
                                                                'gateway-item'"
                                                            class="cursor btnFormProp text-center" data-amount="0"
                                                            onclick="">
                                                            <img class="gateway-img" :src="item.img"
                                                                alt="">
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row" v-if="this.bank.id > 0">
                                                    <div class="col-md-12 col-lg-12">
                                                        <span><small class="textspan"><b>Nombre:</b>
                                                                @{{ this.bank.name }} </small></span>
                                                        <span><small class="textspan"><b>Banco:</b>
                                                                @{{ this.bank.name_bank }} </small></span><br>
                                                        <span><small class="textspan"><b>RIF:</b>
                                                                @{{ this.bank.rif }}</small></span>
                                                        <span><small class="textspan"><b>Nro. de
                                                                    cuenta:</b> @{{ this.bank.account }}</small></span>
                                                    </div>
                                                </div>
                                                <div class="row" v-else>
                                                    <div class="col-md-12 col-lg-12">
                                                        <div class="alert alert-info" role="alert">
                                                            Debes seleccionar un Banco.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="referenciatrans"
                                                                class="form-label texto-correcto">Información de
                                                                Referencia:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" id="referenciatrans"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montotransferencia"
                                                                class="form-label texto-correcto">Monto Bs.:</label>
                                                            <input v-model="formcash.totalbs" type="text"
                                                                class="form-control" id="montotransferencia"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-35" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio" id="" onclick="">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Binance</h6>
                                                <div class="row">
                                                    <div class="col-md-6 col-lg-6">
                                                        <div class="item-img">
                                                            <img src="{{ asset('assets/images/gateway/qrbinance.png') }}"
                                                                alt="Binance" class="img-prin img-fluid qrbinance"
                                                                width="400">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-6">

                                                        <p class="textspan">
                                                            <strong>RED: </strong>
                                                            <span class="texto-correcto">Tron (TRC20)
                                                            </span>
                                                        </p>
                                                        <p class="textspan">
                                                            <strong>ID de Pago: </strong>
                                                            <span class="texto-correcto">455645231
                                                            </span>
                                                        </p>
                                                        <p class="textspan">

                                                            <small class="textspan">
                                                                **Los pagos seran aceptados unicamente en Binance
                                                            </small>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="referenzelle"
                                                                class="form-label texto-correcto">Nombre:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" id="referenzelle"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montozelle"
                                                                class="form-label texto-correcto">Monto
                                                                {{ $general->cur_sym }}:</label>
                                                            <input v-model="formcash.totaldollar" type="text"
                                                                class="form-control" id="montozelle"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-36" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio" id="" onclick="">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Reserve</h6>
                                                <div class="row">
                                                    <div class="col-md-6 col-lg-6">
                                                        <div class="item-img">
                                                            <img src="{{ asset('assets/images/gateway/qrreserve.png') }}"
                                                                alt="reserve" class="img-prin img-fluid qrbinance"
                                                                width="400">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-6">
                                                        <span class='textfontd'><small class="textspan">Envía tu pago al
                                                                usuario</small> ALFOGOLAREXPRESS</span>
                                                        <p>
                                                            <strong>Correo: </strong>
                                                            <span class="texto-correcto">alfogolarexpress@gmail.com
                                                            </span><br>
                                                            <small class="textspan">Proporciona el usuario emisor del
                                                                pago</small><br>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="referenzelle"
                                                                class="form-label texto-correcto">Nombre:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" id="referenzelle"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montozelle"
                                                                class="form-label texto-correcto">Monto
                                                                {{ $general->cur_sym }}:</label>
                                                            <input v-model="formcash.totaldollar" type="text"
                                                                class="form-control" id="montozelle"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="element-34" class="col-md-12 col-lg-12" style="display: none;">
                                <div class="row">
                                    <div class="col">
                                        <div class="card mb-3 card-envio" id="" onclick="">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mt-1 mb-2 text-muted">Zelle</h6>
                                                <div class="row">
                                                    <div class="col-md-12 col-lg-12">
                                                        <span><small class="textspan">Antes de hacer el pago, Por
                                                                favor verificar el
                                                                correo.</small></span>
                                                        <p>
                                                            <small class="textspan">Realiza tu pago a:</small><br>
                                                            <strong>Correo: </strong><span
                                                                class="texto-correcto">vdelnegro@gmail.com</span><br>
                                                            <strong>Nombre: </strong>WALTTER DEL NEGRO
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-7 col-lg-7">
                                                        <div class="mb-3">
                                                            <label for="referenzelle"
                                                                class="form-label texto-correcto">Titular de la
                                                                cuenta:</label>
                                                            <input v-model="formcash.referencia" type="text"
                                                                class="form-control" id="referenzelle"
                                                                aria-describedby="emailHelp">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-5">
                                                        <div class="mb-3">
                                                            <label for="montozelle"
                                                                class="form-label texto-correcto">Monto
                                                                {{ $general->cur_sym }}:</label>
                                                            <input v-model="formcash.totaldollar" type="text"
                                                                class="form-control" id="montozelle"
                                                                aria-describedby="emailHelp" :readonly="blockInput == 0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-12">
                                <div class="alert alert-success" role="alert" v-if="message">
                                    <small>@{{ message }}</small>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button v-if="!disguise" @click="submit" type="button"
                                        class="btn btn-success btn-sm">Registrar
                                        Pago</button>



                                    <div class="text-center container-spinner" v-if="loading">
                                        <i class="fas fa-sync fa-spin"></i> Registrando el Pago
                                    </div>
                                </div>

                                <div v-if="continuet" class="alert alert-primary mt-2" role="alert">
                                    <small>Transaction realizada, continue con la siguiente</small>
                                </div>
                                {{-- <div class="bottom-right">
                                    <button @click="submit" type="button" class="btn btn-success">Registrar Pago</button>
                                </div> --}}
                            </div>
                        </div>


                    </div>
                    <button @click="finalizePurchase" :disabled="validated == 1" type="button"
                        class="btn btn-success btn-lg btn-block mt-2">Finalizar Compra</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="shippinHidden">
    @include('modals.modalAddDirection')
    <!-- Checkout Section Ends Here -->
    <style type="text/css">
        .textfontd {
            font-size: 15px;
        }

        .qrbinance {
            width: 100% !important;
        }

        .paypalpag {
            width: 80% !important;
        }


        .bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
        }

        .border-select {
            border: 1px solid #e77e46;
        }

        .container-total {
            padding: 10px;
            text-align: center;
            padding-bottom: 15px;
        }

        .row-total,
        .row-total .col-4 {
            padding: 0px;
            margin: 0px;
        }

        .gateway-img {
            width: 50% !important;
        }

        .ex1 {
            height: 9.3em;
            line-height: 1em;
            overflow-x: hidden;
            overflow-y: auto;
            width: 100%;
        }

        .check-radio-addres {
            height: 30px !important;
        }

        [type="checkbox"] {
            height: 20px;
            margin-top: 10px;
        }

        .item-prod .item-final {
            padding: 0px 5px 5px 5px;
        }

        .billing-label {
            font-size: 12px;
            color: #efa46d;
            font-weight: 900;
        }

        .payment-details {
            position: sticky;
            top: 50px;
            padding: 0px;
            background: #ffffff;
            border: 1px solid #e5e5e5;
        }

        .textlabel {
            display: initial;
            margin-bottom: .5rem;
        }

        .linkadd {
            font-size: 12px
        }

        .item-prod .item-descp p {
            line-height: 2rem !important;
        }

        .producto-categ {
            /*margin-bottom: 0.5rem;*/
            line-height: 0rem;
            font-size: 12px;
        }

        .check-radio {
            width: 7% !important;
            height: 19px !important;
        }

        .check-radio-time {
            width: 15px !important;
            height: 19px !important;
        }

        .checkbox-shipping {
            width: 30px;
            margin: 20px;
        }

        .card {
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .textspan {
            display: inline-block;
            line-height: 1.5 !important;
        }

        .card-body {
            padding: 7px;
        }

        .accordion-button {
            background-color: #ffffff;
            font-size: 20px !important;
            width: 100%;
        }

        .checkout-wrapper {
            padding: 10px;
            border-radius: 10px;
        }

        #methodShipping {
            /*padding: 20px;*/
        }

        .btn-shipping {
            width: 100%;
            background-color: #009aff;
            color: white !important;
            border-radius: 10px;
        }

        .accordion-item {
            border-style: rgb(0 0 0 / 75%) !important;
            border-style: none none solid none;
            border-color: rgb(0 0 0 / 10%);
        }

        .texto-correcto {
            color: darkorange;
            font-size: 14px;
        }

        .texto-correcto-propina {
            color: darkorange;
            font-size: 10px;
        }



        .hora-item {

            text-align: center;
            color: white;
            display: block;
            min-width: 66px;
            padding: 1px 5px;
            background-color: #ff900c;
            margin-left: 13px;
            border-radius: 5px;
            font-size: 14px;

        }

        .gateway-item {
            text-align: center;
            color: #7e8081;
            display: block;
            min-width: 66px;
            /* padding: 1px 16px;*/
            margin-left: 2px;
            border: 1px solid #dfdddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .gateway-item-selecc {
            text-align: center;
            color: #7e8081;
            display: block;
            min-width: 66px;
            /* padding: 1px 16px;*/
            margin-left: 2px;
            border: 1px solid #ff900c;
            border-radius: 5px;
            font-size: 14px;
        }

        .gateway-item:hover {
            border: 1px solid #e77e46;
        }

        .container-spinner {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .item-prod .item-descp .item-nomb-1 {
            font-family: "futura-medium-bt", Helvetica, Arial, Lucida, sans-serif;
            font-size: 0.8rem;
            font-weight: 0 !important;
        }

        .delete-addres {
            cursor: pointer;
        }
    </style>
@endsection

@push('script')
    <script>
        //alert("La resolución de tu pantalla es: " + screen.width + " x " + screen.height)
        let pay = new Vue({
            el: '#accordion-payment',
            data: {
                blockInput: 0,
                validated: 1,
                message: '',
                loading: false,
                disguise: false,
                continuet: false,
                totalbspago: 0,
                totalpago: 0,
                totalbspendiente: 0,
                totalpendiente: 0,
                show_item: false,
                loading: false,
                order_number: '',
                shipping_type: '',
                total: 0,
                totalbs: 0,
                subtotal: 0,
                afterCouponAmount: 0,
                tasa: '{{ $tasa }}',
                gateways: @json($gateways),
                usershipping: @json($usershipping), //'{!! json_encode($usershipping) !!}',
                formcash: {
                    totaldollar: 0,
                    totalbs: 0,
                    nota: 0,
                    referencia: ""
                },
                form: {
                    method_entrega: 1,
                    checkbox_shipping: 6,
                    shippingUser: 0,
                    order_time: 0,
                    order_time_horario: 0,
                    propina_form: 0,
                    gateway: 33,
                    subtotal: 0,
                    total: 0,
                    totalbs: 0,
                    fact_names: '{{ auth()->user()->firstname }}',
                    fact_lastname: '{{ auth()->user()->lastname }}',
                    fact_type_dni: '{{ auth()->user()->type_dni }}',
                    fact_dni: '{{ auth()->user()->dni }}',
                    fact_mobile: '{{ auth()->user()->mobile }}',
                    fact_address: '{{ auth()->user()->direction }}',
                    coupon_amount: null,
                    id_coupon: null,
                    shippingChargeM: 0,
                    bank_id: 0,
                    method_payment: 1
                },
                secondPayment: {
                    totaldollar: 0,
                    totalbs: 0,
                },
                error: [],
                order: [],
                deposit: {},
                depositReturn: [],
                datagatewayCur: {},
                accountList: [],
                mobileList: [],
                accountNumber: [{
                    id: 1,
                    name: "FRIULCO C.A",
                    id_bank: 1,
                    name_bank: "BANCO NACIONAL DE CREDITO",
                    rif: "J-412820249",
                    type: 1,
                    phone: "",
                    account: "0191-0097-96-2197125411",
                    img: "{{ getImage('assets/images/cash/bnc.png') }}"
                }, {
                    id: 2,
                    name: "FRIULCO C.A",
                    id_bank: 2,
                    name_bank: "BANCO PROVINCIAL",
                    rif: "J-412820249",
                    type: 1,
                    phone: "",
                    account: "0108-0082-0501-00442520",
                    img: "{{ getImage('assets/images/cash/provincial.png') }}"
                }, {
                    id: 3,
                    name: "FRIULCO C.A",
                    id_bank: 1,
                    name_bank: "BANCO NACIONAL DE CREDITO",
                    rif: "J-412820249",
                    type: 2,
                    phone: "0412-4170588",
                    account: "",
                    img: "{{ getImage('assets/images/cash/bnc.png') }}"
                }, {
                    id: 4,
                    name: "FRIULCO C.A",
                    id_bank: 1,
                    name_bank: "BANCO PROVINCIAL",
                    rif: "J-412820249",
                    type: 2,
                    phone: "0414-4151612",
                    account: "",
                    img: "{{ getImage('assets/images/cash/provincial.png') }}"
                }],
                bank: {},
                detail: {}
            },
            mounted: function() {
                this.form.order_time = this.daydate();
                const account = this.accountNumber.filter(i => i.type == 1);
                this.accountList = account
                this.mobileList = this.accountNumber.filter(i => i.type == 2);
                this.gatewayCur(33);
                //console.log(this.gateways);
            },
            computed: {

            },
            methods: {
                deleteShippingUser: function(id) {
                    /* axios.get('{{ route('user.delete_shipping_user') }}', {
                             id: id,
                         })
                         .then(function(res) {
                             console.log(res);
                         })
                         .catch(function(err) {
                             console.log(err);
                         })*/

                    axios.get('{{ route('user.delete_shipping_user') }}', {
                            params: {
                                id: id
                            }
                        }).then(function(res) {
                            //console.log(res.data.shipping);
                            pay.usershipping = res.data.shipping
                        })
                        .catch(function(err) {
                            console.log(err);
                        });
                    //  console.log(res);
                    //  pay.usershipping = res.data.shipping

                },
                newDeposit: function() {

                    this.deposit = {
                        user_id: this.order.user_id,
                        method_code: this.datagatewayCur.method_code,
                        order_id: this.order.id,
                        method_currency: this.datagatewayCur.currency,
                        amount: this.datagatewayCur.currency == 'USD$' ? this.formcash.totaldollar : this
                            .datagatewayCur.currency == 'USD' ? this.formcash.totaldollar : this.formcash
                            .totalbs,
                        charge: 0,
                        rate: this.tasa,
                        final_amo: this.datagatewayCur.currency == 'USD$' ? this.formcash.totaldollar : this
                            .datagatewayCur.currency == 'USD' ? this.formcash.totaldollar : this.formcash
                            .totalbs,
                        detail: this.setDetail(this.datagatewayCur.gateways),
                        btc_amo: 0,
                        btc_wallet: "",
                        trx: this.order.order_number,
                        try: 0,
                        status: 2
                    }

                    //console.log(this.deposit);
                    // axios.post('{{ route('user.deposit.new') }}')

                    axios.post('{{ route('user.deposit.new') }}', this.deposit)
                        .then(function(res) {
                            pay.depositReturn = res.data.depost
                            pay.paymentCompleted();
                            pay.formcash.referencia = ''
                        })
                        .catch(function(err) {
                            console.log(err);
                        })
                },
                finalizePurchase: function() {
                    axios.get('{{ route('user.deposit.reduce') }}')
                        .then(function(res) {
                            //console.log(res.data);
                            window.location.href = "{{ route('user.deposit.history') }}"
                        })
                        .catch(function(err) {
                            console.log(err);
                        })
                },
                paymentCompleted: function() {
                    //debugger
                    //console.log(this.secondPayment);
                    //console.log(this.total,this.totalbs);
                    var totalPago = 0

                    if (this.form.method_payment == 1) { //Pago Unico
                        var totalPago = this.depositReturn.method_currency == 'USD$' ? this.total : this
                            .depositReturn.method_currency == 'USD' ? this.total : this.totalbs
                    } else {
                        this.totalpago = this.depositReturn.final_amo
                        this.fillValueField();

                        var totalPago = this.depositReturn.method_currency == 'USD$' ? this.secondPayment
                            .totaldollar : this
                            .depositReturn.method_currency == 'USD' ? this.secondPayment.totaldollar : this
                            .secondPayment.totalbs
                    }

                    if (this.depositReturn.final_amo == totalPago) {
                        pay.loading = false;
                        pay.validated = 0
                        this.continuet = false
                        this.message = "Pago registrado con exitos. Debe de Finalizar la Compra."
                        this.totalpago = 0
                        this.totalbs = 0
                        this.total = 0

                        // console.log("Debe de finalizar la compra");
                    } else {
                        this.totalpago = this.depositReturn.final_amo
                        this.continuet = true
                        this.loading = false
                        this.disguise = false
                        /*this.formcash.totalbs = 0
                        this.formcash.totaldollar = 60*/
                        this.fillValueField();

                    }
                },
                gatewayCur: function(item) {
                    const typePayment = this.gateways.find(function(m) {
                        return m.id == item
                    })

                    this.datagatewayCur = {
                        id: typePayment.currencies[0].id,
                        currency: typePayment.currencies[0].currency,
                        method_code: typePayment.currencies[0].method_code,
                        gateways: item
                    }

                    this.form.bank_id = 0;
                    this.bank = {}
                    //console.log(this.datagatewayCur);
                },
                setError: function(err) {
                    this.error = []
                    this.error.push(err)
                },
                newOrder: async function() {
                    await axios.post('{{ URL('user/checkout/1') }}', this.form)
                        .then(function(res) {
                            if (res.data.error) {
                                pay.setError(res.data.error)
                            } else {
                                pay.order = res.data.order;
                            }
                        })
                        .catch(function(err) {
                            console.log(err);
                        })
                },
                submit: function() {
                    this.validateform();
                    if (this.error.length == 0) {
                        pay.loading = true;
                        pay.disguise = true
                        /*Pago Unico*/
                        this.singlePayment();
                    }
                },
                singlePayment: async function() {
                    if (this.form.method_payment == 1) { //Pago Unico
                        /*Valida pago en Efectivo Dolares*/
                        this.validateCash();
                        // console.log(this.error);
                        if (this.error.length == 0) {
                            await this.newOrder();
                            //console.log(this.order);
                            this.newDeposit()
                        } else {
                            pay.loading = false;
                            pay.disguise = false;
                        }
                    } else {
                        this.validateCash();
                       // debugger
                        // console.log(this.error);
                        if (this.error.length == 0) {
                            if (!pay.order.id) {
                                await this.newOrder();
                            }
                            ///  this.fillValueField();
                            this.newDeposit()
                        } else {
                            pay.loading = false;
                            pay.disguise = false;
                        }
                    }
                },
                fillValueField: function() {
                    //debugger
                    //console.log(pay.secondPayment);
                    // pay.formcash.totalbs = montoPendienteBs
                    // pay.formcash.totaldollar = this.totalpago
                    var montoPendienteBs = 0
                    if (this.depositReturn.method_currency == 'BS.F' || this.depositReturn.method_currency == 'BS') {
                        if (!pay.order.id) {
                            var montoPendienteBs = parseFloat(this.totalbs - this.totalpago).toFixed(2);
                            //let montoPendienteBs = parseFloat((!pay.order.id ? this.totalbs : pay.secondPayment.totalbs) - this.totalpago).toFixed(2);
                            let cambioDollar = parseFloat((montoPendienteBs) / parseFloat(this.tasa));
                            let montoPendienteDollar = parseFloat((Math.round(cambioDollar * 100) / 100))
                                .toFixed(2);

                            pay.formcash.totalbs = montoPendienteBs
                            pay.formcash.totaldollar = montoPendienteDollar

                            pay.secondPayment.totaldollar = montoPendienteDollar
                            pay.secondPayment.totalbs = montoPendienteBs

                        } else {
                            var montoPendienteBs = parseFloat(pay.secondPayment.totalbs - this.totalpago)
                                .toFixed(2);
                            //let montoPendienteBs = parseFloat((!pay.order.id ? this.totalbs : pay.secondPayment.totalbs) - this.totalpago).toFixed(2);
                            let cambioDollar = parseFloat((montoPendienteBs) / parseFloat(this.tasa));
                            let montoPendienteDollar = parseFloat((Math.round(cambioDollar * 100) / 100))
                                .toFixed(2);

                            let cambioDollarve = parseFloat((pay.secondPayment.totalbs) / parseFloat(this
                                .tasa));
                            let montoPendienteDollarve = parseFloat((Math.round(cambioDollarve * 100) / 100))
                                .toFixed(2);


                            pay.formcash.totalbs = montoPendienteBs
                            pay.formcash.totaldollar = montoPendienteDollar

                            pay.secondPayment.totaldollar = montoPendienteDollarve
                            pay.secondPayment.totalbs = pay.secondPayment.totalbs

                        }



                    } else {

                        let montoPendienteDollar = parseFloat(this.total - this.totalpago).toFixed(2);
                        let cambioBs = parseFloat((montoPendienteDollar) * parseFloat(this.tasa));
                        let montoPendienteBs = parseFloat((Math.round(cambioBs * 100) / 100)).toFixed(2);

                        pay.formcash.totalbs = montoPendienteBs
                        pay.formcash.totaldollar = montoPendienteDollar

                        pay.secondPayment.totaldollar = montoPendienteDollar
                        pay.secondPayment.totalbs = montoPendienteBs
                    }
                    pay.blockInput = 0;

                },
                verifySecondPayment: function() {

                },
                setDetail: function(val) {
                    // let detail = {}
                    if (val == 33) {
                        this.detail = {
                            ingrese_el_valor_de_billete_en_caso_de_tener_que_dar_vuelto: {
                                field_name: this.formcash.nota,
                                type: "text"
                            }
                        }
                    } else {
                        this.detail = {
                            numero_de_referencia: {
                                field_name: this.formcash.referencia,
                                type: "text"
                            }
                        }
                    }
                    //console.log(this.detail);
                    return this.detail;
                },
                validateCash: function() {
                    this.error = []
                    if (this.form.method_payment == 1) { //Pago Unico
                        if (this.form.gateway == 33) { //Efectivo Dolares
                            if (parseFloat(this.formcash.totaldollar) !== parseFloat(this.form.total)) {
                                this.error.push('El monto a pagar es menor que el total de la orden.')
                            }

                            if (parseFloat(this.formcash.nota) < parseFloat(this.formcash.totaldollar)) {
                                this.error.push('El monto del billete no puede ser menor a total a pagar.')
                            }

                        }

                        if (this.form.gateway == 31) { //Pago Mobil
                            if (parseFloat(this.formcash.totalbs) !== parseFloat(this.form.totalbs)) {
                                this.error.push('El monto a pagar es menor que el total de la orden.')
                            }

                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar la referencia del pago.')
                            }

                            if (!this.form.bank_id) {
                                this.error.push('Debe seleccionar un Banco.')
                            }
                        }

                        if (this.form.gateway == 24) { //PayPal
                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar el titular de la cuenta.')
                            }
                        }

                        if (this.form.gateway == 32) { //Transferencia
                            if (parseFloat(this.formcash.totalbs) !== parseFloat(this.form.totalbs)) {
                                this.error.push('El monto a pagar es menor que el total de la orden.')
                            }

                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar la referencia del pago.')
                            }

                            if (!this.form.bank_id) {
                                this.error.push('Debe seleccionar un Banco.')
                            }
                        }

                        if (this.form.gateway == 34) { //Zelle
                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar la referencia del pago.')
                            }
                        }
                    } else {
                        if (this.form.gateway == 33) { //Efectivo Dolares
                            if (parseFloat(this.formcash.nota) < parseFloat(this.formcash.totaldollar)) {
                                this.error.push('El monto del billete no puede ser menor a total a pagar.')
                            }

                            if (parseFloat(this.formcash.totaldollar) > parseFloat(pay.total)) {
                                this.error.push('El monto a depositar no puede ser mayor al total del Pedido.')
                            }
                        }

                        if (this.form.gateway == 31) { //Pago Mobil
                            /*if (parseFloat(this.formcash.totalbs) !== parseFloat(this.form.totalbs)) {
                                this.error.push('El monto a pagar es menor que el total de la orden.')
                            }*/

                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar la referencia del pago.')
                            }

                            if (!this.form.bank_id) {
                                this.error.push('Debe seleccionar un Banco.')
                            }

                            if (parseFloat(this.formcash.totalbs) > parseFloat(pay.totalbs)) {
                                this.error.push('El monto a depositar no puede ser mayor al total del Pedido.')
                            }
                        }

                        if (this.form.gateway == 24) { //PayPal
                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar el titular de la cuenta.')
                            }
                        }

                        if (this.form.gateway == 32) { //Transferencia
                            /* if (parseFloat(this.formcash.totalbs) !== parseFloat(this.form.totalbs)) {
                                 this.error.push('El monto a pagar es menor que el total de la orden.')
                             }*/

                            if (this.formcash.referencia == '') {
                                this.error.push('Debe colocar la referencia del pago.')
                            }

                            if (!this.form.bank_id) {
                                this.error.push('Debe seleccionar un Banco.')
                            }

                            if (parseFloat(this.formcash.totalbs) > parseFloat(pay.totalbs)) {
                                this.error.push('El monto a depositar no puede ser mayor al total del Pedido.')
                            }
                        }
                    }
                },
                methodEntrega: function(id) {
                    this.form.method_entrega = id
                    this.form.checkbox_shipping = 3
                },
                shipping_address: function(val) {
                    const addres = JSON.parse(val);
                    return addres.address + ' - ' + addres.state + ' - ' + addres.city + ' - ' + addres.zip +
                        ' - ' + addres.country;
                },
                daydate: function() {
                    let date = new Date()
                    let day = `${(date.getDate())}`.padStart(2, '0');
                    let month = `${(date.getMonth()+1)}`.padStart(2, '0');
                    let year = date.getFullYear();
                    let formatted_date = year + "-" + month + "-" + day
                    return formatted_date;
                },
                selectBanck: function(val) {
                    this.bank = this.accountNumber.find(function(m) {
                        return m.id == val
                    })
                    this.form.bank_id = this.bank.id;;
                },
                totalPendiente: function() {
                    //debugger
                    if (this.depositReturn.method_currency == 'BS.F' || this.depositReturn.method_currency == 'BS') {
                        const montoPendienteBs = parseFloat(this.totalbs - this.totalpago).toFixed(2);
                        const cambioDollar = parseFloat((montoPendienteBs) / parseFloat(this.tasa));
                        const montoPendienteDollar = parseFloat((Math.round(cambioDollar * 100) / 100)).toFixed(
                            2);
                        return `Bs.${montoPendienteBs} / $.${montoPendienteDollar}`
                    } else {
                        const montoPendienteDollar = parseFloat(this.total - this.totalpago).toFixed(2);
                        const cambioBs = parseFloat((montoPendienteDollar) * parseFloat(this.tasa));
                        const montoPendienteBs = parseFloat((Math.round(cambioBs * 100) / 100)).toFixed(2);
                        return `Bs.${montoPendienteBs} / $.${montoPendienteDollar}`
                    }
                },
                totalPagado: function() {                    
                    // debugger
                    if (this.depositReturn.method_currency == 'BS.F' || this.depositReturn.method_currency == 'BS') {
                        const montoPendienteBs = parseFloat(this.totalpago).toFixed(2);
                        const cambioDollar = parseFloat((montoPendienteBs) / parseFloat(this.tasa));
                        const montoPendienteDollar = parseFloat((Math.round(cambioDollar * 100) / 100)).toFixed(
                            2);
                        return `Bs.${montoPendienteBs} / $.${montoPendienteDollar}`
                    } else {

                        const montoPendienteDollar = parseFloat(this.totalpago).toFixed(2);
                        const cambioBs = parseFloat((montoPendienteDollar) * parseFloat(this.tasa));
                        const montoPendienteBs = parseFloat((Math.round(cambioBs * 100) / 100)).toFixed(2);
                        return `Bs.${montoPendienteBs} / $.${montoPendienteDollar}`
                    }
                },
                validateform: function() {
                    //this.error = "";
                    this.error.length = 0;

                    if (this.form.method_entrega == 1) {
                        if (this.form.shippingUser == 0) {
                            this.error.push('Debe selecionar una Dirección de Envío.')
                        }
                    }

                    if (this.form.method_entrega == 2) {
                        if (this.form.checkbox_shipping == 0) {
                            this.error.push('Debe selecionar una Dirección de Envío.')
                        }
                    }

                    if (this.form.order_time == 0) {
                        this.error.push('Debe selecionar el tipo de envío a usar.')
                    }

                    if (this.form.order_time_horario == 0) {
                        this.error.push('Debe selecionar un horario de envío.')
                    }

                    /*if (this.form.method_payment == 2) {
                        
                    }*/
                },
                tipPrice: function(mount) {
                    if (!pay.order.id) {
                        const val = $('#shippingCharge2').val();
                        $('#propina_form').val(mount);
                        pay.form.propina_form = mount
                        let total = 0;
                        const charge = parseFloat(val);
                        const sub_total = parseFloat('{{ $subtotal }}');
                        const propina = mount;
                        let afterCouponAmount = (sub_total - couponAmount).toFixed(2);

                        if (isNaN(charge)) {
                            charge = 0;
                        }

                        if (isNaN(coupon_amount)) {
                            coupon_amount = 0;
                        }

                        if (isNaN(propina)) {
                            propina = 0;
                        }

                        total = (parseFloat(afterCouponAmount) + (charge + propina + iva) - coupon_amount);
                        total = (Math.round(total * 100) / 100).toFixed(2);
                        // console.log(total);

                        $('#propina').html(`{{ $general->cur_sym }}` + mount);
                        $('#propina2').html('Bs. ' + parseFloat(mount * rate).toFixed(2));
                        $('#shippingCharge').html(`{{ $general->cur_sym }}` + parseFloat(val).toFixed(2));

                        pay.total = parseFloat(total).toFixed(2)
                        pay.form.total = pay.total
                        pay.totalbs = parseFloat(total * rate).toFixed(2)
                        pay.form.totalbs = pay.totalbs

                        /*Totaliza la Propina mediante el metodo de pago*/
                        if (pay.form.method_payment == 1) {
                            pay.formcash.totalbs = pay.totalbs
                            pay.formcash.totaldollar = pay.total
                        } else {

                            //pay.formcash.totalbs = pay.totalbs
                            //pay.formcash.totaldollar = pay.total
                            //}else{
                            // this.tipSecondPayment(mount)

                        }


                        return total;
                    }
                },
                tipSecondPayment: function(mount) {

                    const val = $('#shippingCharge2').val();
                    $('#propina_form').val(mount);
                    pay.form.propina_form = mount
                    let total = 0;
                    const charge = parseFloat(val);
                    const sub_total = parseFloat(pay.secondPayment.totaldollar);
                    const propina = mount;
                    let afterCouponAmount = (sub_total).toFixed(2);

                    if (isNaN(charge)) {
                        charge = 0;
                    }

                    if (isNaN(coupon_amount)) {
                        coupon_amount = 0;
                    }

                    if (isNaN(propina)) {
                        propina = 0;
                    }

                    total = (parseFloat(afterCouponAmount) + (propina));
                    total = (Math.round(total * 100) / 100).toFixed(2);
                    // console.log(total);

                    /*pay.total = parseFloat(total).toFixed(2)
                    pay.form.total = pay.total
                    pay.totalbs = parseFloat(total * rate).toFixed(2)
                    pay.form.totalbs = pay.totalbs*/

                    pay.formcash.totalbs = pay.secondPayment.totalbs
                    pay.formcash.totaldollar = pay.secondPayment.totaldollar
                }
            },
        });

        const moneda = ('{{ $moneda }}');
        const rate = '{{ $tasa }}';
        const iva = parseFloat('{{ $iva }}');
        const coupon_amount = 0;
        const couponAmount =
            @if (session()->has('coupon'))
                {{ session('coupon')['amount'] }}
            @else
                0
            @endif ;
        var selecGateway = 33;
        const shipping = 0;
        const charge = $('#shippingCharge2').val();
        const sub_total = parseFloat({{ $subtotal }});
        pay.subtotal = sub_total;
        pay.form.subtotal = pay.subtotal
        let afterCouponAmount = 0;

        const ButtomPropina = (mount) => {
            //  debugger
            /*const val = $('#shippingCharge2').val();
            $('#propina_form').val(mount);

            pay.form.propina_form = mount

            let total = 0;
            const charge = parseFloat(val);
            const sub_total = parseFloat('{{ $subtotal }}');
            const propina = parseFloat($('#propina_form').val());
            let afterCouponAmount = (sub_total - couponAmount).toFixed(2);

            if (isNaN(charge)) {
                charge = 0;
            }

            if (isNaN(coupon_amount)) {
                coupon_amount = 0;
            }

            if (isNaN(propina)) {
                propina = 0;
            }

            total = (parseFloat(afterCouponAmount) + (charge + propina + iva) - coupon_amount);
            total = (Math.round(total * 100) / 100).toFixed(2);

            $('#propina').html(`{{ $general->cur_sym }}` + mount);
            $('#propina2').html('Bs. ' + parseFloat(mount * rate).toFixed(2));
            $('#shippingCharge').html(`{{ $general->cur_sym }}` + parseFloat(val).toFixed(2));

            pay.total = parseFloat(total).toFixed(2)
            pay.form.total = pay.total
            pay.totalbs = parseFloat(total * rate).toFixed(2)
            pay.form.totalbs = pay.totalbs            
            pay.formcash.totalbs = pay.totalbs
            pay.formcash.totaldollar = pay.total
            return total;*/
        }

        const gatewayCurrency = (gateway) => {

            $('#element-' + selecGateway).fadeOut('fast');
            $('#element-' + gateway).fadeIn('fast')
            $('#btngateway' + gateway).css('border', '1px solid #e77e46');
            $('#btngateway' + selecGateway).css('border', '1px solid #dfdddd');
            selecGateway = gateway;
            pay.form.gateway = gateway;
        }

        const dayDate = () => {
            let date = new Date()
            let day = `${(date.getDate())}`.padStart(2, '0');
            let month = `${(date.getMonth()+1)}`.padStart(2, '0');
            let year = date.getFullYear();
            let formatted_date = year + "-" + month + "-" + day
            return formatted_date;
        }

        function method_payment(opcion) {
            if (opcion == 1) {
                $('#card_m_payment1').css('background-color', '#ff6900').css('color', 'white');
                $('#card_m_payment2').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                $('#method_payment_1').prop('checked', true);
                $('#method_payment_2').prop('checked', false);

                pay.form.method_payment = 1
                pay.blockInput = 0;
                pay.formcash.totaldollar = pay.total
                pay.formcash.totalbs = pay.totalbs


            } else {

                $('#card_m_payment2').css('background-color', '#ff6900').css('color', 'white');
                $('#card_m_payment1').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                $('#method_payment_1').prop('checked', false);
                $('#method_payment_2').prop('checked', true);

                pay.form.method_payment = 2
                pay.formcash.totalbs = 0
                pay.blockInput = 1;
                pay.formcash.totaldollar = 0
            }
        }

        function method_entrega(opcion) {
            if (opcion == 1) {
                $('#card_m_envio1').css('background-color', '#ff6900').css('color', 'white');
                $('#card_m_envio2').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                $('#method_entrega_1').prop('checked', true);
                $('#method_entrega_2').prop('checked', false);

                $('#ViewDirection').fadeIn('fast');
                $('#ViewPickup').fadeOut('fast');

                $("input[name=order_time][value='" + dayDate() + "']").prop("checked", true);
                pay.form.checkbox_shipping = 6;
                pay.form.shippingUser = 0;
                //pay.form.order_time = dayDate();

            } else {
                $('#card_m_envio2').css('background-color', '#ff6900').css('color', 'white');
                $('#card_m_envio1').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                $('#method_entrega_2').prop('checked', true);
                $('#method_entrega_1').prop('checked', false);

                $('#ViewDirection').fadeOut('fast');
                $('#ViewPickup').fadeIn('fast');

                $("input[name=order_time][value='" + dayDate() + "']").prop("checked", true);
                pay.form.checkbox_shipping = 0;
                pay.form.shippingUser = 0;
            }

            pay.form.order_time = dayDate();
            /*  $('.checkbox_shipping').prop('checked', false);
              $('.card-envio').removeClass('border border-success');
              $('.card-method-s').removeClass('border border-success');
              $('.checkbox-shipping').prop('checked', false);

              if (opcion == 1) {
                  $('#card_m_envio1').css('background-color', '#ff6900').css('color', 'white');
                  $('#card_m_envio2').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                  $('#method_entrega_1').prop('checked', true);
                  $('#method_entrega_2').prop('checked', false);

                  $('#ViewDirection').fadeIn('fast');
                  $("#panel-chipping-bod ").collapse('hide');

                  $('#shippinHidden').val(0); // shipping--;

                  $('#card-method-s-3').hide();
                  $('#card-method-s-4').hide();
                  $('#card-method-s-5').show();
                  $('#card-method-s-6').show();
                  pay.shipping_type = 1;
                  $('#SelectDate').hide();
              } else {
                  $('#card_m_envio2').css('background-color', '#ff6900').css('color', 'white');
                  $('#card_m_envio1').css('background-color', 'rgba(214, 224, 226, 0.2)').css('color', '#7f8081');

                  $('#method_entrega_2').prop('checked', true);
                  $('#method_entrega_1').prop('checked', false);

                  $('#ViewDirection').fadeOut('fast');
                  $("#panel-chipping-body").collapse('show');

                  $('#shippinHidden').val(1); // shipping++;

                  $('#card-method-s-3').show();
                  $('#card-method-s-4').show();
                  $('#card-method-s-5').hide();
                  $('#card-method-s-6').hide();
                  pay.shipping_type = 2;
                  $('#SelectDate').show();
              }*/
        }

        (function($) {
            if (moneda == 'Dolares') {
                $('#cartSubtotal').text(`{{ $general->cur_sym }}` + parseFloat(sub_total).toFixed(2));
                pay.afterCouponAmount = (sub_total - couponAmount).toFixed(2);
                total = parseFloat((sub_total + iva) - couponAmount);
                pay.total = parseFloat((Math.round(total * 100) / 100)).toFixed(2);
                pay.form.total = pay.total
                pay.formcash.totaldollar = pay.total

                const totalbs = parseFloat((pay.total) * parseFloat(rate));
                pay.totalbs = parseFloat((Math.round(totalbs * 100) / 100)).toFixed(2);
                pay.form.totalbs = pay.totalbs
                pay.formcash.totalbs = pay.totalbs
            } else {
                $('#cartSubtotal').text(`Bs ` + parseFloat(sub_total * rate).toFixed(2));
                pay.afterCouponAmount = ((sub_total - couponAmount) * rate).toFixed(2);
                total = parseFloat((sub_total + iva) - couponAmount);
                pay.total = parseFloat((Math.round(total * 100) / 100)).toFixed(2);
                pay.form.total = pay.total
                pay.formcash.totaldollar = pay.total

                const totalbs = parseFloat((pay.total) * rate);
                pay.totalbs = parseFloat((Math.round(total * 100) / 100)).toFixed(2);
                pay.form.totalbs = pay.totalbs
                pay.formcash.totalbs = pay.totalbs
            }

            $('#btngateway' + selecGateway).css('border', '1px solid #e77e46');

            method_entrega(1);

            method_payment(1)

            $(".product-slider-shippin").owlCarousel({
                responsive: {
                    0: {
                        items: 2,
                    },
                    600: {
                        items: 2,
                        nav: false
                    },
                    1000: {
                        items: 3,
                    },
                    1200: {
                        items: 3,
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

            $('#propina_form').on('input', function() {
                ButtomPropina($('#propina_form').val());
            }).change();
        })(jQuery)
    </script>
@endpush

@push('breadcrumb-plugins')
    <li><a href="{{ route('home') }}">@lang('Home')</a></li>
    <li><a href="{{ route('products') }}">@lang('Products')</a></li>
    <li><a href="{{ route('shopping-cart') }}">@lang('Cart')</a></li>
@endpush


@push('meta-tags')
    @include('partials.seo')
@endpush
