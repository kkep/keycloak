<?php

IncludeModuleLangFile(__FILE__);

class KeycloakHandler
{
    protected static function isEnabled()
    {
        return false && COption::GetOptionString('keycloak', 'enabled', 'N') === 'Y';
    }

    public static function onPageStart()
    {
        if (!static::isEnabled()) return;

        $service = KeycloakWeb::instance();

        // проверяем на наличие токена
        $token = $service->retrieveToken();

        // если токена нет
        if (empty($token)) {
            // если есть "состояние"
            if (!empty($_GET['state'])) {
                // Check for errors from Keycloak
                if (!empty($_GET['error'])) {
                    $error = $_GET['error_description'];
                    $error = ($error) ?: $_GET['error'];

                    throw new Exception($error);
                }

                // Check given state to mitigate CSRF attack
                $state = $_GET['state'];

                if (!$service->validateState($state)) {
                    $service->forgetState();

                    throw new Exception('Invalid state');
                }

                // Change code for token
                $code = $_GET['code'];

                if (! empty($code)) {
                    $token = $service->getAccessToken($code);

                    $service->saveToken($token);
                }
            } else {
                KeycloakWeb::redirectToLogin();
            }
        } else {
            // TODO валидируем токен
            if (true) {
                return;
            } else {
                // сбрасываем токен
                $service->forgetToken();
            }
        }
    }

    public static function onBeforeProlog()
    {
        if (!static::isEnabled()) return;

        if (KeycloakWebGuard::instance()->check() || KeycloakWebGuard::instance()->authenticate()) {
            return;
        } else {
            KeycloakWeb::redirectToLogin();
        }
    }

    public static function onAfterUserLogout($arParams)
    {
        if (!static::isEnabled()) return;

        if ($arParams['SUCCESS']) {
            $logoutUrl = KeycloakWeb::instance()->getLogoutUrl();
            KeycloakWeb::instance()->forgetToken();
            header("Location: $logoutUrl");
            exit();
        }
    }

    public static function onBeforeUserLogout()
    {
    }
}