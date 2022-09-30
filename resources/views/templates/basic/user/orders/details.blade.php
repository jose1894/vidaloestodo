@extends(activeTemplate() .'layouts.master')

@section('content')
<!-- dashboard-section start -->
<div class="invoice-history-section padding-bottom padding-top">
    <div class="container border">
        <!-- Main content -->
        <div class="invoice" id="invoice">
            <!-- title row -->
            <div class="row mt-3 border-bottom p-3">
                <div class="col-lg-6">
                    <h4><i class="fa fa-globe"></i> {{__($general->sitename)}} </h4>
                </div>
                <div class="col-lg-6 text-right">
                    <b>@lang('Order ID'):</b> {{$order->order_number}}<br>
                    <b>@lang('Order Date'):</b> {{showDateTime($order->created_at, 'd/m/Y')}} <br>
                </div>
            </div>

            <div class="invoice-info mb-3">

            </div><!-- /.row -->
            <!-- Table row -->

            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>@lang('SN.')</th>
                            <th>@lang('Product')</th>
                            <th>@lang('Variants')</th>
                            <th>@lang('Discount')</th>
                            <th>@lang('Quantity')</th>
                            <th>@lang('Price')</th>
                            <th>@lang('Total Price')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @php
                            $subtotal = 0;
                            @endphp
                            @foreach($order->orderDetail as $data)

                            @php
                            $details = json_decode($data->details);
                            $offer_price = $details->offer_amount;
                            $extra_price = 0;
                            @endphp
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$data->product->name}}</td>
                                <td>
                                    @if($details->variants)
                                    @foreach ($details->variants as $item)
                                    <span class="d-block">{{__($item->name)}} :  <b>{{__($item->value)}}</b></span>
                                    @php $extra_price += $item->price;  @endphp
                                    @endforeach
                                    @else
                                    @lang('N/A')
                                    @endif
                                </td>
                                @php $base_price = $data->base_price + $extra_price @endphp
                                <td class="text-right">{{$general->cur_sym.getAmount($offer_price)}}/ @lang('Item')</td>
                                <td class="text-center">{{$data->quantity}}</td>
                                <td class="text-right">{{$general->cur_sym. ($data->base_price - getAmount($offer_price))}}</td>

                                <td class="text-right">{{$general->cur_sym.getAmount(($base_price - $offer_price)*$data->quantity)}}</td>
                                @php $subtotal += ($base_price - $offer_price) * $data->quantity @endphp
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div><!-- /.col -->
            </div><!-- /.row -->

            <div class="row mt-4">
                <!-- accepted payments column -->
                <div class="col-lg-6">
                    @if( isset($order->deposit) )
                        @foreach($order->deposit as $od)
                            @if($od->status != 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="50%">Método de Pago</td>
                                                <td width="50%">
                                                    @if($od->method_code == 0)
                                                    <span data-toggle="tooltip" title="Efectivo">Efectivo
                                                    </span>
                                                    @else
                                                    {{ __($od->gateway->name) }}
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Cargo por pago</td>
                                                <td>{{$general->cur_sym. $charge = getAmount(@$od->charge) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Monto Total del Pago </td>
                                                <td>{{$general->cur_sym. getAmount(($od->amount + $charge)) }}</td>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                        
                    @endif


                </div><!-- /.col -->
                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                @if($discountPrime > 0)
                                    <tr>
                                        <th width="50%">Descuento PRIME</th>
                                        <td width="50%">
                                            {{ $general->cur_sym.getAmount($discountPrime, 2) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th width="50%">@lang('Subtotal')</th>
                                    <td width="50%">{{@$general->cur_sym.getAmount(($order->base_imponible + $order->excento), 2)}}</td>
                                </tr>
                                @if($order->appliedCoupon)
                                <tr>
                                    <th>(<i class="la la-plus"></i>) @lang('Coupon') ({{ $order->appliedCoupon->coupon->coupon_code }})</th>
                                    <td> {{$general->cur_sym.getAmount($order->appliedCoupon->amount, 2)}}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>(<i class="la la-minus"></i>) @lang('Shipping')</th>
                                    <td>{{ @$general->cur_sym.getAmount($order->shipping_charge, 2)}}</td>
                                </tr>
                                @if(!is_null($order->propina))
                                    <tr>
                                        <th>Propina: </th>
                                        <td>{{@$general->cur_sym.($order->propina)}}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th width="50%">Base Imponible</th>
                                    <td width="50%">{{@$general->cur_sym.getAmount($order->base_imponible, 2)}}</td>
                                </tr>
                                <tr>
                                    <th width="50%">Excento</th>
                                    <td width="50%">{{@$general->cur_sym.getAmount($order->excento, 2)}}</td>
                                </tr>
                                <tr>
                                    <th width="50%">IVA</th>
                                    @if($order->iva > 0)
                                        <td width="50%">{{@$general->cur_sym.getAmount(($order->iva), 2)}}</td>
                                    @else 
                                        <td width="50%">{{@$general->cur_sym.getAmount(($order->base_imponible * 0.16), 2)}}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <th>@lang('Total')</th>
                                    <td>{{@$general->cur_sym.($order->total_amount)}}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div><!-- /.col -->



                <div class="col-md-12">
                    <h5 class="mb-2">@lang('Shipping Address')</h5>
                    @php
                        $sa = json_decode($order->shipping_address);
                        if(isset($sa->shipping_address)){
                            $shipping_address = json_decode($sa->shipping_address);
                        }
                        else{
                            $shipping_address_admin = $sa;
                        }
                        
                        $invoice_information = json_decode($order->invoice_information);
                    @endphp

                    <address>
                        @if(isset($shipping_address))
                            <strong>Nombre:</strong> {{ $shipping_address->firstname ?? $order->user->firstname }} {{$shipping_address->lastname ?? $order->user->lastname }},
                            <strong>Teléfono móvil:</strong> {{$shipping_address->mobile??''}}
                            <strong>Dirección:</strong> {{$shipping_address->address}},
                            <strong>Estado:</strong> {{$shipping_address->state}},
                            <strong>Ciudad:</strong> {{$shipping_address->city}},
                            <strong>Código Postal:</strong> {{$shipping_address->zip}},
                            <strong>País:</strong> {{$shipping_address->country}}
                        @else 
                            <strong>Nombre:</strong> {{ $shipping_address_admin->firstname ?? $order->user->firstname }} {{$shipping_address_admin->lastname ?? $order->user->lastname }},
                            <strong>Teléfono móvil:</strong> {{$shipping_address_admin->mobile??''}}
                            <strong>Dirección:</strong> {{$shipping_address_admin->address}}
                        @endif
                    </address>

                    <h4> <strong>Metodo de envío: </strong> {{$order->shipping->name}} </h4>
                    @if(!is_null($order->propina))
                        <h4> <strong>Propina: </strong> {{@$general->cur_sym.($order->propina)}} </h4>
                    @endif                   
                    @if(!is_null($order->order_time))
                        <h4> <strong>Fecha de Entrega: </strong> {{$order->order_time}} </h4>
                        @if(!is_null($order->order_time_horario))
                            <h4> <strong>Turno: </strong> {{$order->order_time_horario==1?'Mañana':''}}{{$order->order_time_horario==2?'Tarde':''}}{{$order->order_time_horario==3?'Noche':''}}</h4>
                        @endif
                    @else
                        <h4> <strong>Tiempo en días: </strong> {{$order->shipping->shipping_time}} </h4>
                        <h4> <strong>Fecha de Entrega: </strong> No se especificó </h4>
                    @endif
                </div><!-- /.col -->
            </div><!-- /.row -->
            <!-- this row will not appear when printing -->
        </div><!-- /.content -->
        <div class="float-right">
            <a href="{{ route('print.invoice', $order->id) }}" target=blank class="btn btn-dark mt-3"><i class="fa fa-print"></i>@lang('Print')</a>
        </div>
    </div>
</div>

@endsection



@push('breadcrumb-plugins')
    <li><a href="{{route('user.home')}}">@lang('Dashboard')</a></li>
    <li><a href="{{route('user.orders', 'all')}}">@lang('Orders')</a></li>
@endpush
