@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body">
                <div class="row justify-content-end">
                    <div class="col-lg-4 mb-3">
                        @if(request()->routeIs('admin.users.deposits'))
                            <form action="" method="GET">
                                <div class="input-group has_append">
                                    <input type="text" name="search" class="form-control" placeholder="Número de TRX/Nombre de Usuario" value="{{ $search ?? '' }}">

                                    <div class="input-group-append">
                                        <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <form action="{{route('admin.deposit.search', $scope ?? str_replace('admin.deposit.', '', request()->route()->getName()))}}" method="GET">
                                <div class="input-group has_append  ">
                                    <input type="text" name="search" class="form-control" placeholder="Número de TRX/Nombre de Usuario" value="{{ $search ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Número de TRX</th>
                                <th>@lang('Username')</th>
                                <th>Método</th>
                                <th>@lang('Amount')</th>
                                <th>Carga</th>
                                <th>Después de la carga</th>
                                <th>Tarifa</th>
                                <th>Pagadero</th>

                                @if(request()->routeIs('admin.deposit.pending') || request()->routeIs('admin.deposit.approved'))
                                    <th>@lang('Action')</th>

                                @elseif(request()->routeIs('admin.deposit.list') || request()->routeIs('admin.deposit.search') || request()->routeIs('admin.users.deposits'))
                                    <th>Estado</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($deposits as $deposit)
                            @php
                                $details = ($deposit->detail != null) ? json_encode($deposit->detail) : null;
                            @endphp
                            <tr>
                                <td data-label="Fecha"> {{ showDateTime($deposit->created_at) }}</td>
                                <td data-label="Número de TRX" class="font-weight-bold text-uppercase">{{ $deposit->trx }}</td>
                                <td data-label="@lang('Username')"><a href="{{ route('admin.users.detail', @$deposit->user_id) }}">{{@ $deposit->user->username }}</a></td>

                                <td data-label="@lang('Method')">
                                    @if($deposit->method_code == 0)
                                        <span data-toggle="tooltip" title="Pago en Efectivo">@lang('COD')</span>
                                    @else
                                    {{ $deposit->gateway->name }}
                                    @endif
                                </td>

                                <td data-label="@lang('Amount')" class="font-weight-bold">
                                    {{$deposit->method_currency}}. {{ number_format(getAmount($deposit->amount), 2, ",", ".") }}</td>
                                <td data-label="@lang('Charge')" class="text-success">{{ getAmount($deposit->charge)}} {{ $general->cur_text }}</td>
                                <td data-label="@lang('After Charge')"> {{ getAmount($deposit->amount+$deposit->charge) }} {{ $general->cur_text }}</td>
                                <td data-label="@lang('Rate')"> {{$deposit->method_currency}}. {{ number_format(getAmount($deposit->rate), 2, ",", ".") }} </td>
                                <td data-label="@lang('Payable')" class="font-weight-bold">{{$deposit->method_currency}}. {{ number_format(getAmount($deposit->final_amo), 2, ",", ".") }} </td>

                                @if(request()->routeIs('admin.deposit.approved') || request()->routeIs('admin.deposit.pending'))

                                    <td data-label="@lang('Action')">
                                        @if($deposit->method_code == 0)
                                            <a href="javascript:void(0)" class="icon-btn ml-1 disabled"><i class="la la-eye"></i></a>
                                        @else
                                            <a href="{{ route('admin.deposit.details', $deposit->id) }}" class="icon-btn ml-1 " data-toggle="tooltip" data-original-title="Detalles">
                                                <i class="la la-eye"></i>
                                            </a>
                                        @endif
                                    </td>

                                @elseif(request()->routeIs('admin.deposit.list')  || request()->routeIs('admin.deposit.search') || request()->routeIs('admin.users.deposits'))
                                    <td data-label="@lang('Status')">
                                        @if($deposit->status == 2)
                                            <span class="badge badge--warning">Pendiente</span>
                                        @elseif($deposit->status == 1)
                                            <span class="badge badge--success">Aprobado</span>
                                        @elseif($deposit->status == 3)
                                            <span class="badge badge--danger">Rechazado</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($empty_message) }}</td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table><!-- table end -->
                </div>
            </div>
            <div class="card-footer py-4">
                {{ $deposits->links('admin.partials.paginate') }}
            </div>
        </div><!-- card end -->
    </div>
</div>
@endsection



