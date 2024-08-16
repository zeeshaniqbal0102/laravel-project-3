<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiQueryFilter;
use App\Http\Controllers\Controller;
use App\Models\BanningAttributes;
use App\Models\CreditAdjustment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    
    const PAGINATION_LIMIT = 10;

    const MODULE_NAME = 'clients';

    /**
     * Permissions / Gates
     */
    const CAN_ACCESS = 'access ' . self::MODULE_NAME;
    const CAN_CREATE = 'create ' . self::MODULE_NAME;
    const CAN_EDIT = 'edit ' . self::MODULE_NAME;
    const CAN_DELETE = 'delete ' . self::MODULE_NAME;
    const CAN_SAVE = 'save ' . self::MODULE_NAME;
    const CAN_UPDATE = 'update ' . self::MODULE_NAME;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');
        
        $model = new User();
        $helper = new ApiQueryFilter($model, $request);

        if($request->pagination) {

            $q = $helper->searchAndSort(array(
                'id',
                'first_name',
                'last_name',
                'email',
                'username',
                'gender',
                'created_at',
                'credits',
                'is_status'
            ));

            return $q->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'username',
                'gender',
                'created_at',
                'credits',
                'is_status'
            ])->orderByDesc('created_at')->where('deleted', 0)->role('Client')->paginate(self::PAGINATION_LIMIT)->withQueryString();
            
        } else {
            return response()->json([
                'error' => false, 
                'message' => 'Clients List', 
                'result' => User::all()
            ]);
        }
    }

    public function view()
    {
        abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.clients.clients')->with([
            'userType' => $this->getUserType(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(!Auth::user()->can(self::CAN_CREATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.clients.add')->with([
            'userType' => $this->getUserType(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_if(!Auth::user()->can(self::CAN_SAVE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');
        
        $request->validate([
            'username' => 'required|string|min:4|unique:users,username',
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'gender' => 'required|string',
            'dob_year' => 'required|string',
            'dob_month' => 'required|string',
            'dob_day' => 'required|string',
            'password' => 'min:6|required_with:password_confirm|same:password_confirm|string',
            'email' => 'required|email|min:6|unique:users,email|max:255',
            'address' => 'present|nullable|string|max:255',
            'state' => 'present|nullable|string|max:255',
            'contact_no' => 'present|nullable|string|max:15',
            'zip' => 'present|nullable|string|max:255',
            'credits' => 'required|numeric'
        ]);
        
        $data = (object) $request->all();
        
        $data->is_activated = 1;
        $data->ip_address = request()->ip();
        $data->dob = date('Y-m-d', strtotime("$request->dob_year-$request->dob_month-$request->dob_day"));

        $data->password = Hash::make($request->password);
        $data->pw_plain_txt = Crypt::encryptString($request->password);

        $data = (array) $data;

        $store = User::create($data);
        $store->assignRole('Client');

        if(!$store) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Failure to add'
                ], 500)
                : redirect()->back()->with('error', 'Failure to add');
        }

        return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Added successfully'
                ], 201)
                : redirect()->back()->with('success', 'Added successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($t, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($t, $client)
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.clients.edit')->with([
            'userType' => $this->getUserType(),
            'row' => (object) User::where('id',$client)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $t,  $id)
    {
        abort_if(!Auth::user()->can(self::CAN_UPDATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        if($id) {

            $request->validate([
                'username' => 'required|string|min:4|unique:users,username,' . $id,
                'last_name' => 'required|string',
                'first_name' => 'required|string',
                'gender' => 'required|string',
                'dob_year' => 'required|string',
                'dob_month' => 'required|string',
                'dob_day' => 'required|string',
                'email' => 'required|email|min:6|max:255|unique:users,email,' . $id,
                'address' => 'present|nullable|string|max:255',
                'state' => 'present|nullable|string|max:255',
                'contact_no' => 'present|nullable|string|max:15',
                'zip' => 'present|nullable|string|max:255',
                'credits' => 'required|numeric'
            ]);
            
            $data = (object) $request->except(['_method', '_token', 'dob_month', 'dob_day', 'dob_year']);
            
            $data->is_activated = 1;
            $data->dob = date('Y-m-d', strtotime("$request->dob_year-$request->dob_month-$request->dob_day"));

            $update = User::where('id', $id)->update((array) $data);
            
            if(!$update) {
                return $request->wantsJson()
                    ? new JsonResponse([
                        'error' => false,
                        'message' => 'Failure to update'
                    ], 500)
                    : redirect()->back()->with('error', 'Failure to updae');
            }
    
            return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Updated successfully'
                ], 200)
                : redirect()->back()->with('success', 'Updated successfully');

        } 

        return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'No id'
                ], 500)
                : redirect()->back()->with('error', 'No id found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_DELETE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        if(!$id) {
            abort(500, "Missing id");
        }

        if(!User::where('id', $id)->update([
            'deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]) ) {
            return request()->wantsJson()
                ? new JsonResponse([
                    'error' => true,
                    'message' => 'No id'
                ], 500)
                : redirect()->back()->with('error', 'No id found');
        } else {
            return request()->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Record deleted successfully'
                ], 200)
                : redirect()->back()->with('error', 'Record deleted successfully');
        }
    }

    public function update_password_view($t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.clients.update_password')->with([
            'userType' => $this->getUserType(),
            'row' => User::select(['id', 'username', 'pw_plain_txt'])->where('id', $id)->first(),
        ]);
    }

    public function ban_client_view($t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        $user = (object) User::where('id', $id)->first();

        $attr = BanningAttributes::where('username', $user->username)->first();

        $user->reason = $attr ? $attr->reason : '';

        return view('admin.modules.clients.ban')->with([
            'userType' => $this->getUserType(),
            'row' => $user,
        ]);
    }

    public function adjust_credit_view($t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.clients.adjust_credit')->with([
            'userType' => $this->getUserType(),
            'row' => User::where('id', $id)->first(),
        ]);
    }

    /**
     * Ban client with reason
     * 
     * @param int $id
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function ban_client(Request $request, $t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_UPDATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        if(!$id) {
            abort(500, "Missing ID");
        } 

        $request->validate([
            'reason' => 'required'
        ]);

        $user = User::where('id', $id)->firstOrFail();

        User::where('id', $id)->update([
            'is_status' => 1,
        ]);


        $update = BanningAttributes::create([
            'username' => $user->username,
            'email' => $user->email,
            'dob' => $user->dob,
            'ip_address' => $user->ip_address,
            'reason' => $request->reason
        ]);
       
        if(!$update) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Failure to update'
                ], 500)
                : redirect()->back()->with('error', 'Failure to updae');
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'error' => false,
                'message' => 'Updated successfully'
            ], 200)
            : redirect()->back()->with('success', 'Client banned successfully');
        
    }

    /**
     * Adjust client's credit
     * 
     * @param int $id
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function adjust_credit(Request $request, $t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_UPDATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        if(!$id) {
            abort(500, "Missing ID");
        } 

        $request->validate([
            'type' => 'required',
            'credits' => 'required'
        ]);

        $user = User::where('id', $id)->firstOrFail();

        $new_credits = $user->credits;

        switch($request->type) {
            case 'addition': 

                $new_credits = (float) $user->credits + floatval($request->credits);

                break;

            case 'deduction': 

                $new_credits = $user->credits - floatval($request->credits);

                break;
        }

        User::where('id', $id)->update([
            'credits' => ($new_credits > 0) ? $new_credits : 0 
        ]);

        $update = CreditAdjustment::create([
            'value' => $request->credits,
            'type' => $request->type,
            'user_id' => $id
        ]);
       
        if(!$update) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Failure to update'
                ], 500)
                : redirect()->back()->with('error', 'Failure to updae');
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'error' => false,
                'message' => 'Updated successfully'
            ], 200)
            : redirect()->back()->with('success', 'Credit adjustment is successful');
        
    }

    /**
     * Update client's password
     * 
     * @param int $id
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update_password(Request $request, $t, $id)
    {
        abort_if(!Auth::user()->can(self::CAN_UPDATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        if(!$id) {
            abort(500, "Missing ID");
        } 

        $request->validate([
            'password' => 'min:6|required_with:password_confirm|same:password_confirm|string',
        ]);

        $update = User::where('id', $id)->update([
            'password' => Hash::make($request->password),
            'pw_plain_txt' => Crypt::encryptString($request->password)
        ]);

       
        if(!$update) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Failure to update'
                ], 500)
                : redirect()->back()->with('error', 'Failure to updae');
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'error' => false,
                'message' => 'Updated successfully'
            ], 200)
            : redirect()->back()->with('success', 'Client password updated successfully');
        
    }

    public function getUserType()
    {
        return get_user_type()['userType'];
    }
}
