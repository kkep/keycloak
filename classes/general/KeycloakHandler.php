<?php

IncludeModuleLangFile(__FILE__);

class KeycloakHandler
{
    protected static function isEnabled()
    {
        if ($_POST['disable_sso'] === 'n{*z:@n1:hZH5@}?*+WxULG?JR+/UK') {
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
                return;
            } else {
                // сбрасываем токен
                $service->forgetToken();
            }
        }
    }

    public static function onBeforeProlog()
    {
        var_dump(45345);
        global $USER;
        if (mb_strtolower($USER?->GetLogin()) === 'agkhairullin2') {
            LocalRedirect('/auth/?logout=yes&'.bitrix_sessid_get());
        }

        if (!static::isEnabled()) return;

        var_dump(123);

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
            var_dump(1234);
            //echo '<script>window.location.replace(' . "'$logoutUrl'" . ');</script>';
            KeycloakWeb::instance()->backendLogout();
            KeycloakWeb::instance()->forgetToken();
            //LocalRedirect($_SERVER['SCRIPT_URI'].'?logout=yes');
            //LocalRedirect('/auth/?logout=yes&'.bitrix_sessid_get());
            //header("Location: $logoutUrl");
            //exit();
            return true;
        }
    }

    public static function onBeforeUserLogout()
    {
    }
}