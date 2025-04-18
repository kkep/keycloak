<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2012 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');

$module_id = "keycloak";
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($MOD_RIGHT >= "R"):


    // set up form
    $arAllOptions =	[
        ["base_url", Loc::getMessage('KEYCLOAK_OPTIONS_BASE_URL'), "http://localhost:8080", ["text"]],
        ["realm", Loc::getMessage('KEYCLOAK_OPTIONS_REALM'), "alabuga", ["text"]],
        ["realm_public_key", Loc::getMessage('KEYCLOAK_OPTIONS_REALM_PUBLIC_KEY'), "", ["text"]],
        ["client_id", Loc::getMessage('KEYCLOAK_OPTIONS_CLIENT_ID'), "bitrix", ["text"]],
        ["client_secret", Loc::getMessage('KEYCLOAK_OPTIONS_CLIENT_SECRET'), "", ["text"]],
        ["redirect_url", Loc::getMessage('KEYCLOAK_OPTIONS_REDIRECT_URI'), "", ["text"]],
        ["cache_openid", Loc::getMessage('KEYCLOAK_OPTIONS_CACHE_OPENID'), "N", ["checkbox"]],
        ["redirect_logout", Loc::getMessage('KEYCLOAK_OPTIONS_REDIRECT_LOGOUT'), "no@email", ["text"]],
        ["add_user_when_auth", Loc::getMessage("KEYCLOAK_OPTIONS_NEW_USERS"), "N", ["checkbox"]],
        ["update_user_when_auth", Loc::getMessage("KEYCLOAK_OPTIONS_UPDATE_USERS"), "N", ["checkbox"]],
    ];

    if ($MOD_RIGHT>="W"):

        if ($REQUEST_METHOD === "GET" && $RestoreDefaults <> '' && check_bitrix_sessid()) {
            COption::RemoveOption($module_id);
            $z = CGroup::GetList("id", "asc", ["ACTIVE" => "Y", "ADMIN" => "N"]);
            while($zr = $z->Fetch())
                $APPLICATION->DelGroupRight($module_id, [$zr["ID"]]);
        }

        if($REQUEST_METHOD=="POST" && $Update <> '' && check_bitrix_sessid())
        {
            foreach ($arAllOptions as $option)
            {
                if (!is_array($option))
                    continue;

                $name = $option[0];
                $val = ${$name};
                if($option[3][0] == "checkbox" && $val != "Y")
                    $val = "N";
                if($option[3][0] == "multiselectbox")
                    $val = @implode(",", $val);

                COption::SetOptionString($module_id, $name, $val, $option[1]);
            }
        }

    endif; //if($MOD_RIGHT>="W"):

    $aTabs = [
        ["DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_TAB_SET"), "ICON" => "keycloak_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")],
        ["DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "keycloak_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")],
    ];
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    ?>
    <? $tabControl->Begin(); ?>
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>" name="keycloak_settings">
    <?$tabControl->BeginNextTab();?>
    <?__AdmSettingsDrawList("keycloak", $arAllOptions);?>
    <? $tabControl->BeginNextTab();?>
    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
    <script>
        function RestoreDefaults()
        {
            if(confirm('<?=AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?=$APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANG?>&mid=<?=urlencode($mid)."&".bitrix_sessid_get();?>";
        }
    </script>
    <input type="submit" name="Update" <?=($MOD_RIGHT < "W" ? "disabled" : "")?> value="<?=Loc::getMessage("KEYCLOAK_OPTIONS_SAVE")?>">
    <input type="reset" name="reset" value="<?=Loc::getMessage("KEYCLOAK_OPTIONS_RESET")?>">
    <input type="hidden" name="Update" value="Y">
    <?=bitrix_sessid_post();?>
    <input type="button" <?=($MOD_RIGHT < "W" ? "disabled" : "")?> title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo Loc::getMessage("MAIN_RESTORE_DEFAULTS")?>">
    <?$tabControl->End();?>
    </form>
<?endif;?>
