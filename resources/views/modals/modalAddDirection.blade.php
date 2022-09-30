
<div id="addDirection" class="modal fade">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Dirección</h5>
                <button type="button" class="close" id="closeAddDirection" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="NewDirection">
                    <div class="col-lg-6 mb-20">
                        <label for="phone" class="billing-label">Nombre</label>
                        <input class="form-control custom--style" id="firstname" name="firstname" type="text" value="{{auth()->user()->firstname?? old('firstname')}}" required>
                    </div>
                    <div class="col-lg-6 mb-20">
                        <label for="email" class="billing-label">Apellido</label>
                        <input class="form-control custom--style" id="lastname" name="lastname" type="text" value="{{auth()->user()->lastname?? old('lastname')}}" required>
                    </div>
                    <div class="col-lg-6 mb-20">
                        <label for="phone" class="billing-label">@lang('Mobile')</label>
                        <input class="form-control custom--style" id="mobile" name="mobile" type="text" value="{{auth()->user()->mobile?? old('mobile')}}" required>
                    </div>
                    <div class="col-lg-6 mb-20">
                        <label for="email" class="billing-label">@lang('Email')</label>
                        <input class="form-control custom--style" id="email" name="email" type="text" value="{{auth()->user()->email?? old('mobile')}}" required>
                    </div>
                    <div class="col-md-6 mb-20">
                        <label for="state" class="billing-label">@lang('State')</label>
                        <select id="state" name="estado" class="form-control" onchange="search_cities(this.value)">
                            <option selected disabled>Seleccione un estado</option>
                            @foreach($states as $key)
                                <option value="{{ $key->id }}">{{ $key->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-20">
                        <label for="city" class="billing-label">@lang('City')</label>
                        <select id="city" name="ciudad" class="form-control">
                           
                        </select>
                    </div>

                    <div class="col-md-12 mb-20">
                        <label for="zip" class="billing-label">@lang('Zip/Post Code')</label>
                        <input class="form-control custom--style" id="zip" name="postal" type="text" value="{{auth()->user()->address->zip?? old('zip')}}" required>
                    </div>

                    <div class="col-md-12 mb-20">
                        <label for="address" class="billing-label">@lang('Address')</label>
                        <textarea class="form-control custom--style" name="dirección" id="address" required>{{auth()->user()->address->address??old('address')}}</textarea>
                    </div>

                    {{-- <input min="0" step="any" type="number" id="propina_form" name="propina_form" value="{{ old('propina_form') }}" style="display:none"> --}}

                    <button class="btn btn-primary" onclick="postShippingUser()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>