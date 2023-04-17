<?php

namespace KeycloakApiServices\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KeycloakApiServices\app\Services\KeycloakService;

class SingleSignOnController extends Controller
{
    public $ssoClient;

    public function __construct()
    {
        $this->ssoClient = new KeycloakService();
    }

    /**
     * @return JsonResponse
     */
    public function accessToken()
    {
        return response()->json(['status' => 'success', 'data' => $this->ssoClient->getAccessToken()], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->createUser($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->getUserByUserName($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->updateUserById($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeUserStatus(Request $request): JsonResponse
    {
        $data = $this->ssoClient->changeUserActiveStatus($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->deleteUser($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClient(Request $request): JsonResponse
    {
        $data = $this->ssoClient->getClient($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientRole(Request $request): JsonResponse
    {
        $data = $this->ssoClient->getClientRole($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRealmPublicKey(Request $request): JsonResponse
    {
        $data = $this->ssoClient->getRealmPublicKey($request->realm_name);
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function assignRoleToUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->assignRoleToUser($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserRoles(Request $request): JsonResponse
    {
        $data = $this->ssoClient->getUserRoles($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteRolesFromUser(Request $request): JsonResponse
    {
        $data = $this->ssoClient->deleteRolesFromUser($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $data = $this->ssoClient->resetUserPassword($request->all());
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }
}
