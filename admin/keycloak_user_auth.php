<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!isset($_REQUEST["ldap_user_id"]) || mb_strlen($_REQUEST["ldap_user_id"]) != 32)
	LocalRedirect("/");

IncludeModuleLangFile(__FILE__);
?>