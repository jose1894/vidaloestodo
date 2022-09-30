<?php
namespace App\Http\Controllers\Admin;

use App\Deposit;
use App\Transaction;
use App\User;
use App\UserLogin;
use App\Roles;
use App\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Order;
use Auth;
use App\PlanUsers;

class ManageUsersController extends Controller
{
    public function allClients()
    {
        $page_title = 'Todos los Clientes';
        $empty_message = 'No se encontraron clientes';


        $users = User::with(
            [
                'roles.modules:id,name','plan_users'
            ]
        )->latest()->paginate(getPaginate());


        $roles = Roles::with(['modules'])->get();

        return view('admin.users.list', compact('page_title', 'empty_message', 'users', 'roles'));
    }

    public function allUsers()
    {
        $page_title = 'Todos los Usuarios';
        $empty_message = 'No se encontraron usuarios';

        $moderators = Admin::where('role_id', '2')->get();

        $clientes = User::with(
            [
                'roles.modules:id,name','plan_users'
            ]
        )->get();//latest()->paginate(getPaginate());//->get();//->paginate(getPaginate());

        $users = $moderators->concat($clientes)->sortByDesc('created_at')->paginate(getPaginate());

        //$users = $moderators->merge($clientes)->sortByDesc('created_at')->paginate(getPaginate()); //concateno a los moderadores con los clientes       
        //return $users;
        // dd($users);

        $roles = Roles::with(['modules'])->get();

        return view('admin.users.list', compact('page_title', 'empty_message', 'users', 'roles'));
    }

    public function allUsersPrime()
    {
        $page_title = 'Todos los Usuarios Prime';
        $empty_message = 'No se encontraron usuarios';

        $moderators = Admin::where('role_id', '2')->get();

        $clientes = User::with(
            [
                'roles.modules:id,name','plan_users'
            ]
        )->whereHas('plan_users')->latest()->paginate(getPaginate());

        $users = $clientes;
        // $users = $moderators->merge($clientes)->sortByDesc('created_at')->paginate(getPaginate()); //concateno a los moderadores con los clientes       

        $roles = Roles::with(['modules'])->get();

        return view('admin.users.list_prime', compact('page_title', 'empty_message', 'users', 'roles'));
    }

    public function activeUsers()
    {
        $page_title = 'Clientes Activos';
        $empty_message = 'No se encontró ningún cliente activo';
        $users = User::active()->latest()->paginate(getPaginate());
        $roles = Roles::with(['modules'])->get();
        return view('admin.users.list', compact('page_title', 'empty_message', 'users','roles'));
    }

