@extends($activeTemplate.'layouts.master')

@section('content')

@php
    $login_content        = getContent('login_page.content', true);
@endphp

<div class="account-section padding-bottom padding-top">
    <div class="container">
        <div class="row">
            <div class="offset-md-2 col-md-8 col-12">
                <div class="section-header left-style text-center">
                    <h3 class="title">@lang('welcome_back')</h3>
                    <p>@lang('welcome_back_desc')</p>
                </div>
                    <form method="POST" action="{{ route('user.loginPost')}}" class="contact-form mb-30-none">
                        @csrf

                        <div class="contact-group">
                            <label for="username">@lang('Email')</label>
                            <input id="username" type="text" name="email" placeholder="Ingresa tu email" value="{{ old('email') }}">
                        </div>

                        <div class="contact-group">
                            <label for="password">@lang('Password')</label>
                            <input id="password" type="password" name="password" placeholder="@lang('Enter Your Password')" required autocomplete="current-password">
                        </div>


                        @php $captcha =   getCustomCaptcha('register captcha') @endphp

                        @if($captcha)
                        <div class="contact-group">
                            <label for="password">@lang('Captcha')</label>
                            <input type="text" name="captcha" autocomplete="off" placeholder="Ingrese el código a continuación">

                            <div class="d-flex mt-4 justify-content-end w-100">
                                @php echo  getCustomCaptcha('register captcha') @endphp
                            </div>
                        </div>
                        @endif

                        <div class="contact-group">
                            <button type="submit" id="recaptcha" class="m-0 ml-auto cmn-btn-argo">@lang('Login')</button>
                        </div>



                        <div class="contact-group">
                            <div class="w-100">
                                <div class="m--10 d-flex flex-wrap align-items-center justify-content-between">
                                    @if (Route::has('user.register'))
                                    <span>¿@lang('Don\'t have an account')? <a href="{{route('user.register')}}">@lang('Create An Account')</a></span>
                                    @endif
                                    @if (Route::has('user.password.request'))
                                    <span class="account-alt">
                                        <a href="{{route('user.password.request')}}">
                                            ¿{{ __('Forgot Password') }}?
                                        </a>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>


                    </form>

            </div>
        </div>
    </div>
</div>

@endsection


@push('breadcrumb-plugins')
    <li><a href="{{route('home')}}">@lang('Home')</a></li>
@endpush
