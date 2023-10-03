<?php

use SimpleProctoring\Authentication\AzureAuthentication;

$auth = new AzureAuthentication(
    $authUrl,
    $clientId,
    $clientSecret,
    $redirectUri,
    $scope,
    $responseType,
    $nonce,
    $state
);

$auth->authenticate()
?>