    public function bannedUsers()
    {
        $page_title = 'Clientes prohibidos';
        $empty_message = 'No se encontró ningún cliente prohibido';
        $users = User::banned()->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('page_title', 'empty_message', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $page_title = 'Enviar correo electrónico a clientes no verificados';
        $empty_message = 'No se encontró ningún cliente no verificado por correo electrónico';
        $users = User::emailUnverified()->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('page_title', 'empty_message', 'users'));
    }
    public function emailVerifiedUsers()
    {
        $page_title = 'Enviar correo electrónico a clientes verificados';
        $empty_message = 'No se encontró ningún cliente verificado por correo electrónico';
        $users = User::emailVerified()->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('page_title', 'empty_message', 'users'));
    }


    public function smsUnverifiedUsers()
    {
        $page_title = 'Clientes no verificados de SMS';
        $empty_message = 'No se encontró ningún cliente no verificado de SMS';
        $users = User::smsUnverified()->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('page_title', 'empty_message', 'users'));
    }
    public function smsVerifiedUsers()
    {
        $page_title = 'Clientes verificados por SMS';
        $empty_message = 'No se encontró ningún cliente verificado por SMS';
        $users = User::smsVerified()->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('page_title', 'empty_message', 'users'));
    }



    public function search(Request $request)
    {
        // dd($request->all());
        $search = $request->search;
        $users = User::where(function ($user) use ($search) {
            $user->where('username', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('mobile', 'like', "%$search%")
                ->orWhere('firstname', 'like', "%$search%")
                ->orWhere('lastname', 'like', "%$search%");
        });

        // if (is_null($scope)) {
            // $scope = 'list';
        // }
        $page_title = '';
        // switch ($scope) {
        //     case 'active':
        //         $page_title .= 'Activo ';
        //         $users = $users->where('status', 1);
        //         break;
        //     case 'banned':
        //         $page_title .= 'Prohibido';
        //         $users = $users->where('status', 0);
        //         break;
        //     case 'emailUnverified':
        //         $page_title .= 'Correo electrónico no certificado ';
        //         $users = $users->where('ev', 0);
        //         break;
        //     case 'smsUnverified':
        //         $page_title .= 'SMS no verificado ';
        //         $users = $users->where('sv', 0);
        //         break;
        //     case 'list':
        //         $page_title .= 'SMS no verificado ';
        //         break;
        // }
        $roles = Roles::with(['modules'])->get();
        $users = $users->paginate(getPaginate());
        $page_title .= 'Buscar Cliente - ' . $search;
        $empty_message = 'No se encontraron resultados de búsqueda';
        return view('admin.users.list', compact('page_title', 'search', 'empty_message', 'users','roles'));
    }


    public function detail($id)
    {
        $page_title         = 'Detalles del Cliente';
        $user               = User::findOrFail($id);
        $totalDeposit       = Deposit::where('user_id', $user->id)->where('status',1)->sum('amount');
        $totalTransaction   = Transaction::where('user_id', $user->id)->count();
        $totalOrders        = Order::where('user_id', $user->id)->where('payment_status', '!=', 0)->count();
        return view('admin.users.detail', compact('page_title', 'user','totalDeposit','totalTransaction', 'totalOrders'));
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'firstname' => 'required|max:60',
            'lastname' => 'required|max:60',
            'email' => 'required|email|max:160|unique:users,email,' . $user->id,
        ]);

        if ($request->email != $user->email && User::whereEmail($request->email)->whereId('!=', $user->id)->count() > 0) {
            $notify[] = ['error', 'El Email ya existe.'];
            return back()->withNotify($notify);
        }
        if ($request->mobile != $user->mobile && User::where('mobile', $request->mobile)->whereId('!=', $user->id)->count() > 0) {
            $notify[] = ['error', 'El número de teléfono ya existe.'];
            return back()->withNotify($notify);
        }

        $user->update([
            'mobile' => $request->mobile,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'address' => [
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'country' => $request->country,
            ],
            'direction' => $request->address,
            'status' => $request->status ? 1 : 0,
            'ev' => $request->ev ? 1 : 0,
            'sv' => $request->sv ? 1 : 0
        ]);

        $notify[] = ['success', 'Los datos del cliente se actualizaron correctamente'];
        return redirect()->back()->withNotify($notify);
    }


    public function userLoginHistory($id)
    {
        $user = User::findOrFail($id);
        $page_title = 'Historial de Sesión del cliente - ' . $user->username;
        $empty_message = 'No se encontraron datos de inicio de sesión de clientes.';
        $login_logs = $user->login_logs()->latest()->paginate(getPaginate());
        return view('admin.users.logins', compact('page_title', 'empty_message', 'login_logs'));
    }

    public function loginHistory(Request $request)
    {
        if ($request->search) {
            $search = $request->search;
            $page_title = 'Buscar Historial de Sesión de - ' . $search;
            $empty_message = 'No se encontraron resultados de búsqueda.';
            $login_logs = UserLogin::whereHas('user', function ($query) use ($search) {
                $query->where('username', $search);
            })->latest()->paginate(getPaginate());
            return view('admin.users.logins', compact('page_title', 'empty_message', 'search', 'login_logs'));
        }
        $page_title = 'Historial de inicio de sesión del cliente';
        $empty_message = 'No se encontraron datos de inicio de sesión de clientes.';
        $login_logs = UserLogin::latest()->paginate(getPaginate());
        return view('admin.users.logins', compact('page_title', 'empty_message', 'login_logs'));
    }

    public function loginIpHistory($ip)
    {
        $page_title = 'Iniciar sesión por - ' . $ip;
        $login_logs = UserLogin::where('user_ip',$ip)->latest()->paginate(getPaginate());
        $empty_message = 'No se encontraron datos de inicio de sesión de clientes.';
        return view('admin.users.logins', compact('page_title', 'empty_message', 'login_logs'));

    }



    public function showEmailSingleForm($id)
    {
        $user = User::findOrFail($id);
        $page_title = 'Correo Enviado a: ' . $user->username;
        return view('admin.users.email_single', compact('page_title', 'user'));
    }

    public function sendEmailSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        $user = User::findOrFail($id);
        send_general_email($user->email, $request->subject, $request->message, $user->username);
        $notify[] = ['success', $user->username . ' recibirá un correo electrónico en breve.'];
        return back()->withNotify($notify);
    }

    public function transactions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $page_title = 'Buscar Transactions del Cliente : ' . $user->username;
            $transactions = $user->transactions()->where('trx', $search)->with('user')->latest()->paginate(getPaginate());
            $empty_message = 'Sin transacciones';
            return view('admin.reports.transactions', compact('page_title', 'search', 'user', 'transactions', 'empty_message'));
        }
        $page_title = 'Transacciones del Cliente : ' . $user->username;
        $transactions = $user->transactions()->with('user')->latest()->paginate(getPaginate());
        $empty_message = 'Sin transacciones';
        return view('admin.reports.transactions', compact('page_title', 'user', 'transactions', 'empty_message'));
    }

    public function deposits(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->search) {
            $search = $request->search;
            $page_title = 'Buscar pagos de clientes : ' . $user->username;
            $deposits = $user->deposits()->where('trx', $search)->latest()->paginate(getPaginate());
            $empty_message = 'Sin pagos todavía';
            return view('admin.deposit.log', compact('page_title', 'search', 'user', 'deposits', 'empty_message'));
        }

        $page_title = 'Pago del Cliente : ' . $user->username;
        $deposits = $user->deposits()->latest()->paginate(getPaginate());
        $empty_message = 'Sin pagos';
        return view('admin.deposit.log', compact('page_title', 'user', 'deposits', 'empty_message'));
    }


    public function showEmailAllForm()
    {
        $page_title = 'Enviar correo electrónico a todos los clientes';
        return view('admin.users.email_all', compact('page_title'));
    }

    public function sendEmailAll(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        foreach (User::where('status', 1)->cursor() as $user) {
            send_general_email($user->email, $request->subject, $request->message, $user->username);
        }

        $notify[] = ['success', 'Todos los clientes recibirán un correo electrónico en breve.'];
        return back()->withNotify($notify);
    }

    public function desactivateUser(Request $request, $id){
        $user = User::findOrFail($id);
        $notify = [];
        if($user->status == 1){
            $user->status = 0;
            $notify[] = ['success', 'Usuario inhabilitado con éxito'];
        }
        else if($user->status == 0){
            $user->status = 1;
            $notify[] = ['success', 'Usuario activado con éxito'];
        }
        $user->save();

        return redirect()->back()->withNotify($notify);
    }

}
