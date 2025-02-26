<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOrganizationBranch;
use http\Message;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\Models\Activity;
class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */


    public function store(Request $request)
    {   $validated_details= $request->all();
        //Log::debug($validated_details);
     

       
            DB::beginTransaction();
            try {

            $validator = Validator::make($request->all(), [
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'id_no' => ['nullable', 'string', 'unique:users,id_no'],
                    'staff_no' => ['nullable', 'string', 'unique:users,staff_no'],
                    'phone' => ['required', 'string', 'unique:users,phone'],
                    'second_phone' => ['nullable', 'string', 'unique:users,second_phone'],
                    'email' => ['nullable', 'email', 'unique:users,email'],
                    'phy_address' => ['nullable', 'string', 'max:255'],
                   // 'organization_id' => ['required', 'string', 'max:255'],
                    'password' => ['required', 'string', 'min:3',],
                   // 'role_id' => ['required'],
                    'description' => ['nullable', 'string' ],
                    //'selectedBranches_id' => ['required', 'array', 'min:1'],
                ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }
                LogBatch::startBatch();
                        $user = User::create([
                                'first_name' => $validated_details['first_name'],
                                'last_name' => $validated_details['last_name'],
                                'id_no' => $validated_details['id_no'],
                                'staff_no' => $validated_details['staff_no'],
                                'phone' => $validated_details['phone'],
                                'second_phone' => $validated_details['second_phone'],
                                'email' => $validated_details['email'],
                                'phy_address' => $validated_details['phy_address'],
                                'organization_id' => '',
                                'special_access' => 1,
                                'password' => Hash::make($validated_details['password']),
                          /*  'role_id' => $validated_details['role_id'],*/
                            'description' => $validated_details['description'],
                            ]);

               /* $user->assignRole($validated_details['role_id']);
                        $user_id = $user->id;
                $organization_id =  $user->organization_id;
                        $org_branches = $validated_details['selectedBranches_id'];
                        foreach ($org_branches as $branch) {
                            UserOrganizationBranch::create([
                                'user_id' => $user_id,
                                'branch_id' => $branch,
                                'organization_id' => $organization_id
                            ]);
                        }*/
            LogBatch::endBatch();
            //   $user->assignRole($request->input('roles'));

                event(new Registered($user));
               // return response()->noContent();
              //  return response()->json(['status' => 'success', 'user_id' => $user['id'],'message' =>  'User registered successfully']);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' =>
                        $validated_details['first_name'] . ' '.$validated_details['last_name'] . ' - Added Successful',
                    'user_id' => $user['id']

                ],200);
    }
            catch (\Exception $e) {
                DB::rollBack();
              Log::debug($e);
                return response()->json([
                    'status' => 'error',
                    /*'message' => $e,*/
                    'message' => 'An Error Occured. Contact Adminstrator',
                ], 422);
            }
       
    }

    public function update(Request $request, $user_id): JsonResponse
    {   $validated_details= $request->all();
        unset($validated_details['password']);
    
       
            DB::beginTransaction();
            try {
                $validated = Validator::make($request->all(), [
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'id_no' => ['nullable', 'string', 'unique:users,id_no,' . $user_id],
                    'staff_no' => ['nullable', 'string', 'unique:users,staff_no,' . $user_id],
                   // 'role_id' => ['required'],
                    'phone' => ['required', 'string', 'unique:users,phone,' . $user_id],
                    'second_phone' => ['nullable', 'string', ],
                    'email' => ['nullable', 'email', 'unique:users,email,' . $user_id],
                    'phy_address' => ['nullable', 'string', 'max:255'],
                   // 'organization_id' => ['required', 'string', 'max:255'],
                   // 'selectedBranches_id' => ['required', 'array', 'min:1'],
                    'description' => ['nullable', 'string', ],
                ]);

                if ($validated->fails()) {
                    return response()->json(['status' =>'error', 'errors' => $validated->errors(),]);
                }
                
                LogBatch::startBatch();

                $user = User::withTrashed()->findOrFail($user_id);
                $user->update($validated_details);
            

                LogBatch::endBatch();
                DB::commit();
                return response()->json(['status' => 'success', 'user_id' => $user_id,'message' =>   $user['first_name'].' '.$user['last_name'].' updated successfully']);
            }
            catch (\Exception $e) {
                DB::rollBack();
                Log::debug($e);

        return response()->json([
                    'status' => 'error',
                    'message' => 'An Error Occured. Contact Adminstrator',
                    'errors' => [$e->getMessage()]
                ], 422);
            }
        }
    

    public function destroy($user_id)
    {
        try {
            $user = User::withoutTrashed()->find($user_id);

            if (!$user) {

                return response()->json(['status' => 'error', 'message' =>  'User already deactivated']);
            }
            $user->delete();

            return response()->json(['status' => 'success', 'message' =>  'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }

    public function restore($user_id){

        try {
            $user = User::onlyTrashed()->find($user_id);

            if (!$user) {

                return response()->json(['status' => 'error', 'message' =>  'User already active']);
            }
            $user->restore();

            return response()->json(['status' => 'success', 'message' =>  $user['first_name'].'  '.$user['last_name'].' restored successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }
}
