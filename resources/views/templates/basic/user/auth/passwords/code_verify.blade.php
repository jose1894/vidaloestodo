@extends($activeTemplate.'layouts.master')
@section('content')


@php
    $content  = getContent('code_verify_page.content', true);
@endphp

<div class="account-section padding-bottom padding-top">
    <div class="contact-thumb d-none d-lg-block">
        <img src="{{ getImage('assets/images/frontend/code_verify_page/'. @$content->data_values->image, '600x840') }}" alt="@lang('login-bg')">
    </div>
    <div class="container">

        <div class="row">
            <div class="col-lg-7">
                <div class="section-header left-style">
                    <h3 class="title">
                        {{-- __($content->data_values->title) --}}
                        Verificar Código
                    </h3>
                    <p>
                        {{-- __($content->data_values->description) --}}
                        Hemos enviado un código. Por favor, espera unos minutos mientras se recibe. El código solo será válido después de 15 minutos a partir de ahora.
                    </p>
                </div>
                <form action="{{ route('user.password.verify.code') }}" method="POST" class="contact-form mb-30-none">
                    @csrf

                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="contact-group">
                        <label for="code">
                                {{-- @lang('Verification Code') --}}
                                Código de Verificación
                        </label>
                        <input type="text" id="code" name="code">
                    </div>

                    <div class="contact-group">
                        <button type="submit" class="custom-button m-0 ml-auto">
                            {{-- @lang('Submit') --}}
                            Aceptar
                        </button>
                    </div>

                    <div class="contact-group justify-content-end">
                        <p>
                            {{-- @lang('Please Check Junk/Spam Folder. If Not Found, ') --}}
                            Por favor, revise su bandeja de Spam si no lo recibe.
                            <a href="{{ route('user.password.request') }}">
                                {{-- @lang('Resend') --}}
                                Reenviar
                            </a>
                        </p>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
@endsection
