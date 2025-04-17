<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('keycloak');

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/keycloak/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("keycloak");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";
$APPLICATION->SetTitle(GetMessage("KEYCLOAK_ADMIN_TITLE"));

$find_id = 1;
$find_name = "https://localhost:8080";

// set up navigation string
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
    <table>
        <tbody>
        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_BASE_URL")?>:
            </td>
            <td>
                <input type="text" name="base_url" value="<?echo htmlspecialcharsbx($find_name)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_REALM")?>:
            </td>
            <td>
                <input type="text" name="realm" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_REALM_PUBLIC_KEY")?>:
            </td>
            <td>
                <input type="text" name="realm_public_key" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_CLIENT_ID")?>:
            </td>
            <td>
                <input type="text" name="client_id" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_CLIENT_SECRET")?>:
            </td>
            <td>
                <input type="text" name="client_secret" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_REDIRECT_URI")?>:
            </td>
            <td>
                <input type="text" name="redirect_uri" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_CACHE_OPENID")?>:
            </td>
            <td>
                <input type="checkbox" name="cache_openid" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>

        <tr>
            <td>
                <?echo GetMessage("KEYCLOAK_REDIRECT_LOGOUT")?>:
            </td>
            <td>
                <input type="text" name="redirect_logout" value="<?echo htmlspecialcharsbx($find_id)?>" size="47">
            </td>
        </tr>
        </tbody>
    </table>


</form>


<?//$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>

