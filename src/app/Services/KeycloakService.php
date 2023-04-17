<?php

namespace KeycloakApiServices\app\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


class KeycloakService
{
    public string $token;

    protected string $baseUrl;

    protected array $realm;

    protected array $client;

    protected array $params;

    /**
     * Initialization: Initializing basic config with global variables to be used in all the functions
     * AccessToken: Getting access token of Keycloak admin
     */
    public function __construct()
    {
        $this->realm['verify'] = config('keycloakapiservices.realm.name');
        $this->baseUrl = env('KEYCLOAK_BASE_URL');
        $this->realm['name'] = config('keycloakapiservices.realm.name');
        $this->realm['clientId'] = config('app.realm.clientId');
        $this->realm['endpoint'] = env('KEYCLOAK_REALM_ENDPOINT');
        $this->client['endpoint'] = env('KEYCLOAK_CLIENTS_ENDPOINT');
        $this->client['roleEndpoint'] = env('KEYCLOAK_ROLES_ENDPOINT');
        $this->getAccessToken();
        $this->params = [
            'roles' => '/roles',
            'users' => '/users',
            'client_role_mappings' => '/role-mappings/clients/'
        ];
    }

    /**
     * Function to get Keycloak admin access token to do api calls
     * @return array
     */
    public function getAccessToken(): array
    {
        try {
            $client = $this->httpClient(true);
            $response = $client->post($this->baseUrl . '/realms/master/protocol/openid-connect/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('KEYCLOAK_ADMIN_CLIENT_ID'),
                    'username' => env('KEYCLOAK_ADMIN_USERNAME'),
                    'password' => env('KEYCLOAK_ADMIN_PASSWORD'),
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->token = $data['access_token'];

            return ['message' => 'Access token created successfully', 'token_data' => $data, 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak access token: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Common Function to create object for Client class which can be used in all other functions
     * @param false $baseUrl
     * @return Client
     */
    public function httpClient(bool $baseUrl = false): Client
    {
        if ($baseUrl) {
            $httpClient = new Client(['verify' => $this->realm['verify']], [
                'base_uri' => $this->baseUrl,
            ]);
        } else {
            $httpClient = new Client(['verify' => $this->realm['verify']]);
        }
        return $httpClient;
    }

    /**
     * Function to create new realm for a newly onboard client in Platformization
     * @param $realmName
     * @return array
     * @throws GuzzleException
     */
    public function createRealm($realmName): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'realm' => $realmName,
                    'enabled' => true,
                ],
            ]);
            if ($response->getStatusCode() !== 201) {
                return $this->failedMessage('Failed to create Keycloak realm: ', $response);
            }
            return ['message' => 'Realm created successfully', 'realm' => $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak realm: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Common function sending request header in all other functions
     * @return array[]
     */
    public function requestHeader(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Common function for returning message when the given action is failed to complete
     * @param $message
     * @param $response
     * @return array | [ArrayShape(['message' => "string", 'statusCode' => ""])]
     */
    public function failedMessage($message, $response): array
    {
        return ['message' => $message . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
    }

    /**
     * Function to Create new Keycloak client for a given realm
     * @param $request
     * @throws GuzzleException
     */
    public function createClient($request)
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->client['endpoint'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'clientId' => $request['client_name'],
                    'enabled' => true,
                    'protocol' => 'openid-connect',
                ],
            ]);

            if ($response->getStatusCode() !== 201) {
                return $this->failedMessage('Failed to create Keycloak client: ', $response);
            }
            return ['message' => 'Client for realm created successfully', 'client' => $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak client: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to create a role for a given realm
     * @param $request
     * @throws GuzzleException
     */
    public function createRealmRole($request)
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->client['roleEndpoint'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'name' => $request['name'],
                    'description' => $request['description'],
                    // 'name' => $request->name,
                    // 'description' => $request->description,
                ],
            ]);

            if ($response->getStatusCode() !== 201) {
                return $this->failedMessage('Failed to create Keycloak realm role: ', $response);
            }
            return ['message' => 'Role for realm created successfully', 'client' => $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak realm role: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to create a role for a given Keycloak client
     * @param $request
     * @throws GuzzleException
     */
    public function createClientRole($request)
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->client['endpoint'] . '/' . $request['client_id'] . $this->params['roles'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'name' => $request['name'],
                    'description' => $request['description'],
                    //'composite' => $compositeRoles,
                    // 'name' => $request->name,
                    // 'description' => $request->description,
                ],
            ]);

