@extends($activeTemplate.'layouts.master',[

    'withoutHeader' => true,

    'withoutFooter' => true

])

@section('content')

<div class="dashboard-section padding-top" id="deposit-container">

    <div class="container">
        
       <div class="alert alert-danger" role="alert">Al momento de seleccionar el método de pago debe de colocar el monto total de su factura.
</div>

    <form action="{{route('user.deposit.insert')}} " method="POST">

        @csrf

        <div class="row row-types">

            <div class="col-6">

                <div class="container-type" :class="{ active: type == TYPES.ONE }" @click="type = TYPES.ONE; checked_payments = []">

                    <h3>Pago Único</h3>

                    <p>Seleccione esta opción si tu pago es por un solo método de pago</p>

                </div>

            </div>

            <div class="col-6">

                <div class="container-type" :class="{ active: type == TYPES.MULTIPLE }" @click="type = TYPES.MULTIPLE">

                    <h3>Multi Pagos</h3>

                    <p>Seleccione esta opción si son varios métodos de pago (hasta 2 métodos)</p>

                </div>

            </div>

        </div>

        <div class="row row-total">

            <div class="col-4">

                <div class="container-total">

                    <p class="bold">Total a Pagar</p>

                </div>                

            </div>

            <div class="col-4">

                <div class="container-total">

                    <p id="montoUSD">{{ number_format(getAmount($order->total_amount), 2,".","") }} $</p>

                </div>                

            </div>

            <div class="col-4">

                <div class="container-total">

                    <p id="montoBS">{{ number_format(getAmount($order->totalbs),2,".","") }} Bs.</p>

                </div>                

            </div>

        </div>

        <div class="row">

            @foreach($gatewayCurrency as $data)

                <div class="col-lg-3 col-md-6 col-sm-6 col-xs mb-5">

                    <div class="card">

                        <div class="card-header p-0"><div class="row">

                            <div class="col-10">

                            <img class="card-img-top" src="{{ $data->methodImage() }}" alt="@lang('gateway-image')">

                            {{$data->name}}

                            </div>



                            <div class="col">

                                <input class="cmn-btn btn-block" v-model="checked_payments" @change.prevent="preventChange({{ $data->id }},'{{ $data->currency }}')" data-currency="{{$data->currency}}"  type="checkbox" class="form-check-input" id="gateway{{ $data->id }}" name="gateway_id[]" value="{{$data->id}}">

                            </div>



                        </div></div>

                        <div class="card-body" v-if="checked_payments.indexOf('{{ $data->id }}') != -1">



                            <!--<input class="cmn-btn btn-block" type="checkbox" class="form-check-input" id="gateway_id" name="gateway_id[]" value="{{$data->id}}"  >-->



                            <ul class="list-group list-group-flush text-center">

                                {{-- <li class="list-group-item d-flex justify-content-between align-items-center">Monto total: <strong>{{getAmount($order->total_amount)}} {{$general->cur_text}}</strong></li>



                                <li class="list-group-item d-flex justify-content-between align-items-center">Bs.<strong>

                                        {{ number_format(getAmount($order->totalbs), 2,".","") }} 

                                    </strong>

                                </li> --}}



                                <li class="list-group-item d-flex justify-content-between align-items-center">

                                    <input id="cantidad_pagar{{ $data->id }}" class="cantidad_pagar" step="any" type="number" name="cantidad_pagar[]" value="{{ old('cantidad_pagar') }}"placeholder="Cantidad a pagar en {{$data->currency}}" onchange="sumar('{{$data->id}}','{{$data->currency}}');" min="0" onkeyup="sumar('{{$data->id}}','{{$data->currency}}');" >

                                </li>

                                



                            </ul>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        <input type="hidden" id="monto">

        <input type="hidden" name="currency" class="edit-currency" value="{{$data->currency}}">

        <input type="hidden" name="method_code" class="edit-method-code" value="{{$data->method_code}}">

        <a id="submit" class="cmn-btn btn-block" onclick="Verificate()" style="display:none; color: white; cursor:pointer;"><center>@lang('Pay Now')</center></a>

        <button id="submitbtn" style="display: none">@lang('Pay Now')</button>

    </form>

    </div>

</div>



