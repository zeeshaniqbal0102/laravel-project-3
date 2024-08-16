<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\CallDetail;
use Auth;

class CallHistoryController extends Controller
{


    const MODULE_NAME = 'call history';

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
    public function index()
    {
        abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');
        $user = Auth()->user();        
        // $start  = Carbon::parse($request->start_date);
        // $end    = Carbon::parse($request->end_date);
        $userType = $user->roles[0]->name;
        switch($userType) {
            case 'Admin':
                $data = CallDetail::where('is_admin_deleted',0)->get();
                return view('admin/histories/call', compact('data'), compact('userType') );
                break;
            case 'Reader':
                $data = CallDetail::where('is_reader_deleted',0)->where('reader_handle', $user->username)->get();       
                return view('reader/history/call', compact('data'), compact('userType') );                
                break;
            case 'Client':
                $data = CallDetail::where('is_client_deleted',0)
                                    ->where('user_id', $user->id)->get();
                return view('client/history/call', compact('data'), compact('userType') );                
                break;
        }
        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth()->user();      
        $role = $user->roles[0]->name;
        switch($role) {
            case 'Admin':
                $field = 'is_admin_deleted';
                break;
            case 'Reader':
                $field = 'is_reader_deleted';
                break;
            case 'Client':
                $field = 'is_client_deleted';
                break;
        }
        $data = CallDetail::where('session_id',$request->id)->update([$field=>1]);
        return response()->json([
            'status' => $data,
            'message' => "Successfully Delete Record."
        ]);
    }
}
