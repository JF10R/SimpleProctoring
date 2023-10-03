<?php

declare(strict_types=1);
namespace SimpleProctoring\Authentication;

use SimpleProctoring\Interfaces\AuthenticationInterface;
use SimpleProctoring\User;
use SimpleProctoring\Group;

class AzureAuthentication implements AuthenticationInterface {
    private string $authUrl;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $scope;
    private string $responseType;
    private string $nonce;
    private string $state;

    public function __construct(
        string $authUrl,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $scope,
        string $responseType,
        string $nonce,
        string $state
    ) {
        $this->authUrl = $authUrl;
        $this->tenantId = $tenantId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
        $this->responseType = $responseType;
        $this->nonce = $nonce;
        $this->state = $state;
    }

    public function authenticate(): bool {
        if (isset($_GET['code'])) {
            $tokenUrl = "https://login.microsoftonline.com/{tenant-id}/oauth2/v2.0/token";
            $code = $_GET['code'];
            $grantType = "authorization_code";

            $tokenParams = [
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret,
                "redirect_uri" => $this->redirectUri,
                "code" => $code,
                "grant_type" => $grantType
            ];

            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $tokenData = json_decode($response, true);

            // Check if a valid token was received
            if (isset($tokenData['access_token'])) {
                // Add your Azure SSO authentication code here
                // If the user is successfully authenticated, return true
                return true;
            } else {
                // If the authorization code is not set, redirect the user to the Azure login page
        $authUrl = $this->getAuthorizationUrl();
        header("Location: $authUrl");
        exit();
            }
        } else {
            
        }
    }

    public function getUser(): ?User {
        if (isset($_SESSION['access_token'])) {
            $tokenUrl = "https://graph.microsoft.com/v1.0/me";
            $accessToken = $_SESSION['access_token'];
    
            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $accessToken"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
    
            $userData = json_decode($response, true);
    
            if (isset($userData['id']) && isset($userData['mail']) && isset($userData['givenName']) && isset($userData['surname'])) {
                $groups = null;
    
                if (isset($userData['memberOf'])) {
                    $groups = [];
    
                    foreach ($userData['memberOf'] as $group) {
                        if (isset($group['id']) && isset($group['displayName'])) {
                            $groups[] = new Group($group['id'], $group['displayName']);
                        }
                    }
                }
    
                return new User($userData['id'], $userData['mail'], $userData['givenName'], $userData['surname'], $groups);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getAuthorizationUrl(): string {
        $clientId = $this->clientId;
        $redirectUri = $this->redirectUri;
        $scopes = $this->scope;
    
        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => bin2hex(random_bytes(16)),
            'nonce' => bin2hex(random_bytes(16)),
        ]);
    
        return "https://login.microsoftonline.com/$this->tenantId/oauth2/v2.0/authorize?$query";
    }
}