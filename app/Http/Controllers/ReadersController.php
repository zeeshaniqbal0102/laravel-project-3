<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ReadersController extends Controller
{
    const PAGINATION_LIMIT = 10;

    const MODULE_NAME = 'psychic reader';

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

        $readers = array();

        $readers_api = Http::withHeaders(array(
            'Accept' => 'application/json',
            'Authorization' => $request->header("X-VOICE-API-Authorization")
        ))->get(env('API_URL') . 'get_available_profile');

        if(!$readers_api->successful()) {

            if($readers_api->status() == 401) {
                $tokens = $request->user()->tokens;

                foreach($tokens as $token) {
                    $token->revoke();
                }
            }

            $readers_api = (object) $readers_api->json();

            return response()->json([
                'error' => true,
                'message' => $readers_api->message ?? '',
            ]);
        }

        $readers_api = (object) $readers_api->json();

        foreach($readers_api->data as $reader) {

            $reader = (object) $reader;

            $readers[] = (object) [
                'id' => $reader->id,
                'name' => $reader->handle,
                'area_expertise' => $reader->area_expertise,
                'status' => $reader->status
            ];
        }
 
        return response()->json([
            'error' => false, 
            'message' => 'Reader List', 
            'result' => $readers
        ]);
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
            return Banner::when($request->searchkey, function ($q) use ($request) {
                return $q->where('name', $request->searchkey)
                        ->orWhere('name', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('description', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('max_questions', 'like', '%' . $request->searchkey . '%')
                        ->orWhere('rate', 'like', '%' . $request->searchkey . '%');
            })->paginate(self::PAGINATION_LIMIT)->withQueryString();
        } else {
            return response()->json([
                'error' => false, 
                'message' => 'Banner List', 
                'result' => Banner::all()
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
            'image' => 'required|image|mimes:jpeg,png,jpg',
            'title' => 'required|string|unique:banners,title',
            'description' => 'required|string',
        ]);

        $create = $request->all();

        $path = Storage::disk('public')->put('banners', $request->file('image'));

        $store = Banner::create(array_merge($create, ['image_link' => "/storage/$path"]));

        if(!$store) {
            return response()->json([
                'error' => true,
                'message' => 'Failure to save record'
            ], 500);
        }

        return response()->json([
            'error' => false,
            'message' => 'banner saved successfully'
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
                'record' => Banner::where('id',$id)->first(),
                'error' => false,
                'message' => 'Banner information'
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg',
                'title' => 'required|string|unique:banners,title,' . $id,
                'description' => 'required|string',
            ]);

            $update = Banner::where('id', $id)->update($request->all());
            
            if(!$update) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failure to update record'
                ]);
            }
    
            return response()->json([
                'error' => false,
                'message' => 'Banner updated successfully'
            ], 200);

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
