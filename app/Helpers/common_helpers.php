<?php

use App\Models\User;
use App\Models\BirthdateBan;
use App\Models\BanningAttributes;
use App\Models\WhitelistedUsers;
use Illuminate\Support\Facades\Auth;

if(!function_exists('get_user_type')) {

    /**
     * Returns an array of user type
     *
     * @return Array
     */
    function get_user_type(): array
    {
        if(Auth::check()) {
            $user = User::where('id', Auth::user()->id)->with(['roles:name,id'])->first();

            $userType = count($user->roles) ? $user->roles[0]
                                                ? $user->roles[0]->name 
                                                : 'default'
                                            : 'default';
            
            $role = $userType != 'default' ? $userType : 'Client';
            
            return [
                'userType' => $user->is_admin ? 'admin' : $userType,
                'role' => $role,
            ];
        } else {
            return [
                'userType' => 'default',
                'role' => 'default'
            ];
        }
        
    }
    
}

if(!function_exists('check_banned')) {

    /**
     * Check if user is banned
     *
     * @param int $id
     *
     * @return bool
     */

     function check_banned(int $id): bool
     {
         $user = User::where([
             'id' => $id,
             'is_status' => 1,
             'deleted' => 0
         ])->exists();
 
         if(!$user) 
             return false;
         else 
             return true;
      }
}

if(!function_exists('check_dob_gender_ban')) {

    /**
    * Check if user dob matched banned dob's
    *
    * @param \App\Models\User $user
    *
    * @return \void
    */
    function check_dob_gender_ban(User $user): bool
    {
        if(WhitelistedUsers::where([
            'user_id' => $user->id,
            'status' => 1
        ])->exists()) {
            return false;
        }

        if(BirthdateBan::where([
            'birthdate' => $user->dob,
            'gender' => $user->gender
        ])->exists()) {

            User::where('id', $user->id)->update([
                'is_status' => 1,
            ]);

            BanningAttributes::updateOrCreate([
                'username' => $user->username,
                'email' => $user->email,
                'dob' => $user->dob,
                'ip_address' => $user->ip_address,
                'reason' => "Banned due to match on birthdate and gender"
            ]);

            return true;

        } else {

            return false;

        }
    }

}