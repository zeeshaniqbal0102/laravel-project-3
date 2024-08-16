<?php

namespace App\Http\Controllers;

use App\Helpers\ApiQueryFilter;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PublicPageText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PublicTextController extends Controller
{
    const PAGINATION_LIMIT = 10;

    const MODULE_NAME = 'public page text';

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
        
        $model = new PublicPageText();
        $helper = new ApiQueryFilter($model, $request);

        if($request->pagination) {
            $q = $helper->searchAndSort(array(
                'id', 'identifier', 'text',
            ));

            return $q->paginate(self::PAGINATION_LIMIT)->withQueryString();
        } else {
            return response()->json([
                'error' => false, 
                'message' => 'Public Page Texts List', 
                'result' => PublicPageText::all()
            ]);
        }
    }

    public function view()
    {
        abort_if(!Auth::user()->can(self::CAN_ACCESS) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.publicpagetexts.publicpagetexts')->with([
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

        return view('admin.modules.publicpagetexts.add_publicpagetext')->with([
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
            'identifier' => 'required|string|unique:public_page_texts,identifier',
            'text' => 'required|string'
        ]);


        $store = PublicPageText::create($request->all());

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
    public function edit($t, $publicpagetext)
    {
        abort_if(!Auth::user()->can(self::CAN_EDIT) && !Auth::user()->is_admin, Response::HTTP_FORBIDDEN, 'Request denied');

        return view('admin.modules.publicpagetexts.edit_publicpagetext')->with([
            'userType' => $this->getUserType(),
            'row' => (object) PublicPageText::where('id',$publicpagetext)->first()
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
                'identifier' => 'required|string|unique:public_page_texts,identifier, ' . $id,
                'text' => 'required|string'
            ]);

            $data = $request->only(['identifier', 'text']);

            $update = PublicPageText::where('id', $id)->update($data);
            
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
                ], 500)
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
        if(!$id) {
            abort(500, "Missing id");
        }

        if(!PublicPageText::where('id', $id)->delete() ) {
            request()->wantsJson()
                ? new JsonResponse([
                    'error' => true,
                    'message' => 'No id'
                ], 500)
                : redirect()->back()->with('error', 'No id found');
        } else {
            request()->wantsJson()
                ? new JsonResponse([
                    'error' => false,
                    'message' => 'Record deleted successfully'
                ], 500)
                : redirect()->back()->with('error', 'Record deleted successfully');
        }
    }

    public function getUserType()
    {
        return get_user_type()['userType'];
    }
}
