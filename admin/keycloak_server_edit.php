<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('keycloak');

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/keycloak/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("keycloak");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);


