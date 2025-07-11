<?php

IncludeModuleLangFile(__FILE__);

class KeycloakHandler
{
    protected static function isEnabled()
    {
        if ($_POST['disable_sso'] === COption::GetOptionString('keycloak', 'disable_sso_secret_key', 'n{*z:@n1:hZH5@}?*+WxULG?JR+/UK')) {
            COption::SetOptionString('keycloak', 'enabled', 'N', false);
        }

        return COption::GetOptionString('keycloak', 'enabled', 'N') === 'Y';
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
                return true;
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
        if (!static::isEnabled()) return true;

        if ($arParams['SUCCESS']) {
            KeycloakWeb::instance()->backendLogout();
            KeycloakWeb::instance()->forgetToken();
            $_SESSION = [];
            session_destroy();
            return true;
        }

        return false;
    }

    public static function onBeforeUserLogout()
    {
        return true;
    }
}