    @php
        $banners = getContent('banners_middle.element');
    @endphp


    @if ($banners->count() > 0)
        <!-- Call to Action Section Starts Here -->

        <div class="container-fluid">
            <div class="xxvvxx owl-carousel owl-theme">
                @foreach ($banners as $banner)
                    <div class="item">
                        <a href="{{ $banner->data_values->link }}" class="d-block overlay-effects">
                            <img src="{{ getImage('assets/images/frontend/banners_middle/' . $banner->data_values->image, '551x151') }}"
                                class="w-100" alt="@lang('products-offer')">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Call to Action Section Ends Here -->
    @endif

    
