<!DOCTYPE html>
<html lang="es">

<head>
    <title>{{ $general->sitename(__($page_title)) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @stack('meta-tags')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/fontawesome.all.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/owl.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/main.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue.'css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue ." css/color.php?color1=$general->
    base_color&color2=$general->secondary_color") }}">

    <link rel="shortcut icon" href="{{ getImage('assets/images/logoIcon/favicon.png', '128x128') }}"
        type="image/x-icon">
    @stack('style-lib')
    @stack('style')
    <style type="text/css">
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            overflow-y: scroll;
            scroll-behavior: smooth;
            height: 630px;
            width: 150%;
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 600px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            padding: 12px 16px;
            z-index: 1;
        }

        .dropdown-content>span {
            cursor: pointer;
            padding: 20px;
        }

        .dropdown-content>span:hover {
            -webkit-transition: all ease 0.3s;
            -moz-transition: all ease 0.3s;
            transition: all ease 0.3s;
            /*opacity: 0;*/
            /*box-shadow: 0 0 20px rgba(0, 104, 225, 0.2);*/
            /*font-size: 20px;*/
            color: #efa46d;
        }

        .dropdown:hover .dropdown-content {
            /*display: block;*/
        }


        .card2 {
            /* background-color: rgba(214, 224, 226, 0.2); */
            -webkit-border-top-left-radius: 5px;
            -moz-border-top-left-radius: 5px;
            border-top-left-radius: 5px;
            -webkit-border-top-right-radius: 5px;
            -moz-border-top-right-radius: 5px;
            border-top-right-radius: 5px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .card2 {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #ffffff !important;
            background-clip: border-box;
            /* border: 1px solid rgba(0,0,0,.125); */
            border-radius: 0.25rem;
        }
        
        /* whatsapp */
.whatsapp {
  position: fixed;
  width: 65px;
  height: 65px;
  bottom: 40px;
  left: 40px;
  color: #fff;
  border-radius: 50px;
  text-align: center;
  font-size: 30px;
  z-index: 100;
}
.whatsapp img {
  width: 100%;
}

.whatsapp-icon {
  margin-top: 13px;
  background-color: #fff;
}
    </style>
</head>

<body>
    <div class="overlay"></div>
    @if (!isset($withoutFooter))
    <a href="javascript:void(0)" class="scrollToTop"><i class="las la-angle-up"></i></a>
    @endif
    <!-- ===========Loader=========== -->
    <div class="preloader">
        <div class="logo">
        </div>
        <div class="loader-frame">
            <div class="loader1" id="loader1">
            </div>
            <div class="circle"></div>
            <img src="{{ getImage('assets/images/logoIcon/logo.png', '183x54') }}" alt="@lang('logo')">
            <!-- <span class="hello"><i class="las la-shopping-bag"></i></span>
            <h6 class="wellcome"><span>{{ __($general->preloader_title) }}</span></h6> -->
        </div>
    </div>
    <!-- ===========Loader=========== -->
    
    <a href="https://wa.me/584124170588?text=" class="whatsapp" target="_blank">
        <img src="{{ URL('assets/images/whatsapp-icon.png') }}" />
    </a>
    @if (!isset($withoutHeader))
        @include($activeTemplate.'partials.header')
        @if(!request()->routeIs('home') && !request()->routeIs('user.home'))
        @include($activeTemplate .'partials.breadcrumb')
        @endif
    @endif
    @yield('content')
    @if (!isset($withoutFooter))
        @include($activeTemplate.'partials.footer')
    @endif
    <script src="{{ asset($activeTemplateTrue.'js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/bootstrap.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/owl.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/countdown.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/wow.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/viewport.jquery.js') }}"></script>
    <script src="{{asset($activeTemplateTrue.'js/zoomsl.min.js')}}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/nice-select.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/main.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/dev.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue.'js/sxs.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>




    <script>
        'use strict';
        (function($){
            var product_id = 0;

            var CountProducts = '{{!! json_encode(getAllCategories()) !!}}';
            /*
            ==========TRIGGER NECESSARY FUNCTIONS==========
             */
             getMoneda();
            
            background();
            backgroundColor();
            triggerOwl();
            getCompareData();
            getCartData();
            getCartTotal();
            getWishlistData();
            getWishlistTotal();
            getShippingUser();
            getRate();
            
            

            /* COUNTDOWN FUNCTION FOR OFFERS */
            var countDown = $(".countdown");
            $.each(countDown, function (i, v) {
                $(v).countdown({
                    date: $(v).data('countdown'),
                    offset: +6,
                    day: 'Day',
                    days: 'Days'
                })
            });


            /*
            ==========PRODUCT QUICK VIEW ON MODAL==========
             */
            $(document).on('click', '.quick-view-btn', function(){
                var modal = $('#quickView');
                product_id = $(this).data('product');
                $.ajax({
                    url: "{{ route('quick-view') }}",
                    method: "get",
                    data: {
                        id: $(this).data('product')
                    },
                    success: function(response){
                        modal.find('.modal-body').html(response);
                        background();
                        backgroundColor();
                        triggerOwl();
                    }
                });
                modal.modal('show');
            });

            /*
            ==========QUANTITY BUTTONS FUNCTIONALITIES==========
            */
            $(document).on("click", ".qtybutton", function() {
                var $button = $(this);
                $button.parent().find('.qtybutton').removeClass('active')
                $button.addClass('active');
                    var oldValue = $button.parent().find("input").val();
                    if ($button.hasClass('inc')) {
                        var newVal = parseFloat(oldValue) + 1;
                    } else {
                        if (oldValue > 1) {
                            var newVal = parseFloat(oldValue) - 1;
                        } else {
                            newVal = 1;
                        }
                    }
                $button.parent().find("input").val(newVal);
            });

            /*
            ==========FUNCTIONALITIES BEFORE ADD TO CART==========
            */

            /*------VARIANT FUNCTIONALITIES-----*/
            $(document).on('click', '.attribute-btn', function(){
                var btn             = $(this);
                var ti              = btn.data('ti');
                var count_total     = btn.data('attr_count');
                var discount        = btn.data('discount');
                product_id          = btn.data('product_id');
                var attr_data_size  = btn.data('size');
                var attr_data_color = btn.data('bg');
                var attr_arr        = [];
                var base_price      = parseFloat(btn.data('base_price'));
                var extra_price     = 0;
                btn.parents('.attr-area:first').find('.attribute-btn').removeClass('active');
                btn.addClass('active');

                if(btn.data('type') == 2 || btn.data('type') == 3){
                    $.ajax({
                        url:"{{ route('product.get-image-by-variant') }}",
                        method:"GET",
                        data:{
                            'id': btn.data('id')
                        },
                        success:function(data)
                        {
                            if(!data.error){
                                btn.parents('.product-details-wrapper').find('.variant-images').html(data);
                                triggerOwl();
                            }
                        }
                    });
                }

                if($(document).find('.attribute-btn.active').length == count_total){
                    var activeAttributes = $(document).find('attribute-btn.active');
                    $(document).find('.attribute-btn.active').each(function(key, attr) {
                        extra_price += parseFloat($(attr).data('price'));
                        var id = $(attr).data('id');
                            attr_arr.push(id.toString());
                        });
                        var attr_id = JSON.stringify(attr_arr.sort());
                        var data = {
                            attr_id:attr_id,
                            product_id: product_id
                        }
                        if(ti==1){
                            $.ajax({
                                url:"{{ route('product.get-stock-by-variant') }}",
                                method:"GET",
                                data:data,
                                success:function(data)
                                {
                                    $('.stock-qty').text(`${data.quantity}`);
                                    $('.product-sku').text(`${data.sku}`);
                                    if(data.quantity>1){
                                        $('.stock-status').addClass('badge--success').removeClass('badge--danger');
                                    }else{
                                        $('.stock-status').removeClass('badge--success').addClass('badge--danger');
                                        notify('error', 'Sorry! Your requested amount of quantity isn\'t available in our stock');
                                    }
                                }
                            });
                        }
                }

                if(extra_price > 0) {
                    base_price += extra_price;
                    $('.price-data').text(base_price.toFixed(2));
                    $('.special_price').text(base_price.toFixed(2) - discount);

                }else{
                    $('.price-data').text(base_price.toFixed(2));
                    $('.special_price').text(base_price.toFixed(2) - discount);
                }

            });


            /*
            ==========FUNCTIONALITIES AFTER ADD TO CART==========
            */
            // /*------ADD TO CART-----*/


            var t;
            $(document).on('click','.cart-add-btn',function(e){
                clearTimeout(t);
                event.preventDefault();
                var product_id = $(this).data('id');               
                $('.showProduct'+product_id).attr('disabled',true).html('Procesando...');
                var attributes = $('.attribute-btn.active');
                var output = '';
                attributes.each(function(key, attr) {
                    output += `<input type="hidden" name="selected_attributes[]" value="${$(attr).data('id')}">`
                });
                $('.attr-data').html(output);

                var quantity = $('#quantity'+product_id).val();
                if (quantity < 0 || quantity == null) {
                    quantity = 1;
                }


                var attributes = $("input[name='selected_attributes[]']").map(function(){return $(this).val();}).get();

                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                    url:"{{route('add-to-cart')}}",
                    method:"POST",
                    data:{product_id:product_id, quantity:quantity, attributes:attributes},
                    success:function(response)
                    {
                        if(response.success) {
                            $('.showProduct'+product_id).attr('disabled',false).html('Agregar');  
                            getCartData();
                            getCartTotal();
                            notify('success', response.success);
                        }else{
                            notify('error', response);
                            $('.showProduct'+product_id).attr('disabled',false).html('Agregar');                      
                        }
                        // $('#quantity_qty'+product_id).removeAttr('disabled',false);
                    }
                });
                

            });

            // /*------- ADD TO CART PLAN PRIME */
            $(document).on('click','.cart-add-btn-prime',function(e){
                var product_id = $(this).data('id');
                
                var attributes = $('.attribute-btn.active');
                var output = '';
                attributes.each(function(key, attr) {
                    output += `<input type="hidden" name="selected_attributes[]" value="${$(attr).data('id')}">`
                });
                $('.attr-data').html(output);

                var quantity   = 1;
                var attributes = $("input[name='selected_attributes[]']").map(function(){return $(this).val();}).get();


                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                    url:"{{route('add-to-cart')}}",
                    method:"POST",
                    data:{product_id:product_id, quantity:quantity,attributes:attributes },
                    success:function(response)
                    {
                        if(response.success) {
                            getCartData();
                            getCartTotal();
                            notify('success', response.success);
                        }else{
                            notify('error', response);
                        }

                    }
                });
                // $('#quantity'+product_id).val(1);
                $('#quantity'+product_id).removeAttr('disabled',false).html('Agregar');
                $('#quantity_qty'+product_id).removeAttr('disabled',false);

            });


            /*------ADD QUANTITY TO CART-----*/
            var t;
            $(document).on('keydown','.cart-add-qty',function(e){
                clearTimeout(t);
                var id = $(this).data('id');
                var product_id = $(this).data('product_id');               
                // $('#quantity_qty'+product_id).attr('disabled',true);
                var attributes = $('.attribute-btn.active');
                var output = '';
                attributes.each(function(key, attr) {
                    output += `<input type="hidden" name="selected_attributes[]" value="${$(attr).data('id')}">`
                });
                $('.attr-data').html(output);

                
                var quantity   = $('#quantity_qty'+product_id).val();
                var attributes = $("input[name='selected_attributes[]']").map(function(){return $(this).val();}).get();

                t = setTimeout(() => {
                    $.ajax({
                        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                        url:"{{route('add-to-cart-qty')}}",
                        method:"POST",
                        data:{product_id:product_id, quantity:quantity, id:id, attributes:attributes},
                        success:function(response)
                        {
                            if(response.success) {
                                getCartData();
                                getCartTotal();
                                notify('success', response.success);
                            }else{
                                notify('error', response);
                            }
                            // $('#quantity_qty'+product_id).removeAttr('disabled',false);
                        }
                    });
                }, 1500);
                

            });

            $(document).on('change','.cart-add-qty',function(e){
                clearTimeout(t);
                var id = $(this).data('id');
                var product_id = $(this).data('product_id');               
                $('#quantity_qty'+product_id).attr('disabled',true);
                var attributes = $('.attribute-btn.active');
                var output = '';
                attributes.each(function(key, attr) {
                    output += `<input type="hidden" name="selected_attributes[]" value="${$(attr).data('id')}">`
                });
                $('.attr-data').html(output);

                
                var quantity   = $('#quantity_qty'+product_id).val();
                var attributes = $("input[name='selected_attributes[]']").map(function(){return $(this).val();}).get();

                // t = setTimeout(() => {
                    $.ajax({
                        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                        url:"{{route('add-to-cart-qty')}}",
                        method:"POST",
                        data:{product_id:product_id, quantity:quantity, id:id, attributes:attributes},
                        success:function(response)
                        {
                            if(response.success) {
                                getCartData();
                                getCartTotal();
                                notify('success', response.success);
                            }else{
                                notify('error', response);
                            }
                            // $('#quantity_qty'+product_id).removeAttr('disabled',false);
                        }
                    });
                // }, 1500);
                

            });

            /*------REMOVE PRODUCTS FROM CART-----*/
            $(document).on('click', '.remove-cart' ,function (e) {
                var btn = $(this);
                var id  = btn.data('id');
                var product_id = btn.data('pid');
                $('.badgeProduct').hide();

                var parent      = btn.parents('.cart-row');
                var subTotal    = parseFloat($('#cartSubtotal').text());
                var thisPrice   = parseFloat(parent.find('.total_price').text());


                var url = '{{route('remove-cart-item', '')}}'+'/'+id;
                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                    url: url,
                    method:"POST",
                    success: function(response){
                        if(response.success) {
                            // $('.showProduct'+product_id).hide();
                            $('.badgeProduct'+product_id).hide();
                            $('.quantity'+product_id).hide().val(1);
                            notify('success', response.success);
                            parent.hide(300);

                            if(thisPrice){
                                $('#cartSubtotal').text((subTotal - thisPrice).toFixed(2));
                            }
                            getCartData();
                            getCartTotal();
                        }else{
                            notify('error', response.error);
                        }
                    }
                });
            });

            /*------REMOVE ALL PRODUCTS FROM CART-----*/
            $(document).on('click', '.delete-all-p' ,function (e) {
                var btn = $(this);

                var url = '{{route('remove-cart-all')}}';
                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                    url: url,
                    method:"POST",
                    success: function(response){
                        if(response.success) {
                            $('.item-prod-argo').hide();
                            $('select[name=quantity]').hide();
                            notify('success', response.success);
                            getCartData();
                            getCartTotal();
                        }else{
                            notify('error', response.error);
                        }
                    }
                });
            });


            /*------REMOVE APPLIED COUPON FROM CART-----*/
            $(document).on('click', '.remove-coupon' ,function (e) {
                var btn = $(this);
                var url = '{{route('removeCoupon', '')}}';
                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                    url: url,
                    method:"POST",
                    success: function(response){
                        if(response.success) {
                            notify('success', response.success);
                            getCartData();

                            $('.coupon-amount-total').hide('slow');
                            $('input[name=coupon_code]').val('');
                        }
                    }
                });
            });

            /*
            ==========WISHLIST FUNCTIONALITIES==========
            */

            /* ADD TO WISHLIST */
            $(document).on('click','.add-to-wish-list', function(){
                var product_id = $(this).data('id');
                var products = $(`.add-to-wish-list[data-id="${product_id}"]`);
                var data = {
                    product_id: product_id
                }
                if($(this).hasClass('active')){
                    notify('error', 'Already in the wishlist');
                }else{
                    $.ajax({
                        url: "{{ route('add-to-wishlist') }}",
                        method: "get",
                        data: data,
                        success: function(response){
                            if(response.success) {
                                getWishlistData();
                                getWishlistTotal();

                                $.each(products, function (i, v) {
                                    if(!$(v).hasClass('active')){
                                        $(v).addClass('active');
                                    }
                                });
                                notify('success', response.success);

                            }else if(response.error) {
                                notify('error', response.error);
                            }else{
                                notify('error', response);
                            }
                        }
                    });
                }
            });

            /* REMOVE FROM WISHLIST */
            $(document).on('click', '.remove-wishlist' ,function (e) {
                var id  = $(this).data('id');
                var pid = $(this).data('pid');
                var url = '{{route("removeFromWishlist", '')}}'+'/'+id;
                var page= $(this).data('page');
                var parent = $(this).parent().parent();
                $.ajax({
                    url: url,
                    method: "get",
                    success: function(response){
                        if(response.success) {
                            getWishlistData();
                            getWishlistTotal();
                            notify('success', response.success);
                        }else{
                            notify('error', response.error);
                        }
                    }
                }).done(function(){
                    if(pid){
                        var products = $(`.add-to-wish-list[data-id="${pid}"]`);
                        $.each(products, function (i, v) {
                            if($(v).hasClass('active')){
                                $(v).removeClass('active');
                            }
                        });
                    }
                    if(page == 1){

                        if(id ==0){
                            $('.cart-table-body').html(`
                                <tr>
                                    <td colspan="100%">
                                        @lang('Your wishlist is empty')
                                    </td>
                                </tr>
                            `);
                            $('.remove-all-btn').hide(300);
                        }else{
                            parent.hide(300);
                        }
                    }
                });

            });

            //ADD TO Compare
            $(document).on('click','.add-to-compare', function(){
                var product_id = $(this).data('id');
                var products = $(`.add-to-compare[data-id="${product_id}"]`);

                var data = {
                    product_id: product_id
                }

                if($(this).hasClass('active')){
                    notify('error', 'Already in the comparison list');
                }else{
                    $.ajax({
                        url: "{{ route('addToCompare') }}",
                        method: "get",
                        data: data,
                        success: function(response){
                            if(response.success) {
                                getCompareData();
                                $.each(products, function (i, v) {
                                    if(!$(v).hasClass('active')){
                                        $(v).addClass('active');
                                    }
                                });
                                notify('success', response.success);
                            }else{
                                notify('error', response.error);
                            }
                        }
                    });
                }
            });

            let scrollTimeout;

            var page = 0;
            window.addEventListener("scroll", () => {
                clearTimeout(scrollTimeout);

                scrollTimeout = setTimeout(() => {
                    const container = document.getElementById("products_more_seller");
                    const footerCoor = document.getElementById("footer-copyright").getBoundingClientRect();
                    const viewportSize = window.innerHeight;
                    if (footerCoor.bottom - viewportSize <= footerCoor.height) {
                        $('#msg_loading').show();

                        $.ajax({
                            url: "{{ route('more_products') }}",
                            method: "get",
                            data:{page:page},
                            success: function(response){
                                if (response.length > 0) {
                                    $('#show_categories_products').html(response);
                                    $('#msg_loading').hide();
                                    page++;
                                    getCartData();
                                    getCartTotal();
                                }
                            }
                        });
                    }
                }, 100);
            });

            $("#lista_categoria > li").on('click', 'a', function () {
                let moneda = $(this).attr('id');
               // alert(id_categoria);

                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                    url:"{{route('set-moneda')}}",
                    method:"GET",
                    data: { 
                        moneda: moneda,                        
                    },
                    success:function(response)
                    {
                        if(response.moneda) {
                            notify('success', response.moneda);
                            $('.header-moneda').text(response.moneda);
                            //location.reload();
                            window.location.replace("{{ route('home') }}");
                        }else{
                            notify('error', response);
                        }

                    }
                });
            });
           

            //Setear moneda
            $(document).on('click','.header-change-moneda',function(e){

                $.ajax({
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                    url:"{{route('set-moneda')}}",
                    method:"GET",
                    success:function(response)
                    {
                        if(response.moneda) {
                            notify('success', response.moneda);
                            $('.header-moneda').text(response.moneda);
                            //location.reload();
                            //window.location.replace("{{ route('home') }}");
                        }else{
                            notify('error', response);
                        }

                    }
                });

            });

        })(jQuery)

        function QuantityValue(quantity, product_id) {
            $('#quantity'+product_id).val(quantity);
            $('#quantity-'+product_id).val(quantity);

            $('.showProduct'+product_id).attr('disabled',true).html('Procesando...');
            var attributes = $('.attribute-btn.active');
            var output = '';
            attributes.each(function(key, attr) {
                output += `<input type="hidden" name="selected_attributes[]" value="${$(attr).data('id')}">`
            });
            $('.attr-data').html(output);

            var quantity = $('#quantity'+product_id).val();
            if (quantity < 0 || quantity == null) {
                quantity = 1;
            }


            var attributes = $("input[name='selected_attributes[]']").map(function(){return $(this).val();}).get();

            $.ajax({
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                url:"{{route('add-to-cart')}}",
                method:"POST",
                data:{product_id:product_id, quantity:quantity, attributes:attributes},
                success:function(response)
                {
                    if(response.success) {
                        $('.showProduct'+product_id).show();
                        $('.badgeProduct'+product_id).show().html(quantity);
                        $('.quantity'+product_id).show().val(quantity);
                        $('.showProduct'+product_id).removeAttr('disabled',false).html('Agregar');
                        
                        getCartData();
                        getCartTotal();
                        // notify('success', response.success);
                    }else{
                        notify('error', response);
                    }
                    // $('#quantity_qty'+product_id).removeAttr('disabled',false);
                }
            });
        }

            
        function getCompareData() {
            $.ajax({
            url: "{{ route('get-compare-data') }}",
                method: "get",
                success: function(response){
                    $('.compare-count').text(response.total);
                }
            });
        }

        function getWishlistData(){
            $.ajax({
                url: "{{ route('get-wishlist-data') }}",
                method: "get",
                success: function(response){
                    $('.wish-products').html(response);
                }
            });
        }

        function getWishlistTotal(){
            $.ajax({
                url: "{{ route('get-wishlist-total') }}",
                method: "get",
                success: function(response){
                    $('.wishlist-count').text(response);
                }
            });
        }

        function getCartTotal(id){
            $.ajax({
                url: "{{ route('get-cart-total') }}",
                method: "get",
                success: function(response){
                    if (response.length > 0) {
                        $('.cart-count').text(response);
                        if (id != null) {
                            $('#quantity_qty'+id).removeAttr('disabled',false);
                            $('#quantity'+id).removeAttr('disabled',false).html('Agregar');
                        }
                    }else{
                        getCartTotal(id);
                    }
                }
            });
        }

        function getCartData(id){
            $.ajax({
                url: "{{ route('get-cart-data') }}",
                method: "get",
                success: function(response){
                    $('.cart--products').html(response);
                    getCartProduct();
                }
            });
        }

        function getCartProduct(){
            $.ajax({
                url: "{{ route('get-cart-product') }}",
                method: "get",
                success: function(response){
                    // getCartData();
                    // getCartTotal();
                    if (response.length > 0) {
                        for (var i=0; i < response.length; i++) {
                            $('.showProduct'+response[i].product_id).show();
                            $('.badgeProduct'+response[i].product_id).show().html(response[i].quantity);
                            $('.quantity'+response[i].product_id).show().val(response[i].quantity);
                            $('.showProduct'+response[i].product_id).removeAttr('disabled',false).html('Agregar');
                        }
                    }
                }
            });
        }

        function backgroundColor() {
            var customBg2=$('.product-single-color');
            customBg2.css('background', function () {
                var bg = ('#'+$(this).data('bg'));
                return bg;
            });
        }

        function background() {
            var img=$('.bg_img');
            img.css('background-image', function () {
            var bg = ('url(' + $(this).data('background') + ')');
            return bg;
            });
        };


        function submitUserForm() {
            var response = grecaptcha.getResponse();
            if (response.length == 0) {
                document.getElementById('g-recaptcha-error').innerHTML = '<span style="color:red;">@lang("Captcha field is required.")</span>';
                return false;
            }
            return true;
        }
        function verifyCaptcha() {
            document.getElementById('g-recaptcha-error').innerHTML = '';
        }

        var t;
        function FinishBuy(e) {
            clearTimeout(t);
            $.ajax({
                url: "{{ route('get_cart_compare') }}",
                method: "get",
                success: function(response){
                    if (response > 0) {
                        console.log(response);
                        notify('success', '¡Hay productos que fueron borrados del carrito por falta de existencias!');
                        getCartData();
                        getCartTotal();
                            // window.location.replace("{{ route('user.checkout') }}");
                    }else{
                        window.location.replace("{{ route('user.checkout') }}");
                    }
                }
            });
        }

        function search_cities(id) {
            $('#city').empty();
            $.ajax({
                url: "{{ route('search_cities') }}",
                method: "get",
                data: {
                    state_id: id
                },
                success: function(response){
                    if (response.length > 0) {
                        for (var i=0; i < response.length; i++) {
                            $('#city').append('<option value="'+response[i].name+'">'+response[i].name+'</option>');
                        }
                    }
                }
            });
        }

        function getShippingUser() {
            $.ajax({
                url: "{{ route('user.get_shipping_user') }}",
                method: "get",
                success: function(response){
                    if (response.length > 0) {
                        $('#shipping_user').html(response);
                    }
                }
            });
        }

        function getMoneda(){
            $.ajax({
                url: "{{ route('get-moneda') }}",
                method: "get",
                success: function(response){
                    if(response.moneda) {
                        $('.header-moneda').text(response.moneda);
                    }else{
                        notify('error', response);
                    }
                }
            });
        }

        function getRate(){
            $.ajax({
                url: "{{ route('get-rate') }}",
                method: "get",
                success: function(response){
                    if(response.rate) {
                        $('.header-rate').text(response.rate);
                    }else{
                        notify('error', response);
                    }
                }
            });
        }

        function postShippingUser() {

            var firstname = $('#firstname').val();
            var lastname = $('#lastname').val();
            var documento = $('#socumento').val();
            var mobile = $('#mobile').val();
            var email = $('#email').val();
            var state = $('#state').val();
            var city = $('#city').val();
            var zip = $('#zip').val();
            var address = $('#address').val();
                
            $.ajax({
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                url:"{{ route('user.post_shipping_user') }}",
                method:"POST",
                //data:{city:city,state:state,zip:zip,address:address},
                data:{city:city,state:state,zip:zip,address:address, mobile:mobile, firstname:firstname, lastname:lastname, email:email},
                success: function(response){
                    if (response.error) {
                        notify('error', response.error);
                    }
                    if (response.length > 0) {
                        $('#shipping_user').html(response);
                        console.log(response);
                        // AddressUser(
                        //     response[response.length-1].id,
                        // );
                        $('#closeAddDirection').click();
                    }
                }
            });
        }

        function deleteShippingUser(id) {
            $.ajax({
                url: "{{ route('user.delete_shipping_user') }}",
                method: "get",
                data:{id:id},
                success: function(response){
                    if (response) {
                        notify('success', 'Dirección Eliminada!');
                        // $('#card-shipping-'+id).fadeOut(300, function () {
                        //     this.remove()
                        // });
                        getShippingUser()
                    }else{
                        notify('error', response);
                    }
                }
            });
        }

        function search_bar_home(val) {
            var search = val;

            $.ajax({
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                url:"{{ route('search_bar_home') }}",
                method:"GET",
                data:{search:search},
                success: function(response){
                    $('#show_search_products_categories').html(response);
                    $(".dropdown-content").css('display','block');
                }
            });
        }

        function myFunction() {
        }

        // function MethodShip(id, val) {
        //     $('.card-method').removeClass('border border-success');
        //     $('.checkbox-method').prop('checked', false);

        //     $('#card-method-'+id).addClass('border border-success');
        //     $('#checkbox-method-'+id).prop('checked', true);

        //     alert(val);
        //     $('#shippingCharge2').val(val);
        // }

    </script>

    @stack('script-lib')
    @include($activeTemplate.'partials.notify')
    @stack('script')



    @stack('vue')
</body>

</html>