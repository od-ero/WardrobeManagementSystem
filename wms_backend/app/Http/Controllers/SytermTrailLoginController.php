<?php

namespace App\Http\Controllers;

use App\Models\DeviceList;
use App\Models\Organization;
use App\Models\OrganizationBranch;
use App\Models\SystemTrailLogin;
use App\Models\UserOrganizationBranch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function Psy\debug;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
class SytermTrailLoginController extends Controller
{
    //

public function setBranch(Request $request)
{


    $branch_id = $request->branch_id;
    $device_name = '';
    $branch_name = '';
    $organization_name = '';
    $session_device_code = '';
    $session_device_name = '';
    $session_device_id= '';
    $session_branch_code = '';
    $organization_id = $request->organization_id;
    $logged_in_user = Auth::user();
    try {

    if ($logged_in_user->special_access == 0) {
        $organization_id = $logged_in_user->organization_id;
    }
       /* $user_active_branch = UserOrganizationBranch::where(['user_id' => $logged_in_user->id, 'branch_id' => $branch_id])->first();*/
        $user_active_branches = $logged_in_user->branches;
        if (count($user_active_branches) == 0) {
            return response()->json([
                'message' => 'Kindly ensure you have been assigned a branch.',
                'status' => 'error'
            ]);
        }
        elseif (count($user_active_branches) == 1){
            $user_active_branch = $user_active_branches[0];
            $branch_id = $user_active_branch->id;
            $branch_name = $user_active_branch->name;
        }

        else{
            $user_active_branch = $user_active_branches->find($branch_id);
           if($user_active_branch) {
               $branch_id = $user_active_branch->id;
               $branch_name = $user_active_branch->name;
           }else{
               return response()->json([
                   'status' => 'error',
                   'message' => 'Branch not found. Please register your Branch.',
               ]);
           }
        }
  //  }

    $organization = Organization::find($organization_id);

    if ($organization) {
        $organization_name = $organization->name;
    }else{

        return response()->json([
            'status' => 'error',
            'message' => 'Organization not found. Please register your Organization.',
        ]);
    }
/*    $isBranch = OrganizationBranch::find($branch_id);
    if (!$isBranch) {
        return response()->json([
            'status' => 'error',
            'message' => 'Branch not found. Please register your Branch.',
        ]);
    } else {
        $branch_name = $isBranch->name;
    }*/
        $system_trail_logs = SystemTrailLogin::with(['device', 'branch'])->where('session_id', $request->session()->getId())->orderBy('created_at','asc')->first();
        $session_device = $system_trail_logs['device'];
        if($session_device){
            $session_device_code = $session_device['device_code'];
            $session_device_name = $session_device['device_name'];
            $session_device_id= $session_device['id'];
        }
        $session_branch = $system_trail_logs['branch'];
        if($session_branch){
            $session_branch_code = $session_branch['code'];
        }
        setPermissionsTeamId($branch_id);

        $logged_in_user->unsetRelation('roles')->unsetRelation('permissions');
    SystemTrailLogin::create([
        'session_id' => $request->session()->getId(),
        'user_id' => Auth::id(),
        'broswer_details' => '',
        'ip_address' => $request->ip(),
        'branch_id' => $branch_id,
        'device_id' => $session_device_id,
        'user_agent' => $request->userAgent()
    ]);


    $request->session()->put([
        'login_info' => [
            'device_code' => $session_device_code,
            'device_name' => $session_device_name,
            'device_id'  => $session_device_id,
            'branch_name' => $branch_name,
            'organization_id' => $organization_id,
            'organization_name' => $organization_name,
            'branch_id' => $branch_id,
            'login_time' => Carbon::parse($system_trail_logs->created_at)->format('d/M H:i'),
            'logged_in_branch_code' => $session_branch_code
            ],
        'organization_id' => $organization_id,
        'branch_id' => $branch_id,

    ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Branch assigned successfully.',
        ]);
}
    catch (\Exception $e) {
        Log::debug($e);

        return response()->json([
            'status' => 'error',
            'message' => 'An Error Occured. Contact Adminstrator',
            $e
        ], 500);
    }


}
    public function list(Request $request){
    // $data = SystemTrailLogin::with(['device','user'])->get();
        $data = SystemTrailLogin::with([
            'device',
            'user',
            'session'
        ])->orderByDesc('created_at')
        ->get();
        return response()->json($data);
    }

    public function show($id){
        $data = SystemTrailLogin::with([
            'device',
            'user',
            'session'
        ])->where('id',$id)
            ->first();
        return response()->json($data);
    }
}
