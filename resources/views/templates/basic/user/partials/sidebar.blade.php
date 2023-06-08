<li>
    <a href="{{ route('user.home') }}" class="{{ menuActive('user.home') }}"> <i class="las la-home"></i>
    	{{-- @lang('Dashboard') --}}
    	Vista Principal
	</a>
</li>
<li>
    <a href="{{ route('user.profile-setting') }}" class="{{ menuActive('user.profile-setting') }}"><i class="las la-user-alt"></i>
    	{{-- @lang('Profile') --}}
    	Perfil
	</a>
</li>

<li>
    <a href="{{ route('user.deposit.history') }}" class="{{ menuActive('user.deposit.history') }}"><i class="las la-money-bill-wave"></i>
    	{{-- @lang('Payment Log') --}}
    	Registro de Pagos
	</a>
</li>

<li>
    <a href="{{route('user.orders', 'all')}}" class="{{ menuActive('user.orders') }}"><i class="las la-list"></i>
    	{{-- @lang('Order Log') --}}
    	Registro de Pedidos
	</a>
</li>

<li>
    <a href="{{ route('user.logout') }}"><i class="la la-sign-out"></i>
    	{{-- @lang('Sign Out') --}}
    	Cerrar Sesión
	</a>
</li>
