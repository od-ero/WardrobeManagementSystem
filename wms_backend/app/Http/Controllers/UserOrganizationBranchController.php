<?php

namespace App\Http\Controllers;

use App\Models\OrganizationBranch;
use App\Models\User;
use App\Models\UserOrganizationBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;
class UserOrganizationBranchController extends Controller
{
    public function allocateUserBranches(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $userbranches = $user->branches;
        $auth_user = Auth::user();
        //$auth_user_branches = $auth_user->getAccessibleBranches(); // Collection of branches
        $auth_user_branches = $auth_user->branches;
        /*$auth_user_branches_roles = $auth_user_branches->flatMap(function ($branch) {
            return $branch->roles; // Retrieve roles for each branch
        });*/
        $auth_user_branches_roles = $auth_user_branches->map(function ($branch) use ($user, &$user_branches) {
            return [
                'value' => $branch->id,
                'label' => $branch->name,
                'roles' => $branch->roles->map(function ($role) use ($user) {
                    return [
                        'value' => $role->id,
                        'label' => $role->name,

                    ];
                })
            ];
        });


        $assigned_branches = $userbranches->map(function ($userOrganizationBranch) use ($user, $id) {
                                            setPermissionsTeamId($userOrganizationBranch->id);
                                             $user->unsetRelation('roles')->unsetRelation('permissions');
                                        $role_id = '';
                                        $user_roles = $user->roles->first();
                                            if ($user_roles){
                                                $role_id = $user_roles->id;
                                            }
                                        return [
                                            'branch_id' => $userOrganizationBranch->id,
                                            'role_id' => $role_id,
                                            'description' => $userOrganizationBranch->pivot->description ?? '',

                                        ];
                                    });


       /*             $branches = OrganizationBranch::with('roles')
                                                    ->where('Organization_id', session('organization_id'));
                                   if($auth_user->special_access == 0) {
                                        $branches -> whereIn('id', $auth_user_branches_ids);
                                    }
                                    $branches= $branches   ->get()
                                        ->map(function ($branch) use ($user, &$user_branches) {
                                            // Set the permissions team ID for each branch
                                          // setPermissionsTeamId($branch->id);

                                            return [
                                                'value' => $branch->id,
                                                'label' => $branch->name,
                                               // 'allocated' => $user_branches->contains('branch_id', $branch->id),
                                                'roles' => $branch->roles->map(function ($role) use ($user) {
                                                    return [
                                                        'value' => $role->id,
                                                        'label' => $role->name,
                                                      //  'allocated' => $user->roles->contains('id', $role->id)
                                                    ];
                                                })
                                            ];
                                        });*/


        return response()->json(['assigned_branches'=>$assigned_branches, 'branches'=>$auth_user_branches_roles]);
    }


    public function store(Request $request)
    {   $validated_details= $request->all();

        Log::debug($validated_details);

            DB::beginTransaction();
            try {
                $validator = Validator::make($validated_details, [
                    'user_id'=>['required'],
                    'selectedBranchesWithRoles' => ['required', 'array', 'min:1'],
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ]);
                }
            $user_id = $validated_details['user_id'];
                $organization_id = session('organization_id');
                $selectedBranchesWithRoles = $validated_details['selectedBranchesWithRoles'];
                LogBatch::startBatch();
               /* $org_branches = $validated_details['selectedBranches_id'];
                foreach ($org_branches as $branch) {
                  UserOrganizationBranch::create([
                        'user_id' => $user_id,
                        'branch_id' => $branch,
                        'organization_id' => $organization_id
                    ]);
                }*/
                $syncData = [];
                $user = User::findOrFail($user_id);
                foreach ($selectedBranchesWithRoles as $item) {
                    $syncData[$item['branch_id']] = [
                        'role_id' => $item['role_id'],
                        'description' => $item['description'],
                        'organization_id' => $organization_id
                    ];
                    setPermissionsTeamId($item['branch_id']);
                    $user->unsetRelation('roles')->unsetRelation('permissions');
                    $user->syncRoles($item['role_id']);
                }

              //  $user->branches()->syncWithoutDetaching($syncData);
                $user->branches()->sync($syncData);


                LogBatch::endBatch();
                //   $user->assignRole($request->input('roles'));


                //  return response()->json(['status' => 'success', 'user_id' => $user['id'],'message' =>  'User registered successfully']);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' =>
                        $user['first_name'] . ' '. $user['last_name'] . ' -Role Added Successful',
                    'user_id' => $user['id']

                ],200);
            }
            catch (\Exception $e) {
                DB::rollBack();
                Log::debug($e);
                return response()->json([
                    'status' => 'error',
                    'message' => $e,/*'message' => 'An Error Occured. Contact Adminstrator',*/
                    'errors' => [
                        'alert_error' => [
                            'An Error Occured. Contact Adminstrator'
                        ]
                    ]
                ], 422);
            }
        }

        public function show($id){

            $user= User::findOrFail($id);
            //$user = Auth::user();
            $userbranches = $user->branches;
            if ($id != Auth::id()) {
                $auth_user = Auth::user();
                $auth_user_branches = $auth_user->branches;
                $auth_user_branches_ids = $auth_user_branches->pluck('id')->toArray();

                $userbranches = $userbranches->filter(function ($branch) use ($auth_user_branches_ids) {
                    return in_array($branch->id, $auth_user_branches_ids);
                })->values(); // Reset the array keys
            }
            $user_full_names = $user->first_name . '  ' . $user->last_name;
           $data = $userbranches->map(function ($userBranch) use ($user, $id) {
                setPermissionsTeamId($userBranch->id);
                $user->unsetRelation('roles')->unsetRelation('permissions');
                $role_name = '';
                $user_roles = $user->roles->first();
                if ($user_roles){
                    $role_name = $user_roles->name;
                }
                return [
                    'branch_id'=> $userBranch->id,
                    'name' => $userBranch->name,
                    'phone' => $userBranch->phone,
                    'role' => $role_name,
                  'description' => $userBranch->pivot?->description ?? '',
                ];
            });
            return response()->json(['user_full_name' => $user_full_names,'userbranches' => $data]);
        }
        public function getMyBranches($organization_id){
            session()->put(['organization_id' => $organization_id]);
            return $this->show(Auth::id());

}

}
