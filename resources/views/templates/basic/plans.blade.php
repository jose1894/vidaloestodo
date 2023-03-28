@extends($activeTemplate .'layouts.master')
@section('content')

<div class="main-pages">
    <div class="x-pages">
       <div class="x-main">
    


        <div class="unete">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="img-n-l">
                            <img src="assets/images/logoIcon/logo.png" alt="logo.png">
                        </div>
                        <div class="unete-content">
                            <h2>SÉ PREMIUM Y DISFRUTA DE LA EXCLUSIVIDAD</h2>
                            <p>QUE OFRECEMOS A NUESTROS DISTINGUIDOS CLIENTES<br>

                            </p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="prime">
            <div class="container-fluid">
                
                <div class="row">
                    @foreach($plans as $plan)
                    <div class="col-md-6">
                        <div style="padding-bottom: 20px;" class="card">
                            @if($plan->planDetails->meses <= 10)
                                <img class="card-img-plans" src="assets/images/nosotros/women-p1.jpg" alt="Card image cap">
                            @else
                                <img class="card-img-plans" src="assets/images/nosotros/women-p2.jpg" alt="Card image cap">
                            @endif
                            <div class="card-body" id="app-{{$plan->id}}">
                                <h6 class="card-title">{{ $plan->planDetails->name }}</h6>
                                <h2>{{ $plan->base_price }} $</h2>
                                <p></p>
                                
                                <button type="submit" class="cmn-btn-argo cart-add-btn-prime" data-id="{{ $plan['id'] }}"> Hazte Prime ahora </button>
                                <input type="number" name="quantity" step="1" min="1" value="1" class="integer-validation" style="display: none;">

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
            </div>
        </div>

        <div class="beneficios">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Beneficios de ser Prime</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="beneficios-content">
                            <img src="assets/images/nosotros/bolsa-ico.png" alt="bolsa-ico.png">
                            <h6>DELIVERY COMPLETAMENTE GRATIS Y RÁPIDO</h6>
                            <p>Disfruta de todas tus entregas EXPRESS completamente gratis en todos los artículos.</p>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="beneficios-content">
                            <img src="assets/images/nosotros/man-ico.png" alt="car-ico.png">
                            <h6>OFERTAS EXCLUSIVAS</h6>
                            <p>Podrás disfrutar de ofertas exclusivas en muchos de nuestros productos durante todo el año.</p>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="beneficios-content">
                            <img src="assets/images/nosotros/car-beneficios-ico.png" alt="man-ico.png">
                            <h6>PREMIUM DAY</h6>
                            <p>Disfruta de nuestras OFERTAS FLASH, descuentos por tiempo y/o unidades limitadas, exclusivo para miembros Premium, con ahorros épicos.</p>
                        </div>

                    </div>


                </div>
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div style="text-align: center; margin-top: 100px;">
                           <p> Busca la marca de verificación Premium mientras compras. </ br>Significa OFERTAS, DELIVERY rápido y ¡gratis!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('vue')
<script>
//--
</script>
@endpush

@push('script')
<script>
//--
</script>
@endpush

@push('breadcrumb-plugins')
    <li><a href="{{route('home')}}">@lang('Home')</a></li>
@endpush

@push('meta-tags')
    @include('partials.seo')
@endpush
