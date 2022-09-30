@extends('admin.layouts.app')

@section('panel')
<div class="content-wrapper">
    <div class="container-fluid p-0">
        <div class="card">
            <div class="card-body">
                <!-- Main content -->
                <div class="invoice" id="invoice">
                    <!-- title row -->
                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <h4><i class="fa fa-globe"></i> {{__($general->sitename)}} </h4>
                        </div>
                        <div class="col-lg-6">
                        <h5 class="float-sm-right">{{showDateTime($order->created_at, 'd/M/Y')}}</h5>
                        </div>
                    </div>
                    <hr>
                    <div class="row invoice-info">
                        <div class="col-md-4">
                            <h5 class="mb-2">Detalles del Usuario</h5>
                            <address>
                                <ul>
                                    <li>@lang('Name'): <strong>{{@$order->user->fullname }}</strong></li>
                                    <li>@lang('Dni'): <strong>{{@$order->user->type_dni }}</strong> - {{@$order->user->dni }}</li>
                                    <li>@lang('Address'): {{@$order->user->address->address}}</li>
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
                            @endphp

                            <address>
                                <ul>
                                    @if(isset($shipping_address))
                                        <li>Nombre: <strong>{{ $shipping_address->firstname ?? $order->user->firstname }} {{$shipping_address->lastname ?? $order->user->lastname }}</strong></li>
                                        <li>Teléfono móvil: {{$shipping_address->mobile??''}}</li>
                                        <li>Dirección: {{$shipping_address->address}}</li>
                                        <li>Estado: {{$shipping_address->state}}</li>
                                        <li>Ciudad: {{$shipping_address->city}}</li>
                                        <li>Zona Postal: {{$shipping_address->zip}}</li>
                                        <li>Pais: {{$shipping_address->country}}</li>
                                    @else 
                                        <li>Nombre: <strong>{{ $shipping_address_admin->firstname ?? $order->user->firstname }} {{$shipping_address_admin->lastname ?? $order->user->lastname }}</strong></li>
                                        <li>Teléfono móvil: {{$shipping_address_admin->mobile??''}}</li>
                                        <li>Dirección: {{$shipping_address_admin->address}}</li>
                                    @endif
                                </ul>
                            </address>
                        </div><!-- /.col -->

                        <div class="col-md-4">
                            <b>@lang('Order ID'):</b> {{$order->order_number}}<br>
                            <b>@lang('Order Date'):</b> {{showDateTime($order->created_at, 'd/m/Y')}} <br>
                            <b>Monto Total:</b> {{$general->cur_sym.$order->total_amount}}

                            <br> <br>
                            <div>
                                <h5 class="mb-2">Metodo de envío</h5>
                                <address>
                                    <ul>
                                        <li>Nombre: <strong> {{$order->shipping->name}}  </strong></li>                                      
                                        @if(!is_null($order->order_time))
                                            <li>Fecha de Entrega: <strong> {{$order->order_time}} </strong></li>
                                            @if(!is_null($order->order_time_horario))
                                                <li> <strong>Turno: </strong> {{$order->order_time_horario==1?'Mañana':''}}{{$order->order_time_horario==2?'Tarde':''}}{{$order->order_time_horario==3?'Noche':''}}</li>
                                            @endif
                                        @else
                                            <li>Tiempo en días: {{$order->shipping->shipping_time}} </li>
                                            <li>Fecha de Entrega: <strong> No se especificó </strong></li>
                                        @endif
                                    </ul>
                                </address>
                            </div>
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
                                                            <span data-toggle="tooltip" title="@lang('Cash On Delivery')">Efectivo
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
                                            <th>Propina</th>
                                            <td>{{@$general->cur_sym.($od->propina??0)}}</td>
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
                <hr>
                <div class="no-print float-right">

                    <a href="{{ route('print.invoice', $order->id) }}" target=blank class="btn btn-dark m-1"><i class="fa fa-print"></i>@lang('Print')</a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ URL::previous() }}"
        class="btn btn-sm btn--primary box--shadow1 text--small">
        <i class="la la-fw la-backward"></i> @lang('Go Back')
    </a>
@endpush
