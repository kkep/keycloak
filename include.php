<?php

// require_once __DIR__ .'/helper.php';

// require_once __DIR__ .'/lib/autoload.php';

// или

// CModule::AddAutoloadClasses(bx_module_id(),
// [
//     'CClass' => 'lib/class.php',
// ]);

CModule::AddAutoloadClasses('keycloak', [


    'CKeycloakServer' => 'classes/general/keycloak_server.php',
    '__CKeycloakServerDBResult' => 'classes/general/keycloak_server.php',
    'CKeycloakUtil' => 'classes/general/keycloak_util.php',


    'Session' => 'classes/general/Session.php',
    'HttpClient' => 'classes/general/HttpClient.php',
    'KeycloakWeb' => 'classes/general/KeycloakWeb.php',
    'KeycloakWebGuard' => 'classes/general/KeycloakWebGuard.php',
    'KeycloakWebUserProvider' => 'classes/general/KeycloakWebUserProvider.php',
]);