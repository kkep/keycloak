<?php

CModule::AddAutoloadClasses('keycloak', [
    'Session' => 'classes/general/Session.php',
    'HttpClient' => 'classes/general/HttpClient.php',
    'KeycloakWeb' => 'classes/general/KeycloakWeb.php',
    'KeycloakHandler' => 'classes/general/KeycloakHandler.php',
    'KeycloakWebGuard' => 'classes/general/KeycloakWebGuard.php',
    'KeycloakAccessToken' => 'classes/general/KeycloakAccessToken.php',
    'KeycloakWebUserProvider' => 'classes/general/KeycloakWebUserProvider.php',
]);