<div class="sidebar capsule--rounded bg_img overlay--dark" data-background="{{getImage('assets/admin/images/sidebar/2.jpg','400x800')}}"
     >
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{route('admin.dashboard')}}" class="sidebar__main-logo"><img
                    src="{{getImage(imagePath()['logoIcon']['path'] .'/logo_2.png')}}" alt="@lang('image')"></a>
            <a href="{{route('admin.dashboard')}}" class="sidebar__logo-shape"><img
                    src="{{getImage(imagePath()['logoIcon']['path'] .'/favicon.png')}}" alt="@lang('image')"></a>
            <button type="button" class="navbar__expand"></button>
        </div>

        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            <ul class="sidebar__menu">

                <li class="sidebar-menu-item {{menuActive('admin.dashboard')}}">
                    <a href="{{route('admin.dashboard')}}" class="nav-link ">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('Dashboard')</span>
                    </a>
                </li>
                
                <!-- Superadmin -->
                @if( (Auth::guard('admin')->user()->role_id == 3) ) 
                    @include('admin.partials.nav-admin', [
                            'banned_users_count'=>$banned_users_count, 
                            'email_unverified_users_count'=>$email_unverified_users_count,
                            'sms_unverified_users_count'=>$sms_unverified_users_count,
                            'pending_ticket_count'=>$pending_ticket_count,
                            'pending_deposits_count'=>$pending_deposits_count,
                            'pending_orders_count'=>$pending_orders_count,
                            'processing_orders_count'=>$processing_orders_count,
                            'dispatched_orders_count'=>$dispatched_orders_count
                        ])
                @endif 

                <!-- Moderador -->
                @if( (Auth::guard('admin')->user()->role_id == 2) ) 
                    @include('admin.partials.nav-moderador', [
                            'userlog' => $userlog,
                            'banned_users_count'=>$banned_users_count, 
                            'email_unverified_users_count'=>$email_unverified_users_count,
                            'sms_unverified_users_count'=>$sms_unverified_users_count,
                            'pending_ticket_count'=>$pending_ticket_count,
                            'pending_deposits_count'=>$pending_deposits_count,
                            'pending_orders_count'=>$pending_orders_count,
                            'processing_orders_count'=>$processing_orders_count,
                            'dispatched_orders_count'=>$dispatched_orders_count
                        ])
                @endif 

            </ul>


        </div>
    </div>
</div>
<!-- sidebar end -->