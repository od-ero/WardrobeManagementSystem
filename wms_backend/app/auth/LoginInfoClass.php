<?php

namespace App\auth;

use Carbon\Carbon;

class LoginInfoClass
{
    /**
     * Create a new class instance.
     */
   /* public function __construct()
    {
        //
    }*/
    static function setLoginInfo()
    {
       session()->put(['login_info' => ['device_code' => $device_code, 'device_name' => $device_name, 'device_id' => $device_id, 'login_time' => Carbon::now()->format('d/M H:i'), 'branch_code' => $branch_code, 'branch_name' => $branch_name, 'organization_id' => $organization_id, 'organization_name' => $organization_name, 'branch_id' => $branch_id], 'organization_id' => $organization_id, 'branch_id' => $branch_id, de]);
        return response()->json('successs login');
    }

    static function updateLoginInfo()
    {
        session()->put(['login_info' => ['device_code' => $device_code, 'device_name' => $device_name, 'device_id' => $device_id, 'login_time' => Carbon::now()->format('d/M H:i'), 'branch_code' => $branch_code, 'branch_name' => $branch_name, 'organization_id' => $organization_id, 'organization_name' => $organization_name, 'branch_id' => $branch_id], 'organization_id' => $organization_id, 'branch_id' => $branch_id, de]);
        return response()->json('successs login');
    }
}
