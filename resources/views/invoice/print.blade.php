<!DOCTYPE html>
<html lang="es">
<head>
    <title>{{ $order->order_number }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900' type='text/css'>
<link rel="shortcut icon" href="{{ getImage('assets/images/logoIcon/favicon.png', '128x128') }}" type="image/x-icon">

<link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/fontawesome.all.min.css') }}">
<link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/main.css') }}">
<link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/custom.css') }}">

</head>
<body onload="window.print()">
<!-- Container -->
<div class="container-fluid invoice-container">
  <!-- Header -->

  <div class="container-fluid p-0">
    <div class="card">
        <div class="card-body">
            <!-- Main content -->
            <div class="invoice">
                <!-- title row -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="logo">
                            <img  src="{{ getImage('assets/images/logoIcon/logo.png', '183x54') }}" alt="@lang('logo')">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <b>@lang('Order ID'):</b> {{$order->order_number}}<br>
                        <b>@lang('Order Date'):</b> {{showDateTime($order->created_at, 'd/m/Y')}} <br>
                        <b>@lang('Total Amount'):</b> {{$general->cur_sym.$order->total_amount}}
                    </div>
                </div>
                <hr>
                <div class="row invoice-info">
                    @php
                        $invoice_information = json_decode($order->invoice_information);
                    @endphp
                    <div class="col-md-4">
                        <h5 class="mb-2">Detalles del Usuario</h5>
                        <address>
                            <ul>
                                <li>@lang('Name'): <strong>{{@$order->user->firstname }}</strong> @lang('Last Name'): <strong>{{@$order->user->lastname }}</strong></li>
                                @if(is_null($invoice_information))
                                    <li>@lang('Address'): {{@$order->user->address->address}}</li>
                                @else 
                                    <li>@lang('Address'): {{$invoice_information->address ?? @$order->user->address->address }}</li>
                                    <li>Teléfono móvil: {{$invoice_information->mobile ?? '' }}</li>
                                    <li>CI/RIF: {{$invoice_information->dni ?? '' }}</li>
                                @endif
                                <li>@lang('State'): {{@$order->user->address->state}}</li>
                                <li>@lang('City'): {{@$order->user->address->city}}</li>
                                <li>@lang('Zip'): {{@$order->user->address->zip}}</li>
                                <li>@lang('Country'): {{@$order->user->address->country}}</li>
                            </ul>

                        </address>
                    </div><!-- /.col -->
                    <div class="col-md-4">
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
                            <ul>
                                @if(isset($shipping_address))
                                    <li>@lang('Name'): <strong>{{ $shipping_address->firstname ?? $order->user->firstname }} {{$shipping_address->lastname ??$order->user->lastname }}</strong></li>
                                    <li>Teléfono móvil: {{$shipping_address->mobile??''}}</li>
                                    <li>@lang('Address'): {{$shipping_address->address}}</li>
                                    <li>@lang('State'): {{$shipping_address->state}}</li>
                                    <li>@lang('City'): {{$shipping_address->city}}</li>
                                    <li>@lang('Zip'): {{$shipping_address->zip}}</li>
                                    <li>@lang('Country'): {{$shipping_address->country}}</li>
                                @else 
                                    <li>@lang('Name'): <strong>{{ $shipping_address_admin->firstname ?? $order->user->firstname }} {{$shipping_address_admin->lastname ??$order->user->lastname }}</strong></li>
                                    <li>Teléfono móvil: {{$shipping_address_admin->mobile??''}}</li>
                                    <li>@lang('Address'): {{$shipping_address_admin->address}}</li>
                                @endif
                            </ul>
                        </address>
                    </div><!-- /.col -->

                    <div class="col-md-4">
                        <b>@lang('Order ID'):</b> {{$order->order_number}}<br>
                        <b>@lang('Order Date'):</b> {{showDateTime($order->created_at, 'd/m/Y')}} <br>
                        @if(!is_null($order->order_time))
                            <b>Fecha de Entrega: </b> {{$order->order_time}} </h4>
                        @else
                            <b>Fecha de Entrega: </b> No se especificó </h4>
                        @endif
                        <br>
                        <b>Monto Total:</b> {{$general->cur_sym.$order->total_amount}}
                    </div><!-- /.col -->
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
                                <th>Precio Total</th>
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
                        @if(isset($order->deposit) && $order->deposit[0]->status != 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="50%">Método de Pago</td>
                                        <td width="50%">
                                            @if($order->deposit[0]->method_code == 0)
                                                <span data-toggle="tooltip" title="Efectivo">Efectivo
                                                </span>
                                            @else
                                                {{ __($order->deposit[0]->gateway->name) }}
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Cargo por Pago</td>
                                        <td>{{$general->cur_sym. $charge = getAmount(@$order->deposit[0]->charge) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Monto total del Pago </td>
                                        <td>{{$general->cur_sym. getAmount(($order->deposit[0]->amount + $charge)) }}</td>
                                    </tr>
                                    <tr>
                                        @if(!is_null($order->propina))
                                            <th>Propina</th>
                                            <td>{{@$general->cur_sym.($order->propina)}}</td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                                        <td width="50%">{{@$general->cur_sym.getAmount($subtotal, 2)}}</td>
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
                </div><!-- /.row -->
                <!-- this row will not appear when printing -->
            </div><!-- /.content -->
        </div>
    </div>
</div>

</div>
</body>
</html>