<style type="text/css">

    form {

        margin-top: 50px;

    }

    .card-header {

        padding-right: 10px !important;

    }

    .container-type {

        padding: 15px;

        border-radius: 10px;

        background-color: rgba(214, 224, 226, 0.2);

        cursor: pointer;

    }

    .container-type.active {

        background-color: #97d9ff;

    }

    .container-type h3 {

        font-size: 14px;

        font-weight: bold;

    }

    .container-type p {

        line-height: 12px;

        margin-top: 10px;

        margin-bottom: 10px;

        font-size: 12px;

    }

    .row-types {

        margin-bottom: 20px;

    }

    .container-total {

        border: 1px solid rgba(214, 224, 226, 1);

        padding: 10px;

        text-align: center;

        margin-bottom: 20px;

        padding-bottom: 15px;

    }

    .row-total, .row-total .col-4 {

        padding: 0px;

        margin: 0px;

    }

    .container-total p {

        font-size: 14px;

        margin: 0px;

    }

    .cantidad_pagar {

        font-size:  12px;

    }

</style>

@endsection





@push('breadcrumb-plugins')

    <li><a href="{{route('home')}}">@lang('Home')</a></li>

    <li><a href="{{route('products')}}">@lang('Products')</a></li>

    <li><a href="{{route('shopping-cart')}}">@lang('Cart')</a></li>

    <li><a href="{{route('user.checkout')}}">@lang('Checkout')</a></li>

@endpush



@push('script')

