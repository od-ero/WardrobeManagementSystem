<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\DeviceList;
use App\Models\Organization;
use App\Models\OrganizationBranch;
use App\Models\SystemTrailLogin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {

      try {
            $device_code = $request->device_code;
             $branch_code = $request->branch_code;
            $device_id = null;
            $device_name = '';
            $branch_name = '';
            $branch_id = null;
            $organization_name = '';
           $user_active_branches = [];
         if ($device_code && $branch_code) {

                $isBranch = OrganizationBranch::with(['organization'])->where('organization_branches.code', $branch_code)->first();
                if (!$isBranch) {
                   /* return response()->json([
                        'message' => 'Branch not found. Please register your Branch.',
                    ], 404);*/
                    throw ValidationException::withMessages([
                        'message' => 'Branch not found. Please register your Branch .'
                    ]);
                }

                $isDevice = DeviceList::where('device_code', $device_code)->first();
                if (!$isDevice) {
                   /* return response()->json([
                        'message' => 'Device not found. Please register your device.',
                    ], 422);*/
                    throw ValidationException::withMessages([
                        'message' => 'Device not found. Please register your device .'
                    ]);
                }

                $device_name = $isDevice->device_name;
                $device_id =  $isDevice->id;
                $branch_name = $isBranch->name;

                $branch_id =  $isBranch->id;


                $branch_devices = $isDevice ->branches;
             $isBranchAccessible = $isDevice?->branches->doesntContain('id', $branch_id);
                if ($isBranchAccessible) {
                    throw ValidationException::withMessages([
                        'message' => 'Device not allowed to access this branch'
                    ]);
                }

            }
            $request->authenticate();
           $user =  Auth::user();
           $organization_id = '';
       if ($user->special_access == 0){
           $organization_id = $user->organization_id;
              $organization = Organization::find($organization_id);
              if($organization){
                  $organization_name = $organization->name;
              }
              else {
                  $this->destroy(request());
                  throw ValidationException::withMessages([
                      'message' => 'Kindly ensure Your Organization is Active'
                  ]);
              }


           $user_active_branches = $user->branches;
           if ($user_active_branches->isEmpty()) {
               $this->destroy(request());
               throw ValidationException::withMessages([
                   'message' => 'Kindly ensure you have been assigned a branch .'
               ]);
           }
          }



            $request->session()->regenerate();
            SystemTrailLogin::create([
                'session_id'=> $request->session()->getId(),
                'device_id' => $device_id,
                'user_id' => Auth::id(),
                'broswer_details' => '',
                'ip_address' => $request->ip(),
                'branch_id' => $branch_id,
                'user_agent' => $request->userAgent()
            ]);
            $request->session()->put(['login_info' => ['device_code' => $device_code, 'device_name' => $device_name, 'device_id'=> $device_id,  'login_time' => Carbon::now()->format('d/M H:i'), 'branch_code' =>$branch_code, 'branch_name'=>$branch_name ,'organization_id' => $organization_id, 'organization_name'=> $organization_name, 'branch_id' =>$branch_id,  'logged_in_branch_code' => $branch_code], 'organization_id' => $organization_id, 'branch_id' =>$branch_id ]);
            return response()->json('successs login');
            //return response()->noContent();

     }
     catch (ValidationException $e) {


            return response()->json([
               // 'ggggg'=>$e->getMessage(),
            'message' => $e->getMessage(),
            'errors' => $e->errors()
            ], 422);

       }
       catch (\Exception $e) {
            Log::debug($e);
           $this->destroy(request());
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occured. Contact Adminstrator',
                $e
            ], 500);
        }
    }


/*    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();
        return response()->json(['user' => $user , 'message' => 's
        logged in']);

    }*/

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function validateUser(Request $request){
        try {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:3',]
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ]);
        }
        // Get the currently authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([ 'status' => 'error', 'message' => 'Invalid User']);
        }

        // Check if the provided password matches the logged-in user's password
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'success',

                'message' =>' Thank You '. $user->first_name . '  ' . $user->last_name

            ],200);
        }

        return response()->json([ 'status' => 'error',
            'errors' => [
                'password' => [
                                'incorrect password'
                ]
            ],
            'message' => 'Incorrect password.']);
        }
        catch (\Exception $e) {
            Log::debug($e);
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occured. Contact Adminstrator',

                ],
             422);
        }
    }
}
