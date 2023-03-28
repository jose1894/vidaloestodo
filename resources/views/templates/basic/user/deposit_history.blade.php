@extends($activeTemplate.'layouts.master')
@section('content')
<div class="payment-history-section padding-bottom padding-top">
    <div class="container">
        <div class="row">
            <div class="col-xl-3">
                <div class="dashboard-menu">
                    @include($activeTemplate.'user.partials.dp')
                    <ul>
                        @include($activeTemplate.'user.partials.sidebar')
                    </ul>
                </div>
            </div>
            <div class="col-xl-9">
                <table class="payment-table section-bg">
                    <thead>
                        <tr>
                            <th>@lang('S.N.')</th>
                            <th>ID de transacción</th>
                            <th>ID de Orden</th>
                            <th>Puerta</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Hora</th>
                            <th>Visto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($logs) >0)
                        @foreach($logs as $k=>$data)
                            <tr>

                                <td data-label="@lang('#')">{{$loop->iteration}}</td>
                                <td data-label="@lang('Transaction ID')">{{$data->trx}}</td>
                                <td data-label="@lang('Transaction ID')">{{$data->order['order_number']}}</td>
                                <td data-label="@lang('Gateway')">{{ __(optional($data->gateway)->name) }} {{ __(optional($data->gateway2)->name) }}</td>
                                <td data-label="@lang('Amount')">
                                    <strong>{{getAmount($data->amount)}} {{$data->method_currency}}</strong>
                                </td>
                                <td data-label="@lang('Status')">
                                    @if($data->status == 1)
                                        <span class="d-block badge badge-capsule badge--success">Completa</span>
                                    @elseif($data->status == 2)
                                        <span class="d-block badge badge-capsule badge--warning">Pendiente</span>
                                    @elseif($data->status == 3)
                                        <span class="d-block badge badge-capsule badge--danger">Cancelado</span>
                                    @endif

                                    @if($data->admin_feedback != null)
                                       <button class="cmn-btn-argo-info" data-admin_feedback="{{$data->admin_feedback}}" onclick="rejectedModal('{{$data->admin_feedback}}')"><i class="fa fa-info"></i></button>
                                    @endif
                                   
                                    @if($data->status == 3 || $data->id == 205)
                                        <button class="btn btn-success btn-sm" data-toggle="tooltip" data-title="Cambiar a Pendiente" style="width: 30px; height: 30px; padding: 0px; border-radius: 50%" data-id="{{ $data->id }}">
                                            <i class="fa fa-share"></i>
                                        </button>
                                    @endif


                                </td>
                                <td data-label="@lang('Time')">
                                    <i class="fa fa-calendar"></i> {{showDateTime($data->created_at)}}
                                </td>

                                @php
                                    $details = ($data->detail != null) ? json_encode($data->detail) : null;
                                    $gateway = ($data->gateway != null) ? json_encode($data->gateway) : null;
                                    $gateway2 = ($data->gateway2 != null) ? json_encode($data->gateway2) : null;
                                @endphp

                                <td data-label="@lang('Details')">
                                    <a href="javascript:void(0)" class="edit approveBtn"
                                        data-info="{{$details}}"
                                        data-gateway="{{$gateway}}"
                                        data-gateway2="{{$gateway2}}"
                                        data-id="{{ $data->id }}"
                                        data-subtotal='{{ number_format(getAmount($data->order['base_imponible'] + $data->order['excento']), 2, ",", ".") }} {{ $general->cur_sym }}'
                                        data-baseimponible='{{ number_format(getAmount($data->order['base_imponible']), 2, ",", ".") }} {{ $general->cur_sym }}'
                                        data-excento='{{ number_format(getAmount($data->order['excento']), 2, ",", ".") }} {{ $general->cur_sym }}'
                                        data-iva='{{ number_format(getAmount($data->order['base_imponible'] * 0.16), 2, ",", ".") }} {{ $general->cur_sym }}'
                                        data-amount='{{ number_format(getAmount($data->amount), 2, ",", ".") }} {{ $data->method_currency }}'
                                        data-charge='{{ number_format(getAmount($data->charge), 2, ",", ".") }} {{ $general->cur_sym }}'
                                        data-after_charge='{{ number_format(getAmount($data->amount + $data->charge), 2, ",", ".") }} {{ $data->method_currency }}'
                                        data-rate='{{ number_format(getAmount($data->rate), 2, ",", ".") }} {{ $data->method_currency }}'
                                        data-payable='{{ number_format(getAmount($data->final_amo), 2, ",", ".") }} {{ $data->method_currency }}'
                                    >
                                        <i class="fa fa-desktop"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @else
                            <tr>
                                <td colspan="100%"> ¡Sin Resultados!</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>


{{-- APPROVE MODAL --}}
<div id="approveModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-3">
                <h5 class="modal-title cl-white">Detalles</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CloseModal()">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between rounded-0">Monto subtotal <span class="withdraw-subtotal "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Base imponible <span class="withdraw-baseimponible "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Excento <span class="withdraw-excento "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">IVA <span class="withdraw-iva "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Monto total <span class="withdraw-amount "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Carga <span class="withdraw-charge "></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Después de la carga <span class="withdraw-after_charge"></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0">Tarifa de Conversión <span class="withdraw-rate"></span></li>
                    <li class="list-group-item d-flex justify-content-between rounded-0 border-bottom-0">Monto a Pagar <span class="withdraw-payable"></span></li>
                </ul>


                <ul class="list-group d-flex withdraw-detail mt-1">
                </ul>


            </div>
            <div class="modal-footer">
                <button type="button" class="cmn-btn" data-dismiss="modal" onclick="CloseModal()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- REJECTED MODAL --}}
