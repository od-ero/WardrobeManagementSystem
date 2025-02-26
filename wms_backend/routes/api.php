<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\WardrobeController;
use App\Http\Controllers\SytermTrailLoginController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationBranchesController;
use App\Http\Controllers\RolesAndPermissionsController;
USE App\Http\Controllers\UserOrganizationBranchController;
use App\Http\Controllers\WardrobeCategoryController;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('/users/list-login-names/{device_code}/{branch_code}', [UsersController::class, 'listLoginNames'])->name('users.list-login-names');

});





Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/validate-user', [AuthenticatedSessionController::class, 'validateUser']) ->name('ASC.validateUser');

    Route::get('/users/list-organization-usersNames', [UsersController::class, 'listOrganizationUsersNames'])->name('users.listOrganizationUsersNames');


    Route::get('/organizations/list-active', [OrganizationController::class, 'listActive'])->name('organization.list-active');
    Route::get('/organizations/list-active-names', [OrganizationController::class, 'listActiveNames'])->name('organization.list-active-names');
    Route::get('/organizations/list-inactive', [OrganizationController::class, 'listInactive'])->name('organization.list-inactive');
    Route::post('/organizations/store', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/show/{id}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/update', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/destroy/{id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::delete('/organizations/restore/{id}', [OrganizationController::class, 'restore'])->name('organizations.restore');

    Route::get('/user-organization-branches/get-my-branches/{id}', [UserOrganizationBranchController::class, 'getMyBranches'])->name('UOBC.get-my-branches');

    Route::post('/reports/login_logs/set-branch/', [SytermTrailLoginController::class, 'setBranch'])->name('SystemTrailLogin.setBranch');


   // Route::middleware(['ensure_organization_id'])->group(function () {

        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

        // Route::get('/users/show/{id}', [UsersController::class, 'show'])->name('users.show');

        Route::delete('/users/destroy/{id}', [RegisteredUserController::class, 'destroy'])->name('ruc.destroy');
        Route::delete('/users/restore/{id}', [RegisteredUserController::class, 'restore'])->name('ruc.restore');

        Route::post('/users/update/{id}', [RegisteredUserController::class, 'update'])->name('RUC.update');

        Route::get('/users/list-active', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/list-inactive', [UsersController::class, 'indexInactive'])->name('users.listInactive');
        Route::get('/users/show/{id}', [UsersController::class, 'show'])->name('users.show');
        //->middleware(['ensure_branch_id']);

       



       // Route::middleware(['ensure_branch_id'])->group(function () {
            /*  Route::get('/user', function (Request $request) {
                    return $request->user();
                 // return Auth::user();
                });*/

            Route::get('/wardrobe/index', [WardrobeController::class, 'index'])->name('wardrobe.index');
            Route::get('/wardrobe/index-inactive', [WardrobeController::class, 'indexInactive'])->name('wardrobe-inactive');
            Route::post('/wardrobe/store', [WardrobeController::class, 'store'])->name('wardrobe.store');
            Route::get('/wardrobe/show/{id}', [WardrobeController::class, 'show'])->name('wardrobe.show');
            Route::post('/wardrobe/update', [WardrobeController::class, 'update'])->name('wardrobe.update');
            Route::delete('/wardrobe/destroy/{id}', [WardrobeController::class, 'destroy'])->name('wardrobe.destroy');
            Route::delete('/wardrobe/restore/{id}', [WardrobeController::class, 'restore'])->name('wardrobe.restore');

            Route::get('/wardrobe-categories/list-active', [WardrobeCategoryController::class, 'index'])->name('wardropeCategory.index');
           // Route::get('/reports/login_logs/show/{id}', [WardrobeCategoryController::class, 'show'])->name('SystemTrailLogin.show');




            Route::get('/organization-branches/list-active/{id}', [OrganizationBranchesController::class, 'listActive'])->name('organization-branches.list-active');
            Route::get('/organization-branches/list-inactive/{id}', [OrganizationBranchesController::class, 'listInactive'])->name('organization-branches.list-inactive');
            Route::get('/organization-branches/list-active-names', [OrganizationBranchesController::class, 'listActiveNames'])->name('organization-branches.listActiveNames');
            Route::post('/organization-branches/store', [OrganizationBranchesController::class, 'store'])->name('organization-branches.store');
            Route::get('/organization-branches/show/{id}', [OrganizationBranchesController::class, 'show'])->name('organization-branches.show');
            Route::post('/organization-branches/update', [OrganizationBranchesController::class, 'update'])->name('organization-branches.update');
            Route::delete('/organization-branches/destroy/{id}', [OrganizationBranchesController::class, 'destroy'])->name('organization-branches.destroy');
            Route::delete('/organization-branches/restore/{id}', [OrganizationBranchesController::class, 'restore'])->name('organization-branches.restore');

            Route::get('/user-organization-branches/allocate-user-branches/{id}', [UserOrganizationBranchController::class, 'allocateUserBranches'])->name('UOBC.allocateUserBranches');

            Route::post('/user-organization-branches/store' , [UserOrganizationBranchController::class, 'store'])->name('UOBC.store');
            Route::get('/user-organization-branches/show/{id}' , [UserOrganizationBranchController::class, 'show'])->name('UOBC.show');



            Route::get('/roles-permissions/list-active', [RolesAndPermissionsController::class, 'listActive'])
                ->name('r_p.listActive');
            //->middleware('permission:list-role');

            Route::post('/roles-permissions/store', [RolesAndPermissionsController::class, 'storeRoles'])
                ->name('r_p.storeRoles');

            Route::post('/roles-permissions/store-permissions', [RolesAndPermissionsController::class, 'storePermissions'])
                ->name('r_p.storePermissions');
            //->middleware('can:create-role');

            Route::get('/roles-permissions/show/{id}', [RolesAndPermissionsController::class, 'showRole'])
                ->name('r_p.showRole');
            //->middleware('permission:view-role');

            Route::get('/create-permission', [RolesAndPermissionsController::class, 'createPermission'])
                ->name('r_p.createpermission');



            Route::get('/edit-role/{id}', [RolesAndPermissionsController::class, 'editRole'])
                ->name('r_p.editRole')->middleware('permission:edit-role');

            Route::post('/roles-permissions/update', [RolesAndPermissionsController::class, 'updateRole'])
                ->name('r_p.updateRole')->middleware('permission:edit-role');

            Route::get('/delete-role/{id}', [RolesAndPermissionsController::class, 'destroyRole'])
                ->name('r_p.deleteRole')->middleware('permission:destroy-role');

            Route::get('/edit-permission-level/{id}', [RolesAndPermissionsController::class, 'editPermissionLevel'])
                ->name('r_p.editPermissionLevel')->middleware('permission:edit-employee-role');

            Route::post('/update-permission-level', [RolesAndPermissionsController::class, 'updatePermissionLevel'])
                ->name('r_p.UpdatePermissionLevel')->middleware('permission:edit-employee-role');
            Route::get('/roles-permissions/list-all-permission-names', [RolesAndPermissionsController::class, 'listAllPermissionsNames'])
                ->name('r_p.list-all-permissions-names');

            Route::get('/roles-permissions/list-all-roles-names', [RolesAndPermissionsController::class, 'listAllRolesNames'])
                ->name('r_p.list-all-roles-names');
            //->middleware('permission:edit-employee-role');
        });
  //  });
//});



