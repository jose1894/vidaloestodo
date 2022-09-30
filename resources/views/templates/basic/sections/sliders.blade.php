@php
$sliders = getContent('sliders.element');
@endphp

@if ($sliders && $sliders->count() > 0)
    <div id="carouselExampleSlidesOnly" class="carousel slide" data-ride="carousel"  style="margin-bottom: 18px;">
        <div class="carousel-inner">
            @foreach ($sliders as $key => $item)
                <div class="carousel-item @if ($key == 0) active @endif">
                    <img class="d-block w-100"
                        src="{{ getImage('assets/images/frontend/sliders/' . @$item->data_values->slider, '1220x350') }}"
                        alt="@lang('slider')">
                </div>
            @endforeach           
        </div>
        <a class="carousel-control-prev" data-target="#carouselExampleSlidesOnly" role="button" data-slide="prev">

            <i class="fa fa-chevron-left fa-size-color" aria-hidden="true"></i>
        </a>
        <a class="carousel-control-next" data-target="#carouselExampleSlidesOnly" role="button" data-slide="next">

            <i class="fa fa-chevron-right fa-size-color" aria-hidden="true"></i>
        </a>
    </div>


    {{-- <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">

    <div class="carousel-inner">
        @foreach ($sliders as $key => $item)        
            <div class="carousel-item @if ($key == 0) active @endif"
                style="background-image: url({{ URL('assets/images/frontend/sliders/' . $item->data_values->slider) }})">
            </div>
        @endforeach
    </div>
    <a class="carousel-control-prev" data-target="#carouselExampleIndicators" role="button"
        data-slide="prev">

        <i class="fa fa-chevron-left fa-size-color" aria-hidden="true"></i>
    </a>
    <a class="carousel-control-next" data-target="#carouselExampleIndicators" role="button"
        data-slide="next">

        <i class="fa fa-chevron-right fa-size-color" aria-hidden="true"></i>
    </a>
</div>

<div class="swiffy-slider">
    <ul class="slider-container">
        @foreach ($sliders as $slider)
        <li><img src="{{ getImage('assets/images/frontend/sliders/'. @$slider->data_values->slider, '1220x350') }}" style="max-width: 100%;height: auto;"></li>
        @endforeach
        
    </ul>

    <button type="button" class="slider-nav"></button>
    <button type="button" class="slider-nav slider-nav-next"></button>

    <div class="slider-indicators">
        <button class="active"></button>
        <button></button>
        <button></button>
    </div>
</div>

<div class="banner-section oh rounded--5 mb-30">
    <div class="banner-slider owl-theme owl-carousel">
        @foreach ($sliders as $slider)
        <a href="{{ @$slider->data_values->link }}" class="d-block">
            <div class="slide-item">
                    <img src="{{ getImage('assets/images/frontend/sliders/'. @$slider->data_values->slider, '1220x350') }}" alt="@lang('slider')">
                </div>
            </a>
        @endforeach
    </div>
    <div class="slide-progress"></div>
</div>
<div class="banner-slider owl-carousel owl-theme">
    @foreach ($sliders as $slider)
    <div class="item">

        <img src="{{ getImage('assets/images/frontend/sliders/'. @$slider->data_values->slider, '1220x350') }}" alt="@lang('slider')">
    </div>
    @endforeach
</div> --}}
@endif
