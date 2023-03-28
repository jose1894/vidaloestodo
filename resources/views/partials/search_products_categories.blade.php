<div id="searchDropdown" class="dropdown-content">
	<div class="card2" style="float: right;">
		<button type="button" class="btn-close" aria-label="Close" onclick="$('.dropdown-content').css('display','none');" style="float: right;">Cerrar</button>
	</div>
	<br>
	<br>
	@foreach($products as $k => $key)
		@php $quantity = $key['stocks']->count() > 0 ? $key['stocks'][0]->quantity : 0 @endphp
		@if($quantity > 0)
			<div class="card2">
				<div class="card-body">
					<div class="row">
						<div class="col-md-1">
							<img width="40px" 
								src="{{ getImage(imagePath()['product']['path'].'/'.@$key->main_image, imagePath()['product']['size']) }}" alt="@lang('products-details')"
							>
						</div>
						<div class="col-md-8">
							<a href="{{route('product.detail', ['id'=>$key->id, 'slug'=>slug($key->name)])}}">
								{{$key->name}}
							</a>
								<br>
							<span>
								{{$general->cur_sym }}{{ getAmount($key->precioBaseIva, 2) }}
							</span>
						</div>
						<div class="col-md-3">
							<button @click="isShow = true" type="submit" class="cmn-btn-argo cart-add-btn showProduct{{ $key['id'] }} btn-sm" data-id="{{ $key['id'] }}">Agregar</button>
						</div>
					</div>
				</div>
			</div>
		@endif
	@endforeach

	{{-- @foreach($categories as $k => $key)
		<div class="card2 mb-2">
			<div class="card-body">
				<div class="row">
					<div class="col-md-1">
						
					</div>
					<div class="col-md-9">
						<span >
							{{$key->name}}
						</span>
					</div>
					<div class="col-md-1">
						
					</div>
				</div>
			</div>
		</div>
	@endforeach --}}
</div>