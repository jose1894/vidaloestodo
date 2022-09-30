@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">


        <div class="col-lg-4 col-md-4 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">Pago vía {{optional($deposit->gateway)->name}}</h5>

                    <div class="p-3 bg--white">
                        <div>
                            <img src="{{ $deposit->gateway_currency()->methodImage() }}" alt="@lang('profile-image')"
                                 class="b-radius--10 deposit-imgView">
                        </div>
                    </div>

                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Fecha
                            <span class="font-weight-bold">{{ showDateTime($deposit->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Número Trx
                            <span class="font-weight-bold">{{ $deposit->trx }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="font-weight-bold">
                                <a href="{{ route('admin.users.detail', $deposit->user_id) }}">{{ optional($deposit->user)->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Método
                            <span class="font-weight-bold">{{ optional($deposit->gateway)->name}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Monto
                            <span class="font-weight-bold">{{ getAmount($deposit->amount ) }} {{ trans($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cargar
                            <span class="font-weight-bold">{{ getAmount($deposit->charge ) }} {{ trans($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Después de la Carga
                            <span class="font-weight-bold">{{ getAmount($deposit->amount+$deposit->charge ) }} {{ $general->cur_text }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tarifa
                            <span class="font-weight-bold">1 {{trans($general->cur_text)}}
                                = {{ getAmount($deposit->rate) }} {{$deposit->baseCurrency()}}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            A Pagar
                            <span class="font-weight-bold">{{ getAmount($deposit->final_amo ) }} {{$deposit->method_currency}}</span>
                        </li>


                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Estado
                            @if($deposit->status == 2)
                                <span class="badge badge-pill bg--warning">Pendiente</span>
                            @elseif($deposit->status == 1)
                                <span class="badge badge-pill bg--success">Aprobado</span>
                            @elseif($deposit->status == 3)
                                <span class="badge badge-pill bg--danger">Rechazado</span>
                            @endif
                        </li>

                        @if($deposit->admin_feedback)
                            <li class="list-group-item">
                                <strong>@lang('Admin Response')</strong>
                                <br>
                                <p>{{ __($deposit->admin_feedback) }}</p>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 mb-30">

            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title mb-50 border-bottom pb-2">Información de pago del cliente</h5>

                    @if($details != null)
                        @foreach(\GuzzleHttp\json_decode($details) as $k => $val)
                            @if($val->type == 'file')
                                <div class="row mt-4">
                                    <div class="col-md-8">
                                        <h6>{{ __(inputTitle($k)) }}</h6>
                                        <img src="{{getImage('assets/images/verify/deposit/'.$val->field_name)}}" alt="@lang('Gateway Image')" >
                                    </div>
                                </div>
                            @else
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h6>{{ __(inputTitle($k)) }}</h6>
                                        <p>{{ __($val->field_name) }}</p>
                                    </div>
                                </div>

                            @endif
                        @endforeach
                    @endif


                    @if($deposit->status == 2)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button class="btn btn--success ml-1 approveBtn"
                                        data-dir="{{showDateTime($deposit->created_at,'Y').'/'. showDateTime($deposit->created_at,'m').'/'. showDateTime($deposit->created_at,'d')}}"
                                        data-id="{{ $deposit->id }}"
                                        data-info="{{$details}}"
                                        data-amount="{{ getAmount($deposit->amount)}} {{ trans($general->cur_text) }}"
                                        data-username="{{ optional($deposit->user)->username }}"
                                        data-toggle="tooltip" data-original-title="@lang('Approve')"><i class="fas fa-check"></i>
                                    Aprobar
                                </button>

                                <button class="btn btn--danger ml-1 rejectBtn"
                                        data-dir="{{showDateTime($deposit->created_at,'Y').'/'. showDateTime($deposit->created_at,'m').'/'. showDateTime($deposit->created_at,'d')}}"
                                        data-id="{{ $deposit->id }}"
                                        data-info="{{$details}}"
                                        data-amount="{{ getAmount($deposit->amount)}} {{ trans($general->cur_text) }}"
                                        data-username="{{ optional($deposit->user)->username }}"
                                        data-toggle="tooltip" data-original-title="@lang('Reject')"><i class="fas fa-ban"></i>
                                    Rechazar
                                </button>
                            </div>

                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>



    {{-- APPROVE MODAL --}}
    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación de Aprobación de Pago</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('admin.deposit.approve')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>
                            ¿Seguro desea Aprobar
                            <span class="font-weight-bold withdraw-amount text-success"></span>
                            depósito de <span class="font-weight-bold withdraw-user"></span>?
                        </p>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn--success">Aprobar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación de Rechazo de Pago</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.deposit.reject')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">

                        <p>
                            ¿Está seguro de <span class="font-weight-bold">rechazar</span>
                            <span class="font-weight-bold withdraw-amount text-success"></span>
                            depósito de
                            <span class="font-weight-bold withdraw-user"></span>?
                        </p>


                        <div class="form-group">
                            <label class="font-weight-bold mt-2">Razón del Rechazo</label>
                            <textarea name="message" id="message" class="form-control" rows="5"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn--danger">Rechazar</button>
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
            $('.approveBtn').on('click', function () {
                var modal = $('#approveModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.find('.withdraw-user').text($(this).data('username'));
                modal.modal('show');
            });

            $('.rejectBtn').on('click', function () {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.find('.withdraw-user').text($(this).data('username'));
                modal.modal('show');
            });
        })(jQuery)
    </script>
@endpush
