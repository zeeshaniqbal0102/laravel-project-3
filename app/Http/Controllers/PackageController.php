<?php

namespace App\Http\Controllers;

use App\Helpers\ApiQueryFilter;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PackageController extends Controller
{
    const PAGINATION_LIMIT = 10;

    const MODULE_NAME = 'package';

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
        abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Requed denied');

        $model = new Package();
        $helper = new ApiQueryFilter($model, $request);

        if($request->pagination) {
            $q = $helper->searchAndSort(array(
                'name', 'description', 'max_questions', 'rate'
            ));

            return $q->paginate(self::PAGINATION_LIMIT)->withQueryString();
        } else {
            return response()->json([
                'error' => false, 
                'message' => 'Package List', 
                'result' => Package::all()
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     * For public api purposes
     *
     * @return \Illuminate\Http\Response
     */
    public function index2(Request $request)
    {
        // abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Requed denied');
        if($request->pagination) {
            return Package::when($request->searchkey, function ($q) use ($request) {
                return $q->where('name', $request->searchkey)
                        ->orWhere('name', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('description', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('max_questions', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('rate', 'like', '%' . $request->searchkey . '%');
            })->paginate(self::PAGINATION_LIMIT)->withQueryString();
        } else {
            return response()->json([
                'error' => false, 
                'message' => 'Package List', 
                'result' => Package::all()
            ]);
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
    public function store(Request $request): JsonResponse
    {
        abort_if(!Auth::user()->can(self::CAN_SAVE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Requed denied');

        $request->validate([
            'name' => 'required|string|unique:packages,name',
            'description' => 'required|string',
            'rate' => 'required|numeric',
            'max_questions' => 'required|numeric'
        ]);


        $store = Package::create($request->all());

        if(!$store) {
            return response()->json([
                'error' => true,
                'message' => 'Failure to save record'
            ], 500);
        }

        return response()->json([
            'error' => false,
            'message' => 'package saved successfully'
        ], 201);
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
    public function edit($id): JsonResponse
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Requed denied');

        if($id) {
            return response()->json([
                'record' => Package::where('id',$id)->first(),
                'error' => false,
                'message' => 'package information'
            ]);
        } 

        return response()->json([
            'error' => true,
            'message' => 'Unprocessable entity',
            'errors' => (object) [
                'id' => 'id is required.'
            ]
        ], 401);
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
        abort_if(!Auth::user()->can(self::CAN_UPDATE) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Requed denied');

        if($id) {

            $request->validate([
                'name' => 'required|string|unique:packages,name,' . $id,
                'description' => 'required|string',
                'rate' => 'required|numeric',
                'max_questions' => 'required|numeric'
            ]);

            $update = Package::where('id', $id)->update($request->all());
            
            if(!$update) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failure to update record'
                ]);
            }
    
            return response()->json([
                'error' => false,
                'message' => 'package updated successfully'
            ], 201);

        } 

        return response()->json([
            'error' => true,
            'message' => 'Unprocessable entity',
            'errors' => (object) [
                'id' => 'id is required.'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
