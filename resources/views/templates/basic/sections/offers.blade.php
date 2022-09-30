@foreach ($offers as $offer)
<!-- Flash Section Starts Here -->
<div class="flash-sell-section padding-bottom-half padding-top-half oh">
    <div class="container">
        <div class="section-header-2 left-style">
            <h3 class="title">{{ __($offer->name) }}</h3>
            <ul class="countdown" data-countdown="{{ showDateTime($offer->end_date,'m/d/Y H:i:s') }}">
                <li>
                    <span class="countdown-title"><span class="days">00</span></span>
                    <p class="days_text">@lang('Days')</p>
                </li>
                <li>
                    <span class="countdown-title"><span class="hours">00</span></span>
                    <p class="hours_text">@lang('Hours')</p>
                </li>
                <li>
                    <span class="countdown-title"><span class="minutes">00</span></span>
                    <p class="minu_text">@lang('Minutes')</p>
                </li>
                <li>
                    <span class="countdown-title"><span class="seconds">00</span></span>
                    <p class="seco_text">@lang('Seconds')</p>
                </li>
            </ul>
        </div>

        <div class="m--15">
            <div class="product-slider-2 owl-carousel owl-theme">

                @foreach ($offer->products as $item)
                @php
                $discount = calculateDiscount($offer->amount, $offer->discount_type, $item->base_price);

                $wCk = checkWishList($item->id);
                $cCk = checkCompareList($item->id);
                @endphp

                <div class="product-item-2">
                    <div class="product-item-2-inner wish-buttons-in">
                        @if(isset($item->offer))
                        @if($item->offer['activeOffer'])
                            @if($item->offer['activeOffer']['discount_type'] == 2)
                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}% </span>
                            @else 
                                <span class="text-white bg-danger tag-discount"> -{{$item->offer['activeOffer']['amount']}}$ </span>
                            @endif
                        @endif
                        @endif
                        <div class="product-thumb">
                            <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">
                                <img src="{{ getImage(imagePath()['product']['path'].'/thumb_'.@$item->main_image, imagePath()['product']['size']) }}"
                                    alt="@lang('flash')">
                            </a>
                        </div>
                        @php
                            $rate = session()->get('rate');
                            $moneda = session()->get('moneda');
                        @endphp
                        <div class="product-content">
                            <div class="product-before-content">
                                <h6 class="title">
                                    <a href="{{route('product.detail', ['id'=>$item->id, 'slug'=>slug($item->name)])}}">{{
                                        __($item->name) }}</a>
                                </h6>
                                <div class="ratings-area justify-content-between">
                                    <div class="ratings">
                                        @php echo __(display_avg_rating($item->reviews)) @endphp
                                    </div>

                                    <span class="ml-2 mr-auto">({{ __($item->reviews->count()) }})</span>
                                    <div class="price">
                                        @if($moneda=='Dolares'|| $moneda == '')
                                            @if($discount > 0)
                                            {{ $general->cur_sym }}{{ getAmount($item->base_price - $discount, 2) }}
                                            <del>{{ getAmount($item->base_price, 2) }}</del>
                                            @else
                                            {{ $general->cur_sym }}{{ getAmount($item->base_price, 2) }}
                                            @endif
                                        @else 
                                            @if($discount > 0)
                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->base_price - $discount * $rate, 2) }}
                                            <del>{{ getAmount($item->base_price * $rate, 2) }}</del>
                                            @else
                                            {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->base_price * $rate, 2) }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="product-after-content">
                                <button data-product="{{$item->id}}" class="cmn-btn btn-sm quick-view-btn">
                                    @lang('View')
                                </button>
                                <div class="price">
                                    @if($moneda=='Dolares' || $moneda == '')
                                        @if($discount > 0)
                                        {{ $general->cur_sym }}{{ getAmount($item->base_price - $discount, 2) }}
                                        <del>{{ getAmount($item->base_price, 2) }}</del>
                                        @else
                                        {{ $general->cur_sym }}{{ getAmount($item->base_price, 2) }}
                                        @endif
                                    @else 
                                        @if($discount > 0)
                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->base_price - $discount * $rate, 2) }}
                                        <del>{{ getAmount($item->base_price * $rate, 2) }}</del>
                                        @else
                                        {{ $moneda == 'Euros' ? '€. ' : 'Bs. ' }}{{ getAmount($item->base_price * $rate, 2) }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
<!-- Flash Section Ends Here -->
@endforeach