<script>



    var vue = new Vue({

        el: '#deposit-container',

        data: {

            checked_payments: [],

            TYPES: {

                ONE: 1,

                MULTIPLE: 2

            },

            type: 1

        },

        methods: {

            preventChange(id, currency) {

                if (vue.checked_payments.length > 2) {

                    const index = vue.checked_payments.indexOf(id);

                    vue.checked_payments.splice(index,1);

                }

            }

        }

    });



    var totalbs = '{{ number_format(getAmount($order->totalbs), 2,".","") }}';

    var total_amount = '{{ number_format(getAmount($order->total_amount), 2,".","") }}';

    var rate = '{{ $rate->tasa_del_dia }}';



    function sumar(id,currency) {



        var total = 0;

        var checks1 = '';

        var checks2 = null;



        var monto = 0;

        // if (vue.checked_payments.length > 1) {

        var ch1 = $('#gateway'+vue.checked_payments[0]).val();

        var ch2 = $('#gateway'+vue.checked_payments[1]).val()??0;



        var checks1 = document.getElementById('gateway'+ch1);

        var checks2 = document.getElementById('gateway'+ch2)??null;



        var currency1 = checks1.dataset.currency;

        var val1 = this.calcularMonto(ch1, currency1)??0;



        var currency2 = null;

        var val2 = 0;



        if (checks2) {

            var currency2 = checks2.dataset.currency;

            var val2 = this.calcularMonto(ch2, currency2);

        }





        // }else{

        //     var ch1 = $('#gateway'+vue.checked_payments[0]).val();

        //     var ch2 = $('#gateway'+vue.checked_payments[]).val()??0;



        //     var checks1 = document.getElementById('gateway'+ch1);

        //     var checks2 = null;



        //     var currency1 = checks1.dataset.currency;

        //     var currency2 = null;



        //     var val1 = parseFloat(this.calcularMonto(ch1, checks1.dataset.currency));

        //     var val2 = 0

        // }



        var monto = parseFloat(val1)+parseFloat(val2);

        console.log(monto+'-'+val1+'-'+val2)





        // for (var i=1; i < vue.checked_payments.length+1; i++) {

        //     console.log([i]+' - '+monto);

        // if (currency1 == 'Bs.F') {

        //     monto = parseFloat(monto + val1);

        // }else{

        //     monto = parseFloat(monto + val1);

        // }

        // if (currency2 != null) {

        //     if (checks2.dataset.currency == 'Bs.F') {

        //         monto = parseFloat(monto + val2);}else{monto = parseFloat(monto + val2);}

        // }

            // }

        // }



        // console.log(val1,val2,monto)

        if (monto > 0) {

            this.montoTotal(id,parseFloat(monto).toFixed(2));

        }else{

            $('#montoUSD').html('{{ number_format(getAmount($order->total_amount), 2,".","") }}'+' $');

            $('#montoBS').html('{{ number_format(getAmount($order->totalbs), 2,".","") }}'+' Bs.');

        }





        



        // if (currency == 'Bs.F') {

        //     $(".cantidad_pagar").each(function() {

        //         var totalVar = parseFloat(val1)+parseFloat(val2);

        //         // console.log(totalVar);



        //         if (isNaN(parseFloat($(this).val()))) {



        //             total += 0;



        //         } else {



        //             total += parseFloat($(this).val())/rate;

        //             // console.log('total - '+total)

        //             //si el valor se pasa del total lo reseteamos a 0 y mostramos la alerta

        //             if (total > {{ $order->total_amount }}) {

        //                 total = 0;



        //                 $(this).val(total);

        //                 alert('El valor supera el del total de la orden');

        //             }



        //         }



        //     });

        // }else{

        //     $(".cantidad_pagar").each(function() {

        //         var totalVar = val1+val2;

        //         // console.log(totalVar);



        //         if (isNaN(parseFloat($(this).val()))) {



        //             total += 0;



        //         } else {



        //             total += parseFloat($(this).val());



        //             //si el valor se pasa del total lo reseteamos a 0 y mostramos la alerta

        //             if (total > {{ $order->total_amount }}) {

        //                 total = 0;

        //                 $(this).val(total);

        //                 alert('El valor supera el del total de la orden');

        //             }



        //         }



        //     });

        // }



    }





    function calcularMonto(id, currency) {

        var monto = $('#cantidad_pagar'+id).val();



        if (currency == 'Bs.F'){

            monto = monto / rate

        }

        if (isNaN(monto) || monto == null || monto < 0 || monto == 0) {

            monto = 0;

        }

        return monto;

    }



    function montoTotal(id,monto) {



        $('#montoBS').html(parseFloat(totalbs-monto*rate).toFixed(2)+' Bs.');

        $('#montoUSD').html(parseFloat(total_amount - monto).toFixed(2)+' $');

        $('#monto').val(monto);

        if (monto > {{ $order->total_amount }}) {

            total = 0;

            $('#cantidad_pagar'+id).val(total);

            alert('El valor supera el del total de la orden');



            var checks = document.getElementById('gateway'+id);

            var currency = checks.dataset.currency;

            $('#cantidad_pagar'+id).val(0);



            this.sumar(id,currency);

        }

    }

    function Verificate() {

        var monto = $('#monto').val();

        if (monto == total_amount) {

            $('#submitbtn').click();

        }else if(monto<total_amount){

            alert('El monto a pagar es menor que el total de la orden');

        }else{

            alert('El monto a pagar es mayor que el total de la orden');

        }

    }



    $(document).ready(function(){

        var totalbs = '{{ number_format(getAmount($order->totalbs), 2,".","") }}';

        var total_amount = '{{ number_format(getAmount($order->total_amount), 2,".","") }}';

        var rate = '{{ $rate->tasa_del_dia }}';

		var cantidadMaxima=2;



        // Evento que se ejecuta al soltar una tecla en el input

        $("#cantidad").keydown(function(){

            $("input[type=checkbox]").prop('checked', false);

            $("#seleccionados").html("0");

        });

    

        // Evento que se ejecuta al pulsar en un checkbox

        $("input[type=checkbox]").change(function(){

            var id = $(this).val();



            //Buscamos el tipo de método de pago

            var checks = document.getElementById('gateway'+id);

            var currency = checks.dataset.currency;

            //---



            if (vue.type == vue.TYPES.ONE) {

                vue.checked_payments = [$(this).val()];

            }

            // Cogemos el elemento actual

            var elemento=this;

            var contador=0;

    

            // Recorremos todos los checkbox para contar los que estan seleccionados

            $("input[type=checkbox]").each(function(){

                if($(this).is(":checked"))

                    contador++;

            });



            if(contador>0){

                $("#submit").css("display", "block");

            }

            else{

                $("#submit").css("display", "none");

            }

    



    

            // Comprovamos si supera la cantidad máxima indicada

            if(contador>cantidadMaxima)

            {

                alert("Solo puedes seleccionar 2 metodos de pago");

    

                // Desmarcamos el ultimo elemento

                $(elemento).prop('checked', false);

                contador--;

            }





            // if (currency == 'Bs.F') {

            //     var rate = parseFloat('{{ $rate->tasa_del_dia }}')

            //     if (vue.checked_payments.length == 1) {

            //         $('#cantidad_pagar'+id).val(totalbs);

            //     }

            // }else{

            //     if (vue.checked_payments.length == 1) {

            //         $('#cantidad_pagar'+id).val(total_amount);

            //     }

            // }

    

            $("#seleccionados").html(contador);

            sumar(id,currency)

        });      



    });

</script>

@endpush









