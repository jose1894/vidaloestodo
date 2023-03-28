@if(!is_null($data) && count($data) > 0)
		@foreach($data as $k => $key)
			@php
				$address = json_decode($key->shipping_address);
			@endphp
			@if(!is_null($key->shipping_address))
				<div class="card mb-3 card-shipping" id="card-shipping-{{$key->id}}">
					<div class="card-body">
						<div class="row">
							<div class="col-md-2" >
								<input v-model="shippingUser" type="radio" class="form-check-input check-radio-addres" name="shippingUser" value="{{$key->id}}" id="checks-shipping-{{$key->id}}">
							</div>
							<div class="col-md-9" >
								<span >
									<small class="textspan">
										{{$address->address}} - {{$address->state}} - {{$address->city}} - {{$address->zip}} - {{$address->country}}
									</small>
								</span>
							</div>
							<div class="col-md-1">
								<span class="text-danger" style="float: right;" onclick="deleteShippingUser('{{$key->id}}')">
									<i class="fa fa-trash"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			@endif
		@endforeach
	
@endif