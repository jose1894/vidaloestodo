<!-- Header Section Starts Here -->
<header class="container-fluid fixed-top px-0" id="header">
    <nav id="menu-normal" class="row navbar navbar-expand py-0">
        <div class="col-12 py-2 linea-uno">
            <div class="header-wrapper justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ getImage('assets/images/logoIcon/logo-algolar.png', '183x54') }}"
                            alt="@lang('logo')">
                    </a>
                </div>
                <form autocomplete="off" autocomplete="false" action="{{ route('product.search') }}" method="GET"
                    class="header-search-form mr-auto @if (!request()->routeIs('home')) w-100 @endif">
                    <div class="dropdown">
                        <div class="header-form-group">
                            <input v-model="search" v-on:keyup="autocomplete" autocomplete="off" autocomplete="false"
                                type="text" name="search_key" value="{{ request()->search_key }}"
                                placeholder="@lang('Search today')...">
                            <button type="submit"><i class="las la-search"></i></button>
                        </div>
                        <div id="show_search_products_categories"></div>
                        <div class="select-item">
                            <select class="select-bar" name="category_id">
                                <option selected value="0">@lang('All Categories')</option>
                                @foreach ($categories_with_products_in_stock as $category)
                                    @if ($category->parent_id == null)
                                        <option value="{{ $category->id }}">@lang($category->name)</option>
                                    @endif
                                    @php
                                        $prefix = '--';
                                    @endphp
                                    @foreach ($category->allSubcategories as $subcategory)
                                        {{-- @include($activeTemplate.'partials.subcategories', ['subcategory' => $subcategory,
                    'prefix'=>$prefix]) --}}
                                        <option value="{{ $subcategory->id }}">
                                            {{ $prefix }} @lang($subcategory->name)
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                    </div>
                </form>
                <ul class="shortcut-icons" onclick="$('.dropdown-content').css('display','none');">
                    <li>
                        @if (Auth::check())
                            <div class="link-argo">
                                <a href="#">Usuario</a>
                                <a href="#" class="dashboard-menu-bar">
                                    {{ str_limit(auth()->user()->fullname, 12, '...') }}
                                </a>
                            </div>
                        @else
                            <div class="link-argo">
                                <a href="{{ route('user.register') }}">@lang('createaccountargo')</a>
                                <a href="javascript:void(0)" class="dashboard-menu-bar">
                                    @lang('loginsystem')
                                </a>
                            </div>
                        @endif

                    </li>
                    <li>
                        <div class="link-argo">
                            <a href="#">@lang('zone argo')</a>
                            <a href="#">
                                Valencia
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="link-argo header-change-moneda">
                            <a href="#">Moneda</a>
                            <a href="#" id="moneda" name="moneda">
                                <span class="header-moneda"> </span>
                            </a>
                        </div>
                    <li>
                        <div class="header-bar e-none">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </li>
                    <li>
                        <a class="xcart" href="javascript:void(0)" id="cart-button">
                            <img src="{{ getImage('assets/images/icos/car-ico.png', '54x54') }}"
                                alt="@lang('logo')">
                            <span class="cart-count amount">0</span>
                        </a>
                    </li>
                </ul>

            </div>
        </div>
        <div class="area-links linea-dos col-12 py-1">
            <div class="bar-bottom-argo">
                <div class="menu-argo-btn">
                    <a href="javascript:void(0)" id="menu-argo-button">
                        <img src="{{ getImage('assets/images/icos/menu-categoria-ico.png', '54x54') }}"
                            alt="@lang('logo')"> <span class="x-argo-t">Comprar por categoria</span>
                    </a>

                </div>

                <div class="menu-argo-link">
                    <ul class="menu ml-auto d-none d-lg-flex">
                        <li>
                            <a href="{{ route('plans') }}">Hazte Premiun</a>
                        </li>
                        <li>
                            <a href="{{ route('products.offers') }}">Ofertas</a>
                        </li>

                        <li>
                            <a href="{{ route('mas-vendidos') }}">Más Vendidos</a>
                        </li>

                        <li>
                            <a href="{{ route('recientes') }}">Productos Recientes</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

