@extends($activeTemplate.'layouts.master')
@section('content')
@php
    $register_content        = getContent('register_page.content', true);
@endphp
    <div class="account-section padding-bottom padding-top">
        <div class="container">

            <div class="row">
                <div class="section-header text-center p-3">
                    <h3 class="title">
                        {{-- __($register_content->data_values->title) --}}
                        ¡Bienvenid@!
                    </h3>
                    <p>
                        {{-- __($register_content->data_values->description) --}}
                        Bienvenido a VidaAutomercados
                    </p>
                </div>
            </div>
            <div class="row">
                
                <div class="offset-2 col-8">
                    <form action="{{ route('user.register') }}" method="POST" class="contact-form mb-30-none">
                        @csrf
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="firstname">@lang('First Name')</label>
                                <input id="firstname" type="text" name="firstname" placeholder="Nombre" value="{{ old('firstname') }}" required>
                            </div>

                            <div class="form-group col-6">
                                <label for="lastname">@lang('Last Name')</label>
                                <input id="lastname" type="text" name="lastname" placeholder="Apellido " value="{{ old('lastname') }}" required>
                            </div>
                        </div>
                        {{-- 
                        <div class="form-group ">
                            <label for="username">@lang('Username')</label>
                            <input id="username" type="text" placedholder="" name="username" value="{{ old('username') }}" required>
                        </div> --}}

                        <div class="row">
                            <div class="col-6">
                                <label>@lang('Mobile')</label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <select name="country_code form-control">
                                                @include('partials.country_code')
                                            </select>
                                        </span>
                                    </div>
                                    <input style="height:65px" type="text" class="form-control" name="mobile" value="{{ old('mobile') }}" placeholder="Teléfono">
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <label for="email">@lang('Country')</label>
                                <!-- <input type="text" name="country"  > -->

                                <div class="input-group2">
                                    <div class="input-group-prepend2">
                                        <span class="input-group-text">
                                            <select name="country">
                                                @include('partials.country')
                                            </select>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-12">
                                <label for="email">@lang('Email')</label>
                                <input id="email" type="email" name="email" placeholder="correo" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6">
                                <label for="password">@lang('Password')</label>

                                <input id="password" type="password" placeholder="********" name="password" required autocomplete="new-password">

                            </div>

                            <div class="form-group col-6">
                                <label for="password-confirm">@lang('Confirm Password')</label>
                                <input id="password-confirm" type="password" name="password_confirmation" placeholder="********" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row">
                            @php $captcha =   getCustomCaptcha('register captcha') @endphp
                            @if($captcha)
                            <div class="form-group col-6">
                                <label for="password">@lang('Captcha')</label>
                                <input type="text" name="captcha" autocomplete="off" placeholder="Ingrese el código a continuación">
                            </div>

                            <div class="col-6">
                                <div class="d-flex mt-4 justify-content-start w-100" >
                                    @php echo  getCustomCaptcha('register captcha') @endphp
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div style="color: black;">
                                    <input id="terms-conditions" onclick="termsConditios()" type="checkbox" style="width: 25px; height: 20px;">
                                    <span onclick="window.open('{{ route('pages', ['id' => 39, 'slug'=> 'terminos-y-condiciones'])}}', '_blank')" style="cursor: pointer;">@lang('Terms and Conditions?')</span>
                                </div>
                                <a onclick="window.open('{{ route('pages', ['id' => 39, 'slug'=> 'terminos-y-condiciones'])}}', '_blank')" style="cursor: pointer;">Leer t&eacute;rminos y condiciones</a>
                            </div>
                        </div>

                        <div class="contact-group">
                            <div class="w-100 ">
                                <div class="m--10 d-flex flex-wrap align-items-center justify-content-between">
                                    <span class="account-alt">¿Ya tienes una cuenta? <a href="{{ route('user.login') }}">Iniciar Sesión</a></span>

                                    <button type="submit" id="recaptcha" class=" d-sm-block cmn-btn-argo" disabled>Registrar</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
<style>
.captcha.register {
    width: 100%!important;
}
</style>
@endpush

@push('script')
<script>
    'use strict';

    (function($){
        @if($country_code)
        var t = $(`option[data-code={{ $country_code }}]`).attr('selected','');
        @endif

        var country = $('select[name=country_code] :selected').data('country');
        if(country){
            $('input[name=country]').val(country);
        }

        $('select[name=country_code]').on('change', function(){
            $('input[name=country]').val($('select[name=country_code] :selected').data('country'));
        });

    })(jQuery)

    function termsConditios() {
        if ($('#terms-conditions').prop('checked')) {
            $('.custom-button').css('background','#19bbdb');
            $('#recaptcha').removeAttr('disabled',false);
        }else{
            $('.custom-button').css('background','#6c757d');
            $('#recaptcha').attr('disabled',true);
        }
    }
  </script>
@endpush
