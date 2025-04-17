<?php
IncludeModuleLangFile(__FILE__);
$MOD_RIGHT = $APPLICATION->GetGroupRight("keycloak");

if ($MOD_RIGHT != "D") {
    $aMenu = [
        "parent_menu" => "global_menu_settings",
        "section" => "keycloak",
        "sort" => 400,
        "text" => "Keycloak",
        "title" => GetMessage("KEYCLOAK_MENU_SERVERS_ALT"),
        "icon" => "keycloak_menu_icon",
        "page_icon" => "keycloak_page_icon",
        "items_id" => "menu_keycloak",
        "url" => "/bitrix/admin/keycloak_server_admin.php?lang=".LANG,
        "more_url" => Array("/bitrix/admin/keycloak_server_edit.php"),
    ];
    return $aMenu;
}

return false;
?>