<div onclick="$('.dropdown-content').css('display','none');">

    <div class="mobile-menu d-lg-none">
        <div class="mobile-menu-header">
            <div class="mobile-menu-close">
                <i class="las la-times"></i>
            </div>
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{ getImage('assets/images/logoIcon/logo_2.png', '183x54') }}" alt="@lang('logo')">
                </a>
            </div>
        </div>
        <ul class="nav-tabs nav border-0">
            <li>
                <a href="#menu" class="active" data-toggle="tab">@lang('Menu')</a>
            </li>
            <li>
                <a href="#cate" data-toggle="tab">Categoría</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="menu">
                <div class="mobile-menu-body">
                    <ul class="menu mt-4">
                        <li>
                            <a href="{{ route('home') }}">@lang('Home')</a>
                        </li>

                        <li>
                            <a href="{{ route('products') }}">@lang('Products')</a>
                        </li>

                        <li>
                            <a href="{{ route('brands') }}">@lang('Brands')</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">@lang('Contact')</a>
                        </li>
                    </ul>
                </div>
                <div class="quick-links mt-4">
                    <ul>
                        @if ($pages->count() > 0)
                            @foreach ($pages as $item)
                                <li><a
                                        href="{{ route('pages', ['id' => $item->id, 'slug' => slug($item->data_values->page_title)]) }}">@php
                                        echo __($item->data_values->page_title); @endphp</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="tab-pane fade" id="cate">
                <div class="left-category single-style">
                    <ul class="categories">
                        @foreach ($categories as $category)
                            <li>
                                <a
                                    href="{{ route('products.category', ['id' => $category->id, 'slug' => slug($category->name)]) }}">
                                    @php echo $category->icon @endphp {{ $category->name }}
                                </a>
                                <div class="cate-icon">
                                    <i class="las la-angle-down"></i>
                                </div>

                                @if ($category->allSubcategories->count() > 0)
                                    <ul class="sub-category">
                                        @foreach ($category->allSubcategories as $subcategory)
                                            @include($activeTemplate . 'partials.menu_subcategories', [
                                                'subcategory' => $subcategory,
                                            ])
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ===========Cart=========== -->
    <div id="body-overlay" class="body-overlay"></div>


    <div class="cart-sidebar-area" id="cart-sidebar-area">
        <div class="header-cart">Tu carrito
            <span class="side-sidebar-close-btn"><i class="las la-times"></i></span>
        </div>
        <div class="s-argo">
            <div class="top-content">
                <!--       <a href="{{ route('home') }}" class="logo">
                    <img src="{{ getImage('assets/images/logoIcon/logo_2.png', '183x54') }}" alt="@lang('logo')">
                </a> -->
            </div>
            <div class="bottom-content">
                <div class="cart-products cart--products">


                </div>
            </div>
        </div>
    </div>
    <!-- ===========Cart End=========== -->


    <!-- ===========menu category=========== -->
    <div id="body-overlay" class="body-overlay"></div>
    <div class="menu-argo-sidebar-area" id="menu-argo-sidebar-area">
        <span class="side-sidebar-close-btn"><i class="las la-times"></i></span>

        <div class="tab-pane" id="cate">
            <div class="left-category xargo-sub">
                <ul class="categories">
                    @foreach ($categories_with_products_in_stock as $category)
                        @if (is_null($category->parent_id))
                            <li>
                                <a
                                    href="{{ route('products.category', ['id' => $category->id, 'slug' => slug($category->name)]) }}">
                                    @php echo $category->icon @endphp {{ $category->name }}
                                </a>
                                <div class="cate-icon">
                                    <i class="las la-angle-down"></i>
                                </div>

                                @if ($category->allSubcategories->count() > 0)
                                    <ul class="sub-category">
                                        @foreach ($category->allSubcategories as $subcategory)
                                            @include($activeTemplate . 'partials.menu_subcategories', [
                                                'subcategory' => $subcategory,
                                            ])
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
    <!-- ===========menu category End=========== -->


    <!-- ===========Wishlist=========== -->
    <div class="cart-sidebar-area" id="wish-sidebar-area">
        <div class="top-content">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ getImage('assets/images/logoIcon/logo_2.png', '183x54') }}" alt="@lang('logo')">
            </a>
            <span class="side-sidebar-close-btn"><i class="las la-times"></i></span>
        </div>
        <div class="bottom-content">
            <div class="cart-products wish-products">

            </div>
        </div>
    </div>
    <!-- ===========Wishlist End=========== -->

    <!-- Header Section Ends Here -->
    <div class="dashboard-menu before-login-menu d-flex flex-wrap justify-content-center flex-column">
        <span class="side-sidebar-close-btn"><i class="las la-times"></i></span>
        @guest
            <div class="login-wrapper py-5 px-4">
                <h4 class="subtitle cl-white">@lang('My Account')</h4>
                <form method="POST" action="{{ route('user.loginPost') }}" class="sign-in-form">
                    @csrf
                    <div class="form-group">
                        <label for="login-username">@lang('Username')</label>
                        <input type="text" class="form-control" name="username" id="login-username"
                            value="{{ old('email') }}" placeholder="@lang('Username')">
                    </div>
                    <div class="form-group">
                        <label for="login-pass">@lang('Password')</label>
                        <input type="password" class="form-control" name="password" id="login-pass"
                            placeholder="Contraseña">
                    </div>

                    @php $captcha = getCustomCaptcha('login captcha') @endphp

                    @if ($captcha)
                        <div class="form-group">
                            <label for="password">@lang('Captcha')</label>
                            <input type="text" class="mb-4" name="captcha" autocomplete="off"
                                placeholder="Ingrese el código a continuación">
                            @lang($captcha)
                        </div>
                    @endif

                    <div class="form-group text-center pt-2">
                        <button type="submit" class="cmn-btn-argo">@lang('Login')</button>
                    </div>

                    <div class="pt-2 mb-0">
                        <p class="create-accounts">
                            <a href="{{ route('user.password.request') }}" class="mb-2">¿@lang('Forgot
                                                                                                                                                                                                        Password')?</a>
                        </p>
                        <p class="create-accounts">
                            <span>¿@lang('Don\'t have an account')? <a href="{{ route('user.register') }}"
                                    class="link-color">@lang('Create An Account')</a> </span>
                        </p>
                    </div>
                </form>
            </div>
        @endguest

        @auth
            @include($activeTemplate . 'user.partials.dp')
            <ul class="cl-white">
                @include($activeTemplate . 'user.partials.sidebar')
            </ul>
        @endauth


    </div>

</div>


<script>
    //alert("La resolución de tu pantalla es: " + screen.width + " x " + screen.height)
    let vue = new Vue({
        el: '#header',
        data: {
            search: "",
            timer: null
        },
        methods: {
            autocomplete: function(event) {
                clearTimeout(this.timer);
                const self = this;
                if (this.search.length > 0) {
                    this.timer = setTimeout(function() {
                        axios.get('{{ route('search_bar_home') }}', {
                                params: {
                                    search: self.search
                                }
                            })
                            .then(function(res) {
                                // console.log(res.data);
                                $('#show_search_products_categories').html(res.data);
                                $(".dropdown-content").css('display', 'block');
                            })
                            .catch(function(err) {
                                console.log(err);
                            })
                    }, 1000);
                }
            }
        }
    });
</script>
