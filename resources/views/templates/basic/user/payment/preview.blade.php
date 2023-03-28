@extends($activeTemplate.'layouts.master',[
    'withoutHeader' => true,
    'withoutFooter' => true
])
@section('content')
<div class="checkout-section padding-top">
    <div class="container">
        <div class="row justify-content-center">
        @foreach($data as $data)
            @php $moneda = $data->moneda; @endphp
            <div class="col-lg-4 ">
            
                <div class="card">
                
                    <div class="card-header d-flex justify-content-between">
                        
                            <img style="width: 20%!important" src="{{ $data->gateway_currency()->methodImage() }}" class="card-img-top w-25" @lang('gateway-image')">
                        
                        <h3 class="align-self-center cl-1">
                            {{$data->name}}
                        </h3>
                    </div>
                    <div class="card-body border-1">

                        <ul class="list-group list-group-flush text-center">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Monto: <strong>{{getAmount($data->amount)}} {{$moneda=='usd'?$general->cur_text:'Bs'}}</strong></li>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Carga:
                                <span><strong>{{getAmount($data->charge)}}</strong> {{$general->cur_text}}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Pagadero: <strong>{{getAmount($data->amount + $data->charge)}} {{$moneda=='usd'?$general->cur_text:'Bs'}}</strong>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Conversión: <strong>1 {{$general->cur_text}} = Bs. 
                                    {{-- getAmount($data->rate) --}}
                                    {{ number_format(getAmount($rates->tasa_del_dia), 2, ",", ".") }}
                                </strong>
                            </li>

                            @if($moneda=='usd')
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                En Bolivares:
                                <strong>Bs. 
                                    {{-- getAmount($data->final_amo) --}}
                                    {{ number_format(getAmount($data->totalbs*$rates->tasa_del_dia), 2, ",", ".") }} 
                                </strong>
                            </li>
                            @else 
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                En Dolares:
                                <strong>USD. 
                                    {{-- getAmount($data->final_amo) --}}
                                    {{ number_format(getAmount($data->totalbs/$rates->tasa_del_dia), 2, ",", ".") }} 
                                </strong>
                            </li>
                            @endif

                            @if($data->gateway->crypto==1)
                                <li class="list-group-item">Conversión con {{$data->method_currency}} y el valor final se verá en el siguiente paso.
                                </li>
                            @endif
                            @if( 1000 >$data->method_code)
                                @if($data->status == 0)
                                    <li class="list-group-item p-0">
                                        <a href="{{route('user.deposit.confirm', $data->id)}}" class="cmn-btn btn-block"  >Pagar Ahora</a>
                                    </li>
                                @else
                                    <span class="btn-success btn-block"> Pagado con exito </span>
                                @endif

                            @else
                                @if($data->status == 0)
                                    <li class="list-group-item p-0">
                                        <a href="{{route('user.deposit.manual.confirm', $data->id)}}" class="cmn-btn btn-block" >Pagar Ahora</a>
                                    </li>
                                @else
                                    <span class="btn-success btn-block"> Pagado con exito (por confirmar) </span>
                                @endif        
                            @endif
                        </ul>

                    </div>
                
                </div>
            
            </div>
        @endforeach
        </div>
    </div>
</div>

<style type="text/css">
    .checkout-section {
        margin-top: 50px;
    }
</style>
@endsection

@push('breadcrumb-plugins')
    <li><a href="{{route('home')}}">@lang('Home')</a></li>
    <li><a href="{{route('products')}}">@lang('Products')</a></li>
    <li><a href="{{route('shopping-cart')}}">@lang('Cart')</a></li>
    <li><a href="{{route('user.checkout')}}">@lang('Checkout')</a></li>
    <li><a href="{{route('user.deposit')}}">@lang('Payment')</a></li>
@endpush

