@extends($activeTemplate.'layouts.master')

@section('content')
<div class="account-section padding-bottom padding-top">
    <div class="container">
        <a href="{{ route('ticket') }}"
            class="btn btn-sm btn-success box--shadow1 text--small mb-3 mt-2">
            <i class="la la-fw la-backward"></i> Regresar
        </a>
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-3 text-white">{{ __($page_title) }}
                        <a href="{{route('ticket') }}" class="cmn-btn-2 btn-sm float-right">
                            <i class="la la-backward"></i>
                            Regresar
                        </a>
                    </div>

                    <div class="card-body">

                        <form  action="{{route('ticket.store')}}" method="post" enctype="multipart/form-data" onsubmit="return submitUserForm();" class="contact-form">
                            @csrf
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label for="name">@lang('Name')</label>
                                    <input type="text" class="form-control rounded-0" id="name" name="name" value="{{@$user->firstname . ' '.@$user->lastname}}" placeholder="@lang('Enter Name')" required>
                                </div>

                                <div class="form-group col-lg-6">
                                    <label for="email">Correo</label>
                                    <input type="email" class="form-control rounded-0" id="email" name="email" value="{{@$user->email}}" placeholder="Ingrese el correo" required>
                                </div>

                                <div class="form-group col-lg-12">
                                    <label for="website">Sujeto</label>
                                    <input type="text" class="form-control rounded-0" id="subject" name="subject" value="{{old('subject')}}" placeholder="Sujeto" >
                                </div>

                                <div class="col-12 form-group">
                                    <label for="inputMessage">Mensaje</label>
                                    <textarea name="message" id="inputMessage" rows="6">{{old('message')}}</textarea>
                                </div>
                            </div>

                            <div class="row justify-content-between">
                                <div class="col-md-12">
                                    <label for="inputAttachments" class="font-weight-bold">Adjuntar Archivo</label>
                                    <div class="form-group custom-file">
                                        <input type="file" name="attachments[]" id="customFile" class="custom-file-input"/>
                                        <label class="custom-file-label2" style="
                                            position: absolute;
                                            top: 0;
                                            right: 0;
                                            left: 0;
                                            z-index: 1;
                                            height: calc(1.5em + .75rem + 2px);
                                            padding: .375rem .75rem;
                                            font-weight: 400;
                                            line-height: 1.5;
                                            color: #495057;
                                            background-color: #fff;
                                            border: 1px solid #ced4da;
                                            border-radius: .25rem;"
                                        for="customFile">Escoger Archivo</label>
                                    </div>
                                    <div id="fileUploadsContainer"></div>

                                    <p class="my-2 ticket-attachments-message text-muted">
                                        {{-- @lang("Allowed File Extensions: .jpg, .jpeg, .png, .pdf") --}}
                                        Solo se permiten las extensiones: .jpg, .jpeg, .png, .pdf.
                                    </p>
                                </div>
                            </div>
                            <a href="javascript:void(0)" class="cmn-btn-2 btn-sm mt-4 add-more-btn">
                                <i class="la la-plus"></i>
                            </a>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn btn-sm btn-danger h-unset" type="button" onclick="formReset()">&nbsp;Cancelar</button>

                                <button class="cmn-btn btn-success" type="submit" id="recaptcha" ></i>&nbsp;Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>

        "use strict";
        (function ($) {

            $(document).on("change", '.custom-file-input' ,function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label2").addClass("selected").html(fileName);
            });

            var itr = 0;

            $('.add-more-btn').on('click', function(){
                itr++
                $("#fileUploadsContainer").append(` <div class="form-group custom-file mt-3">
                                            <input type="file" name="attachments[]" id="customFile${itr}" class="custom-file-input"/>
                                            <label class="custom-file-label2" style="position: absolute;top: 0;right: 0;left: 0;z-index: 1;height: calc(1.5em + .75rem + 2px);padding: .375rem .75rem;font-weight: 400;line-height: 1.5;color: #495057;background-color: #fff;border: 1px solid #ced4da;border-radius: .25rem;" for="customFile${itr}">Escoger Archivo</label>
                                        </div>`);

            })
        })(jQuery);

        function formReset() {
            window.location.href = "{{url()->current()}}"
        }
    </script>
@endpush


@push('breadcrumb-plugins')
<li><a href="{{route('user.home')}}">@lang('Dashboard')</a></li>
<li><a href="{{route('ticket')}}">Tickets de Soporte</a></li>
@endpush
