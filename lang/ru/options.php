<?
$MESS["KEYCLOAK_OPTIONS_SAVE"] = "Сохранить";
$MESS["KEYCLOAK_OPTIONS_RESET"] = "Отменить";
$MESS["KEYCLOAK_OPTIONS_GROUP_LIMIT"] = "Максимальное количество KEYCLOAK записей, выбираемых за один запрос:";
$MESS["KEYCLOAK_OPTIONS_USE_NTLM"] = "Использовать NTLM авторизацию<sup><span class=\"required\">1</span></sup>:";
$MESS["KEYCLOAK_OPTIONS_USE_NTLM_MSG"] = "<span class=\"required\">1</span> - Для работы NTLM авторизации требуется выполнить настройку соответствующих модулей веб-сервера, а также задать домены для NTLM авторизации в настройках AD-серверов на портале.";
$MESS["KEYCLOAK_CURRENT_USER"] = "Текущий логин пользователя NTLM авторизации (домен\\логин):";
$MESS["KEYCLOAK_CURRENT_USER_ABS"] = "Не определен";
$MESS["KEYCLOAK_OPTIONS_NTLM_VARNAME"] = "Имя переменной PHP, в которой хранится логин пользователя NTLM (обычно REMOTE_USER):";
$MESS["KEYCLOAK_NOT_USE_DEFAULT_NTLM_SERVER"] = "Не использовать";
$MESS["KEYCLOAK_DEFAULT_NTLM_SERVER"] = "Сервер домена по умолчанию:";
$MESS["KEYCLOAK_OPTIONS_DEFAULT_EMAIL"] = "E-mail для пользователей, у которых он не указан:";
$MESS["KEYCLOAK_BITRIXVM_BLOCK"] = "Переадресация Ntlm авторизации на порты 8890 8891:";
$MESS["KEYCLOAK_BITRIXVM_SUPPORT"] = "Включить переадресацию NTLM авторизации:";
$MESS["KEYCLOAK_BITRIXVM_NET"] = "Ограничить NTLM переадресацию следующей подсетью:";
$MESS["KEYCLOAK_BITRIXVM_HINT"] = "Укажите здесь подсеть, NTLM авторизацию пользователей которой, необходимо переадресовывать.<br> Например: <b>192.168.1.0/24</b> или <b>192.168.1.0/255.255.255.0</b>.<br>Можно указать несколько диапазонов через точку с запятой (;).<br> Если поле оставить пустым, тогда переадресация будет работать для всех пользователей.";
$MESS["KEYCLOAK_WRONG_NET_MASK"] = "Адрес и маска подсети для NTLM авторизации указаны в неверном формате.<br> Приемлемые варианты:<br> сеть/маска <br> xxx.xxx.xxx.xxx/xxx.xxx.xxx.xxx <br> xxx.xxx.xxx.xxx/xx. <br> Можно указать несколько диапазонов через точку с запятой (;)";
$MESS["KEYCLOAK_WITHOUT_PREFIX"] = "Проверять авторизацию на всех доступных ldap серверах, если в логине не указан префикс:";
$MESS["KEYCLOAK_DUPLICATE_LOGIN_USER"] = "Создавать пользователя, если пользователь с таким логином уже существует:";

$MESS["KEYCLOAK_OPTIONS_NEW_USERS"] = "Создавать новых пользователей при первой успешной авторизации:";
$MESS["KEYCLOAK_OPTIONS_UPDATE_USERS"] = "Обновлять данные при успешной авторизации:";
$MESS['KEYCLOAK_OPTIONS_BASE_URL'] = 'Base URL';
$MESS['KEYCLOAK_OPTIONS_REALM'] = 'Realm';
$MESS['KEYCLOAK_OPTIONS_REALM_PUBLIC_KEY'] = 'Realm Public Key';
$MESS['KEYCLOAK_OPTIONS_CLIENT_ID'] = 'Client ID';
$MESS['KEYCLOAK_OPTIONS_CLIENT_SECRET'] = 'Client Secret';
$MESS['KEYCLOAK_OPTIONS_REDIRECT_URI'] = 'Redirect URL';
$MESS['KEYCLOAK_OPTIONS_CACHE_OPENID'] = 'Cache OpenID';
$MESS['KEYCLOAK_OPTIONS_REDIRECT_LOGOUT'] = 'Redirect URL';
?>