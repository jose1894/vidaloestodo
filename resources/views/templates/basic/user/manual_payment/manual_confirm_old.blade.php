<!-- @section('content')
<div class="contact-page section-big-py-space bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12 col-sm-12">              
                <div class="card">
                    <h5 class="cl-1 pt-3 text-center">Validación de Pago</h5>
                    <hr>
                    <div class="card-body">
                        <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="col-md-12">
                                <h4 class="text-center">Se le solicita que pague la cantidad final de
                                    {{ $data[0]->method[0]->currency }}.
                                    {{ number_format(getAmount($data[0]['final_amo']), 2, ",", ".") }}
                                    {{-- getAmount($data[0]['final_amo']) --}}
                                    incluyendo los
                                    {{ $data[0]->method[0]->currency }}.
                                    {{ number_format(getAmount($data[0]['charge']*$data[0]['rate']), 2, ",", ".") }}
                                    {{-- getAmount($data[0]['charge']*$data[0]['rate']) --}}
                                    por cobrar
                                </h4>
                                <hr>

                                <h4 class="text-center bg-warning p-2 mb-2">Por favor, siga las Instrucciones</h4>

                            </div>
                            @foreach($data as $data)
                                <input type="hidden" name="gateway" value="{{ $data->method[0]->id}}"/>
                                <div class="row">

                                    <div class="col-md-12">
                                        <h4 class="pt-2 text-center">@php echo __($data->method[0]->method->description) @endphp</h4>
                                    </div>

                                    @if($data->method[0]->gateway_parameter)

                                        @foreach(json_decode($data->method[0]->gateway_parameter) as $k => $v)

                                            @if($v->type == "text")
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label><strong>{{__(inputTitle($v->field_level))}} @if($v->validation == 'required') <span class="text-danger">*</span>  @endif</strong></label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            name="{{$k}}[]"  value="{{old($k)}}" placeholder="{{__($v->field_level)}}">
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

                            @endforeach
                            <div class="col-md-12 my-2">
                                <button type="submit" class="cmn-btn btn-block">@lang('Confirm')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection -->