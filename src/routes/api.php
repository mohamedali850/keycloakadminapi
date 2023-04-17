<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use KeycloakApiServices\App\Http\Controllers\SingleSignOnController;

//Get Keycloak Access Token
Route::get('get-access-token', [SingleSignOnController::class, 'accessToken']);
//Get Client and Roles
Route::get('get-client', [SingleSignOnController::class, 'getClient']);
Route::get('get-client-roles', [SingleSignOnController::class, 'getClientRole']);
//User CRED
Route::post('create-user', [SingleSignOnController::class, 'createUser']);
Route::post('update-user', [SingleSignOnController::class, 'updateUser']);
Route::get('get-user', [SingleSignOnController::class, 'getUser']);
Route::post('change-user-password', [SingleSignOnController::class, 'changePassword']);
Route::post('change-user-status', [SingleSignOnController::class, 'changeUserStatus']);
Route::get('delete-user', [SingleSignOnController::class, 'deleteUser']);
//User Roles
Route::post('assign-user-roles', [SingleSignOnController::class, 'assignRoleToUser']);
Route::post('delete-user-roles', [SingleSignOnController::class, 'deleteRolesFromUser']);
Route::get('get-user-roles', [SingleSignOnController::class, 'getUserRoles']);
