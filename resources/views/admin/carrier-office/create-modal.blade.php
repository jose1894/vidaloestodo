
<div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Agregar oficina de envío asads</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.carrier-office.store', 0) }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label for="carrier_id" class="col-sm-2 col-form-label col-form-label-sm">@lang('Carrier')</label>
                        <div class="col-sm-10">
                                <select class="form-control select2-basic" name="carrier_id" required>
                                    <option selected disabled value="">
                                        {{-- @lang('Select One') --}}
                                        Seleccionar Uno
                                    </option>                                    
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ @$carrier->id }}"
                                            {{ isset($carrierOffice) ? ($carrier->id == $carrierOffice->carrier_id ? 'selected' : '') : '' }}>
                                            {{ __($carrier->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    <div class="form-group row">
                        <label for="state_id" class="col-sm-2 col-form-label col-form-label-sm">@lang('State')</label>
                        <div class="col-sm-4">
                            <select class="form-control select2-basic" name="state_id"  id="add-state_id" onchange="addSearchCities(this.value)" required>
                                <option selected disabled value="">
                                    {{-- @lang('Select One') --}}
                                    Seleccionar Uno
                                </option>                                    
                                @foreach ($states as $state)
                                <option value="{{ @$state->id }}"
                                {{ isset($carrierOffice) ? ($state->id == $carrierOffice->state_id ? 'selected' : '') : '' }}>
                                {{ __($state->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <label for="city_id" class="col-sm-2 col-form-label col-form-label-sm">@lang('City')</label>
                        <div class="col-sm-4">
                            <select class="form-control select2-basic" name="city_id" id="add-city_id" required>
                               
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="name" class="col-sm-2 col-form-label col-form-label-sm">@lang('Name')</label>
                        <div class="col-sm-10">
                        <input type="text" class="form-control form-control-sm" id="add-name" name="name" placeholder="@lang('Name')" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="code" class="col-sm-2 col-form-label col-form-label-sm">@lang('Code')</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control form-control-sm" id="add-code" name="code" placeholder="@lang('Code')">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="code" class="col-sm-2 col-form-label col-form-label-sm">@lang('Address')</label>
                        <div class="col-sm-10">
                        <textarea class="form-control form-control-sm" id="add-address" name="address" placeholder="@lang('Address')" required></textarea>
                        </div>
                    </div>
                        
                    <button type="submit" class="btn btn-block btn--success mr-2">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function addSearchCities(value){        
        $.ajax({
            url: "{{ route('search_cities') }}",
            method: "get",
            data: {
                state_id: value
            },
            async: false,
            success: function(response){
                if (response.length > 0) {
                    for (var i=0; i < response.length; i++) {
                        $('#add-city_id').append('<option value="'+response[i].id+'">'+response[i].name+'</option>');
                    }
                }
            }
        });
    }
</script>
