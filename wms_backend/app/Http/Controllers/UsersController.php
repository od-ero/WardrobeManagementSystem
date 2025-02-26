<?php

namespace App\Http\Controllers;

use App\Models\DeviceList;
use App\Models\OrganizationBranch;
use App\Models\Permission;
use App\Models\SystemTrailLogin;
use App\Models\User;
use App\Models\UserOrganizationBranch;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Pest\Laravel\json;
class UsersController extends Controller
{

    public function listLoginNames ($branch_code, $device_code)
    {
try {
   $device = DeviceList::where('device_code', $device_code)->first();
  $branch = OrganizationBranch::where('code', $branch_code)->first();
    $org_branch_id = $branch->id;
     if (!$device || !$branch) {
         return response()->json(['status' => 'error',
             "errors" => [
                 "email" => ["The email field is required."],
                 "password" => ["The password must be at least 8 characters."]
             ],
             'message' => 'Invalid device or branch', 'data' => []], 404);
     }

     if ($device->organization_id !== $branch->organization_id) {
         return response()->json(['status' => 'error', 'message' => 'The URL does not exist', 'data' => []], 404);
     }

    /* $data = User::withoutTrashed()
         ->where('special_access', 0)
         ->whereHas('branches', function ($query) use ($org_branch_id) {
             $query->where('branch_id', $org_branch_id);
         })
         ->get([
             DB::raw("CONCAT(first_name, ' ', last_name) as label"),
             'id as value'
         ])
         ->toArray();

     return response()->json($data);*/

    /*  $data = User::withoutTrashed()
          ->where('special_access', 0)
          ->whereHas('branches', function ($query) use ($org_branch_id) {
              $query->where('branch_id', $org_branch_id);
          })
       ->select('first_name as label','id as value')
          ->get()
          ->toArray();*/
    $data = User::withoutTrashed()
        ->where('special_access', 0)
         ->whereHas('branches', function ($query) use ($org_branch_id) {
             $query->where('organization_branches.id', $org_branch_id);
         })
        ->select(
            DB::raw("CONCAT(first_name, ' ', last_name) as label"), // Concatenate first_name and last_name
            'id as value'
        )
        ->get();


    return response()->json($data);
}
    catch (\Exception $e) {

        Log::debug($e);
        return response()->json([
            'status' => 'error',
            'message' => 'An Error Occured. Contact Adminstrator',
            'errors' => [
                'alert_error' => [
                    'An Error Occured. Contact Adminstrator'
                ]
            ]
        ], 422);
    }

    }

    public function listOrganizationUsersNames(){

        $data = User::withoutTrashed()
            ->where('special_access', 0)
            ->where('organization_id', session('organization_id'))
            ->select(
                DB::raw("CONCAT(first_name, ' ', last_name) as label"), // Concatenate first_name and last_name
                'id as value'
            )
            ->get();

        return response()->json($data);
    }
    public function index(Request $request)
    {
        $data = User::withoutTrashed()
        // ->withCount('branches')
        // ->where('special_access',0)
        // ->where('organization_id',session('organization_id', Auth::user()->organization_id))
        //->whereNot('id', Auth::id())
        ->get();
        return response()->json($data);

    }

    public function indexInactive(Request $request)
    {
        $data = User::onlyTrashed()
            // ->withCount('branches')
            // ->where('special_access',0)
            // ->where('organization_id',session('organization_id', Auth::user()->organization_id))
            //->whereNot('id', Auth::id())
            ->get();
        return response()->json($data);
    }


    public function show($id){
        $user = User::find($id);
        // $roles = $user->getRoleNames();
        // $user->ke_role = $roles;
        // $user->team_id = $team_id;
        return response()->json($user);

    }
public function getAuthenthicatedUser(Request $request){

  $user = $request->user();
    setPermissionsTeamId(session('branch_id'));
    $user->unsetRelation('roles')->unsetRelation('permissions');
   $permissions = ['gggg'=>'kkkk'];
    if($user->hasRole('super-admin') || $user->special_access == 1){
        $permissions = Permission::all();
    }
    else{
       // $permissions = $user->getAllPermissions();
       // $permissions = $user->getAllPermissions();
       $permissions = $user->getPermissionsViaRoles();
    }

  $permissions = $permissions->map(function ($permission) {
        return [
            'name' => $permission->name,
          //  'label' => $permission->display_name,
            'module' => $permission->module,
        ];
    })->groupBy('module');
        $login_info = $request->session()->get('login_info');

        $user->login_info =$login_info;
        $user->permissions =  $permissions;


        return $user;
}

  /*  public function getAuthenthicatedUser(Request $request)
    {
        setPermissionsTeamId(session('branch_id')); // If using Spatie's multi-tenancy

        $user = Auth::user();

        // Ensure roles and permissions are loaded
        $user->load('roles.permissions');

        if ($user->hasRole('super-admin') || $user->special_access == 1) {
            $permissions = Permission::all();
        } else {
            $permissions = $user->getAllPermissions();

            // Fallback: If permissions are empty, fetch manually
            if ($permissions->isEmpty()) {
                $permissions = Permission::whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('id', $user->roles->pluck('id'));
                })->get();
            }
        }

        // Format permissions by module
        $permissions = $permissions->map(function ($permission) {
            return [
                'name' => $permission->name,
                'module' => $permission->module,
            ];
        })->groupBy('module');

        // Attach login info
        $user->login_info = $request->session()->get('login_info');
        $user->permissions = $permissions;
        $user->team_id = session('branch_id');

        return $user;
    }*/


    /*public function  listAssignedBranches($user_id){
        $user_branches = UserOrganizationBranch::where('user_id', $user_id)
            ->with('branch') // Eager load the branch relationship
            ->get()
            ->pluck('branch');
        return response()->json($user_branches);

    }*/
}
