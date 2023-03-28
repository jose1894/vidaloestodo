@extends(activeTemplate().'layouts.master',[
    'withoutHeader' => true,
    'withoutFooter' => true
])

@section('content')
<div class="manual-payment contact-page section-big-py-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card">
                    <h5 class="cl-1 pt-3 text-center">Validación de Pago</h5>
                    <hr>
                    <div class="card-body">
                        <form action="{{ route('user.deposit.manual.update', $data['id']) }}" method="POST" enctype="multipart/form-data" onsubmit="showLoader()">
                            @csrf
                            <input type="hidden" name="gateway" value="{{ $method->id}}"/>
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="text-center">Monto a cancelar 
                                        {{ $data->method_currency }}.
                                        {{ number_format(getAmount($data['final_amo']), 2, ",", ".") }}
                                        {{-- getAmount($data['final_amo']) --}} 

                                        {{-- @if(getAmount($data['charge']*$data['rate']) > 0)
                                            incluyendo los 

                                            {{ $method->currency }}.
                                            {{ number_format(getAmount($data['charge']*$data['rate']), 2, ",", ".") }}

                                            por cobrar
                                        @endif --}}
                                        
                                    </h4>
                                    <hr>

                                    <h4 class="text-center bg-warning p-2 mb-2">Por favor, siga las Instrucciones</h4>
                                    <p class="card-zelle">@php echo __($method->method->description) @endphp</p>
                                </div>

                                @if($method->gateway_parameter)

                                    @foreach(json_decode($method->gateway_parameter) as $k => $v)

                                        @if($v->type == "text")
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><strong>{{__(inputTitle($v->field_level))}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif</strong></label>
                                                    <input type="text" class="form-control form-control-lg"
                                                           name="{{$k}}"  value="{{old($k)}}" placeholder="{{__($v->field_level)}}">
                                                </div>
                                            </div>
                                        @elseif($v->type == "textarea")
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label><strong>{{__(inputTitle($v->field_level))}}
                                                            @if($v->validation == 'required')
                                                            <span class="text-danger">*</span>
                                                            @endif</strong>
                                                        </label>
                                                        <textarea name="{{$k}}"  class="form-control"  placeholder="{{__($v->field_level)}}" rows="3">{{old($k)}}</textarea>

                                                    </div>
                                                </div>
                                        @elseif($v->type == "file")
                                            <div class="col-md-12">

                                                <label class="text-uppercase">
                                                    <strong>
                                                        {{__($v->field_level)}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif
                                                    </strong>
                                                </label>
                                                <div class="verification-img">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type='file' name="{{$k}}" id="imageUpload" accept=".png, .jpg, .jpeg" />
                                                            <label for="imageUpload"></label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="imagePreview" style="background-image: url({{ asset(imagePath()['image']['default']) }});">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                    @endforeach

                                @endif
                                <div class="col-md-12 my-2">
                                    <button type="submit" class="cmn-btn btn-block">@lang('Confirm')</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .manual-payment {
        margin-top: 50px;
    }
</style>
@endsection

@push('script')
<script>
    "use strict";

    function showLoader() {
        $('.preloader').fadeIn();
    }

    (function($){
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#imageUpload").on('change', function() {
            readURL(this);
        });
    })(jQuery)
</script>
@endpush
