<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\Models\Activity;

class RolesAndPermissionsController extends Controller
{
    public function addRoles(Request $request)//assign super admin to user of given
    {
        //   $role = Role::create([
        //         'name'=> 'Admin'
        //     ]);

        // foreach($request->permission as $permission){
        //     $role->givePermissionTo($permission);
        // }
        //  $role->givePermissionTo('Add-Supplier');
        // foreach($request->users as $user){
        //     $user = User::find($user);
        //     $role->assignRole($role->name);
        // }
        $user = User::find(1);
        $user->assignRole('super-admin');
        dd('success');

    }


    public function listActive(Request $request)
    {

            $user = Auth::user();
            $role = $user->roles->first();
            $role_id = 1;
            if ($role) {
                // Get the role ID
                $role_id = $role->id;
            }

            $roles = Role::with(['branch'])->whereNot('name','super-admin')
                ->get();


        return response()->json($roles);
    }


    public function storeRoles(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $branch_id = session('branch_id');

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($branch_id) {
                        $exists = Role::withTrashed()
                            ->where('name', $value)
                            ->where('team_id', $branch_id)
                            ->exists();
                        if ($exists) {
                            $fail('The name has already been taken for this Branch.');
                        }
                    }
                ],
                'selectedPermissions_id' => ['required', 'array', 'min:1'],
                'description' => ['nullable', 'string']
            ]);

            if ($validator->fails()) {
                return response()->json(['status' =>'error', 'errors' =>  $validator->errors(),]);
            }
            $selected_permissions_id = $request->input('selectedPermissions_id');


            LogBatch::startBatch();
            $role_name = $request->input('name');
            $description = $request->input('description');
              $role = Role::create(['name' => $role_name, 'team_id' => $branch_id, 'description'=>$description, 'guard_name'=>'web']);

                $role->syncPermissions($selected_permissions_id);

            LogBatch::endBatch();
            DB::commit();

            return response()->json(['status' => 'success', 'id' => $role->id, 'message' => $role->name .'
            Role created']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occurred. Contact Administrator',
                'errors' => ['alert_error' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showRole($id): JsonResponse
    {
        $role = Role::with(['branch'])->find($id);
        return response()->json($role);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editRole($encoded_role_id): JsonResponse
    { $id = base64_decode($encoded_role_id);
        $role = Role::find($id);
        $user = Auth::user();
        if ($user->hasRole('super-admin')) {
            $permissions = Permission::get();
        } else {
            $permissions = $user->getAllPermissions();
            $permissions = $permissions->where('grouping_id', '!=', 'permission');
        }
        $permissions = $permissions->groupBy('grouping_id');

        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return response()-> json(['permissions' => $permissions, 'rolePermissions' => $rolePermissions, 'role'=>$role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request): JsonResponse
    {
        $role_details = $request->all();

        $validated = Validator::make($role_details, [
            'role_name' => 'required|unique:roles,name,' .  $role_details['role_id'],
            'permission' =>  ['required', 'array', 'min:1'],
        ]);
        if ($validated->fails()) {

            return response()->json(['status' =>'error', 'message' => $validated->errors()->all()]);
        }

        $role = Role::find($role_details['role_id']);
        $role->name = $request->input('role_name');
        $role->save();

        $permissionsID = array_map(
            function($value) { return (int)$value; },
            $request->input('permission')
        );

        $role->syncPermissions($permissionsID);

        return response()->json(['status' =>'success', 'role_id'=>$role['id'], 'message' => 'Role Edited']);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyRole($encoded_role_id): JsonResponse
    {
        try{
            $role_id = base64_decode($encoded_role_id);
            DB::table("roles")->where('id',$role_id)->delete();
            return response()->json(['status' =>'success', 'message' => 'Role Deleted']);
        }catch (\Exception $e) {

            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }


    public function storePermissions(Request $request){
       /* $permissions = $request->all();

        if ($permissions['no_of_permissions'] > 0) {

            for ($i = 1; $i <= $permissions['no_of_permissions']; $i++) {
                $permission_key = "permission_" . $i;

                if (isset($permissions[$permission_key])) {
                    $created= Permission::create([
                        'name'=> $permissions[$permission_key]
                    ]);
                }

            }
            return response()->json(['status' => 'success', 'message' => 'Permission Created']);

        }else{
            return response()->json(['status' => 'error', 'message' => 'Kindly Enter Permission']);
        }*/
        (new RoleSeeder())->run();
        (new PermissionSeeder())->run();
        return response()->json(['message' => 'Permissions Seeder and Super executed successfully']);
    }

        public function editPermissionLevel($encoded_user_id){
            $user_id = base64_decode($encoded_user_id);
            $user = User::find($user_id);
            $roles = Role::whereNot('name','super-admin')
                ->orderBy('id','DESC')
                ->pluck('name','name');
            $userRole = $user->roles->pluck('name','name')->first();

            return response()->json(['roles'=>$roles , 'userRole'=>$userRole]);

        }

        public function updatePermissionLevel(Request $request){
            $user_detail = $request->all();

            $validated = Validator::make($user_detail, [
                'password' => ['required','current_password'],
                'roles' => ['required'],
            ]);

            if ($validated->fails()) {

                return response()->json(['status' =>'error', 'message' => $validated->errors()->all()]);
            }
            try{
                $user_id = base64_decode($user_detail['user_id']);
                $user = User::find($user_id);
                DB::table('model_has_roles')->where('model_id',$user_id)->delete();

                $user->assignRole($request->input('roles'));
                return response()->json(['status' => 'success', 'message' => 'Permission Level Edited']);
            } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'message' => 'Ann Error Occured']);
            }

        }

public function listAllPermissionsNames(Request $request){


    $user = Auth::user();
    setPermissionsTeamId(session('branch_id'));
    $user->unsetRelation('roles')->unsetRelation('permissions');
    $permissions = [];
    if($user->hasRole('super-admin') || $user->special_access == 1){
        $permissions = Permission::all();
    }
    else{
        $permissions = $user->getAllPermissions();
    }
    $permissions = $permissions->map(function ($permission) {
        return [
            'value' => $permission->id,
            'label' => $permission->display_name,
            'module' => $permission->module,
        ];
    })->groupBy('module');
   /* $permissions = Permission::select('id as value', 'display_name as label', 'module')
        ->get()->groupBy('module');*/
    return response()->json($permissions);
}

    public function listAllRolesNames(Request $request){
        $allRolesExceptSuperadmin = Role::whereNot('name', 'super-admin')->select('id as value', 'name as label')->get();

        return response()->json($allRolesExceptSuperadmin);
    }

}
