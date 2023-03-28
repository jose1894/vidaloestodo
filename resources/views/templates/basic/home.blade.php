@extends($activeTemplate.'layouts.master')
@section('content')
    <section>
        <div class="slider-argo">
            @include($activeTemplate . 'sections.sliders')
        </div>
    </section>

    <section>
        <div class="container-fluid">
            <div class="ads-argo">
                @include($activeTemplate . 'sections.banners_middle')
            </div>
        </div>
    </section>
    <section>
        <div class="category-argo">
            @include($activeTemplate . 'sections.filter_categories', ['f_categories' => $categories])

            <center>
                <div id="msg_loading" style="display: none;padding: 30px;">Cargando...</div>
            </center>
                        
        </div>
    </section>
@endsection

@push('script')
    <script>
        'use strict';
        (function($) {
            //ADD TO CART
            $(document).on('click', '.subscribe-btn', function() {
                var email = $('input[name="email"]').val();

                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    url: "{{ route('subscribe') }}",
                    method: "POST",
                    data: {
                        email: email
                    },
                    success: function(response) {
                        if (response.success) {
                            notify('success', response.success);
                        } else {
                            notify('error', response);
                        }
                    }
                });

            });
        })(jQuery)
    </script>
@endpush


@push('meta-tags')
    @include('partials.seo')
@endpush
