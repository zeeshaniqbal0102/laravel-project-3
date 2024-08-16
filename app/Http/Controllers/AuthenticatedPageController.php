<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Repositories\ApiRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedPageController extends Controller
{
    public function __construct( ApiRepositoryInterface $apiRepository)
    {
        $this->apiRepository = $apiRepository;
    }

    public function dashboard()
    {
        $this->apiRepository->init();

        $temp     = $this->getUserType();
        $role     = $temp['role'];
        $userType = $temp['userType'];
        
        $client = ['userType' => strtolower($userType)];

        if(request()->get('reader')) {
            $client = array(
                'userType' => strtolower($userType),
                'reader' => request()->get('reader'),
            );
        } 

        if(request()->get('success_registration')) {
            $client = array(
                'userType' => strtolower($userType),
                'success_registration' => 1
            );
        }

        switch($role) {
            case 'Reader': 
                return redirect()->route('readerdashboard.index', ['userType' => strtolower($userType) ]);
            break;

            case 'Admin': 
                return redirect()->route('admindashboard.index', ['userType' => strtolower($userType)]);
            break;

            case 'Client': 
                return redirect()->route('clientdashboard.index', $client);
            break;
        }
    }

    public function profile()
    {
        $this->apiRepository->init();
        
        $temp     = $this->getUserType();
        $role     = $temp['role'];
        $user     = User::where('id', Auth::user()->id)->with(['roles:id,name', 'categories'])->first();

        switch($role) {
            case 'Reader': 

                $user->profile_changes = $user->profile_changes ? true : false;

                $categories = Category::all();

                return view('profile.reader', compact('user', 'categories'));
                
            break;

            case 'Admin': 
                return view('profile.admin', compact('user'));
            break;

            case 'Client': 

                $user->dob_day = date('d', strtotime($user->dob));
                $user->dob_year = date('Y', strtotime($user->dob));
                $user->dob_month = date('m', strtotime($user->dob));

                return view('profile.client', compact('user'));
            break;
        }
    }

    private function getUserType(): array
    {
        return get_user_type();
    }
}