<div id="rejectedModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-3">
                <h5 class="modal-title cl-white">Motivo de Cancelación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CloseModal()">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="detailsRejected"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="cmn-btn" data-dismiss="modal" onclick="CloseModal()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Detail MODAL --}}
<div id="detailModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-3">
                <h5 class="modal-title">Detalles</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CloseModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="withdraw-metodo"></div>

                <div class="withdraw-detail"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="CloseModal()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- STATUS MODAL --}}
<div id="statusModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('user.payment.pending') }}" method="POST" class="cmn-form mt-30">
                @csrf
                <div class="modal-header bg-3">
                    <h5 class="modal-title">Cambiar a Pendiente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CloseModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Realmente desea pasar este pago Rechazado nuevamente a Pendiente?
                    <div class="form-group">
                        <label>Número de Referencia</label>
                        <input type="text" class="form-control" name="ref" required placeholder="Ingresar nuevo número de referencia">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" id="payment_id">
                    <button type="submit" class="btn btn-success">Aceptar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="CloseModal()">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    "use strict";
    (function($){
        $('.approveBtn').on('click', function() {
            var modal = $('#approveModal');
            modal.find('.withdraw-subtotal').text($(this).data('subtotal'));
            modal.find('.withdraw-baseimponible').text($(this).data('baseimponible'));
            modal.find('.withdraw-excento').text($(this).data('excento'));
            modal.find('.withdraw-iva').text($(this).data('iva'));
            modal.find('.withdraw-amount').text($(this).data('amount'));
            modal.find('.withdraw-charge').text($(this).data('charge'));
            modal.find('.withdraw-after_charge').text($(this).data('after_charge'));
            modal.find('.withdraw-rate').text($(this).data('rate'));
            modal.find('.withdraw-payable').text($(this).data('payable'));

            var list = [];
            var details =  Object.entries($(this).data('info'));

            var gateway =  ($(this).data('gateway'));
            var gateway2 =  ($(this).data('gateway2'));
            if(isObjEmpty(gateway2) == true){
                gateway2 = null;
            }

            function isObjEmpty(obj){
                return Object.keys(obj).length === 0;
            }


            var ImgPath = "{{asset(imagePath()['verify']['deposit']['path'])}}";
            var singleInfo = '';

            if(gateway2 == null){
                var metodo = `<li class="list-group-item d-flex justify-content-between rounded-0">
                                        <span class="font-weight-bold "> Metodo de pago: </span> <span class="font-weight-bold ml-3">${gateway.name} </span>
                                    </li>`;
            }
            else{
                var metodo = `<li class="list-group-item d-flex justify-content-between rounded-0">
                                        <span class="font-weight-bold "> Metodo de pago: </span> <span class="font-weight-bold ml-3">${gateway.name} ${gateway2.name} </span>
                                    </li>`;
            }
            


            var field_name = '';
            var atr = '';
            for (var i = 0; i < details.length; i++) {
                if(typeof(details[i][1].field_name) === 'undefined'){
                    Object.keys(details[i][1]).forEach(function (key) {
                        field_name = details[i][1][key].field_name;
                        atr = key;
                    });
                }
                else{
                    field_name = details[i][1].field_name;   
                    atr = details[i][0].replaceAll('_', " ");  
                }
                console.log('field_name: ', field_name)
                if(field_name == null){
                    field_name = 'N/A';
                }
                
                
                if (details[i][1].type == 'file') {
                    singleInfo += `<li class="list-group-item d-flex justify-content-between rounded-0">
                                        <span class="font-weight-bold mb-2"> ${details[i][0].replaceAll('_', " ")} </span> <img class="w-25" src="${ImgPath}/${details[i][1].field_name}" alt="..." class="w-100">
                                    </li>`;
                }else{
                    singleInfo += `<li class="list-group-item d-flex justify-content-between rounded-0">
                                        <span class="font-weight-bold "> ${atr} </span> <span class="font-weight-bold ml-3"> ${field_name} </span>
                                    </li>`;
                }
            }

            if (singleInfo)
            {
                modal.find('.withdraw-detail').html(`<h5 class="my-3 text-center">Información de pago</h5> ${metodo} ${singleInfo}`);
            }else{
                modal.find('.withdraw-detail').html(`@lang('${singleInfo}')`);
            }

            modal.modal('show');
        });
        $('.detailBtn').on('click', function() {
            var modal = $('#detailModal');
            var feedback = $(this).data('admin_feedback');
            modal.find('.withdraw-detail').html(`<p> @lang('${feedback}') </p>`);
            modal.modal('show');
        });

        $('.btn-sm').on('click', function() {
            var modal = $('#statusModal');
            var id = $(this).data('id');
            $('#payment_id').val(id);
            modal.modal('show');
        });
    })(jQuery)

    function rejectedModal(details) {
        $('#detailsRejected').html('<h3>'+details+'</h3>');
        $('#rejectedModal').modal('show');
    }
    function CloseModal() {
        $('#rejectedModal').modal('hide');
        $('#approveModal').modal('hide');
        $('#detailModal').modal('hide');
        $('#statusModal').modal('hide');
    }
</script>
@endpush



@push('breadcrumb-plugins')
    <li><a href="{{route('user.home')}}">@lang('Dashboard')</a></li>
@endpush