            if ($response->getStatusCode() !== 201) {
                return $this->failedMessage('Failed to create Keycloak client role: ', $response);
            }
            return ['message' => 'Role for client created successfully', 'client' => $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak client role: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to create a new user for given realm(Platformization Client)
     * @param $request
     * @return array|ResponseInterface
     * @throws GuzzleException
     */
    public function createUser($request): array|ResponseInterface
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'username' => $request['first_name'],
                    'email' => $request['email'],
                    'firstName' => $request['first_name'],
                    'lastName' => $request['last_name'],
                    'enabled' => true,
                    'emailVerified' => true,
                    'credentials' => [
                        [
                            'type' => 'password',
                            'value' => $request['password'],
                            'temporary' => false
                        ]
                    ]
                ]
            ]);
            if ($response->getStatusCode() !== 201) {
                return ['message' => 'Failed to create Keycloak user: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'User created successfully', 'data' => $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to create Keycloak user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get user by username from a specific realm
     * @param $request
     * @return mixed
     * @throws GuzzleException
     */
    public function getUserByUserName($request): mixed
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'], [
                'headers' => $this->requestHeader(),
                'query' => [
                    'username' => $request['username']
                ]
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return ['message' => 'Failed to get Keycloak user: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'User data retrieved', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get user by user's Keycloak id from a specific realm
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function getUserById($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'], [
                'headers' => $this->requestHeader(),

            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return ['message' => 'Failed to get Keycloak user: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'User data retrieved', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to update user by user's Keycloak id for a specific realm
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function updateUserById($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->put($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'email' => $request['email'],
                    'firstName' => $request['first_name'],
                    'lastName' => $request['last_name'],
                    'username' => $request['first_name'],
                ]
            ]);
            if (!in_array($response->getStatusCode(), [201, 204])) {
                return ['message' => 'Failed to update Keycloak user: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'User profile updated successfully', 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to update Keycloak user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get a client of a realm by realm name and client's Keycloak unique id
     * @param $request
     * @return mixed
     * @throws GuzzleException
     */
    public function getClient($request): mixed
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . '/clients?clientId=' . $request['client_id'], [
                'headers' => $this->requestHeader()
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return ['message' => 'Failed to get Keycloak client: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'Client data retrieved', 'client' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak client: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get a client roles of a realm by realm name and client's Keycloak unique id
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function getClientRole($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . '/clients' . '/' . $request['client_id'] . $this->params['roles'], [
                'headers' => $this->requestHeader()
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return ['message' => 'Failed to get Keycloak client role: ' . $response->getBody()->getContents(), 'statusCode' => $response->getStatusCode()];
            }
            return ['message' => 'Client role data retrieved', 'roles' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak client: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get realm by realm name
     * @param $realmName
     * @return mixed
     * @throws GuzzleException
     */
    public function getRealm($realmName): mixed
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $realmName, [
                'headers' => $this->requestHeader(),
            ]);
            return ['message' => 'Realm data retrieved', 'realm' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get realm: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get a realm roles of a realm by realm name
     * @param $realmName
     * @return mixed
     * @throws GuzzleException
     */
    public function getRealmRole($realmName): mixed
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $realmName . $this->params['roles'], [
                'headers' => $this->requestHeader()
            ]);
            if ($response->getStatusCode() !== 201) {
                return $this->failedMessage('Failed to get Keycloak realm role: ', $response);
            }
            return ['message' => 'Realm roles data retrieved', 'roles ' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak realm role: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get a realm public key by realm name which can be used for user authentication
     * @param $realmName
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function getRealmPublicKey($realmName): StreamInterface
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $realmName . '/keys', [
                'headers' => $this->requestHeader(),
            ]);
            return ['message' => 'Realm pulic key retrieved', 'realm ' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get Keycloak realm public key: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to assign realm's client roles to a user by user's unique id and client's unique id
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function assignRoleToUser($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'] . $this->params['client_role_mappings'] . $request['client_id'], [
                'headers' => $this->requestHeader(),
                'json' => $request['multiple_role']
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to assign roles user', $response);
            }
            return ['message' => 'Roles assigned to user successfully', 'roles' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to assign roles user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to assign realm roles to a user by user's unique id
     * @param $request
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function assignRealmRoleToUser($request): ResponseInterface
    {
        try {
            $client = $this->httpClient();
            $response = $client->post($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'] . '/role-mappings/realm', [
                'headers' => $this->requestHeader(),
                'json' => $request['multiple_role']
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to assign role to Keycloak user: ', $response);
            }
            return ['message' => 'Roles assigned to user successfully', 'roles' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to assign role to Keycloak user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get realm's client roles of a user by user's unique id and client's unique id
     * @param $request
     * @return mixed
     * @throws GuzzleException
     */
    public function getUserRoles($request): mixed
    {
        try {
            $client = $this->httpClient();
            $response = $client->get($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'] . $this->params['client_role_mappings'] . $request['client_id'], [
                'headers' => $this->requestHeader(),
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to get user roles: ', $response);
            }
            return ['message' => 'User role retrieved', 'roles' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to get user roles: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to delete realm's client roles of a user by user's unique id and client's unique id
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function deleteRolesFromUser($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->delete($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'] . $this->params['client_role_mappings'] . $request['client_id'], [
                'headers' => $this->requestHeader(),
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to deleting roles: ', $response);
            }
            return ['message' => 'All roles deleted successfully', 'roles' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to deleting roles: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to get user by user's unique id
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function deleteUser($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->delete($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'], [
                'headers' => $this->requestHeader(),

            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Error deleting user: ', $response);
            }
            return ['message' => 'User deleted successfully', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to delete user: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to update user password by user's unique id
     * @param $request
     * @return array
     * @throws GuzzleException
     */
    public function resetUserPassword($request): array
    {
        try {
            $client = $this->httpClient();
            $response = $client->put($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'] . '/reset-password', [
                'headers' => $this->requestHeader(),
                'json' => [
                    'type' => 'password',
                    'value' => $request['password'],
                    'temporary' => false
                ]
            ]);

            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to changing user password: ', $response);
            }
            return ['message' => 'Password updated successfully', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to changing user password: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to update user profile data by user's unique id
     * @param $request
     * @throws GuzzleException
     */
    public function updateUserProfile($request)
    {
        try {
            $client = $this->httpClient();
            $response = $client->put($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'email' => $request['email'],
                    'firstName' => $request['first_name'],
                    'lastName' => $request['last_name'],
                    'username' => $request['first_name'],
                ]
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to updating user profile: ', $response);
            }
            return ['message' => 'User profile updated successfully', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Failed to updating user profile: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to update email configuration for a realm
     * @param $request
     * @throws GuzzleException
     */
    public function updateEmailConfig($request)
    {
        try {
            $client = $this->httpClient();
            $response = $client->put($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'smtpServer' => [
                        'host' => $request['host'],
                        'port' => $request['port'],
                        'replyTo' => $request['reply_to'],
                        'from' => $request['from_email'],
                        'envelopeFrom' => $request['envelope_from'],
                    ],
                ]
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to updating user profile: ', $response);
            }
            return ['message' => 'Realm email config successfully', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Error updating realm email config: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * Function to change user's active status
     * @param $request
     * @throws GuzzleException
     */
    public function changeUserActiveStatus($request): ResponseInterface
    {
        try {
            $client = $this->httpClient();
            $response = $client->put($this->baseUrl . $this->realm['endpoint'] . '/' . $request['realm_name'] . $this->params['users'] . '/' . $request['user_id'], [
                'headers' => $this->requestHeader(),
                'json' => [
                    'enabled' => $request['status'],
                ],
            ]);
            if (!in_array($response->getStatusCode(), [200, 201, 204])) {
                return $this->failedMessage('Failed to update user active status: ', $response);
            }
            return ['message' => 'User active status updated successfully', 'user' => json_decode($response->getBody()->getContents()), 'statusCode' => $response->getStatusCode()];
        } catch (Exception $e) {
            return ['message' => 'Error updating user active status: ' . $e->getMessage(), 'statusCode' => $e->getCode()];
        }
    }

    /**
     * @throws GuzzleException
     */
    public function automaticRealmClientCreate()
    {
        $realmName = 'myrealm-ems14';
        $baseUrl = 'https://dev-auth.isportz.co/admin/realms/';

        // Define the role
        $role = [
            'name' => 'myrole',
            'description' => 'My Role Description',
        ];

        $role1 = [
            "name" => "admin",
            "description" => "Admin Role"
        ];


        // Define the client and its redirect URI
        $clientDetails = [
            'clientId' => 'myclient',
            'enabled' => true,
            'protocol' => 'openid-connect',
            'rootUrl' => 'http://localhost:8000',
            'baseUrl' => 'http://localhost:8000',
            'redirectUris' => [
                'http://localhost:8000',
                'http://localhost:8000/*',
            ],
            'webOrigins' => [
                'http://localhost:8000',
            ],
            'frontchannelLogout' => true,
            'directAccessGrantsEnabled' => true,
            // 'logoutUrl' => 'http://localhost:8000/logout',
        ];

        // Define the client role
        $clientRole = [
            'name' => 'myclientrole',
            'description' => 'My Client Role Description',
        ];

        // Define the composite role
        // $compositeRole = [
        //     'name' => 'mycompositerole',
        //     'description' => 'My Composite Role Description',
        //     'clientRoleMappings' => [
        //         [
        //             'id' => 'myclientrole-id',
        //             'name' => 'myclientrole',
        //         ]
        //     ],
        // ];

        // Define the SMTP server configuration
        $smtpServer = [
            "enabled" => true,
            "passwordPolicy" => "hashAlgorithm(password(8), salt)",
            "recoveryEmailAsUsername" => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'replyTo' => 'youremail@gmail.com',
            'from' => 'your_email@gmail.com',
            'envelopeFrom' => 'your_email@gmail.com',
        ];

        // Define the realm and its properties
        $realm = [
            'realm' => $realmName,
            'enabled' => true,
            'displayName' => 'My Realm',
            'smtpServer' => [$smtpServer],
            'roles' => [$role],
            'clients' => [$clientDetails],
            //'clientRoles' => [$clientRole],
            //'roles' => [$compositeRole],
        ];

        $token = $this->getAccessToken();

        // Send the request to create the realm
        $client = $this->httpClient();
        $response = $client->post($this->baseUrl . $this->realm['endpoint'], [
            'headers' => $this->requestHeader(),
            'json' => [
                'realm' => $realmName,
                'enabled' => true,
                'displayName' => 'My Realm',
                'smtpServer' => $smtpServer,
                'clients' => [$clientDetails],
                'resetPasswordAllowed' => true,
                "accessTokenLifespan" => 1800,
                "accessCodeLifespan" => 3000,
                // 'ssoSessionIdleTimeout' => 1800, // 30 minutes
                // 'ssoSessionMaxLifespan' => 7200, // 2 hours
                // 'loginTimeout' => 900, // 15 minutes
                //'roles' => [$role],
                //'clientRoles' => [$clientRole],
            ],
        ]);

        // Check the response status
        if ($response->getStatusCode() == 201) {
            echo 'Realm created successfully';
        } else {
            echo 'Failed to create realm';
        }

    }
}
