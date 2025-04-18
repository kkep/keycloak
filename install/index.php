<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class keycloak extends CModule
{
	var $MODULE_ID = "keycloak";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	
	var $errors = [];

	function __construct()
	{
		$arModuleVersion = [];

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("KEYCLOAK_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("KEYCLOAK_MODULE_DESC");
	}

	function InstallFiles($arParams = [])
	{
		global $APPLICATION;

        if ($_ENV["COMPUTERNAME"] != 'BX') {
            CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/keycloak/install/images", $_SERVER['DOCUMENT_ROOT']."/bitrix/images/keycloak");
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/keycloak/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/keycloak/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
        }

		if (count($this->errors) > 0) {
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function UnInstallFiles($arParams = [])
	{
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/keycloak/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/keycloak/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
        DeleteDirFilesEx("/bitrix/themes/.default/icons/keycloak/");//icons
        DeleteDirFilesEx("/bitrix/images/keycloak/");//images
		
		return true;
	}

    function InstallDB($arParams = array())
    {
        global $APPLICATION;
        RegisterModule("keycloak");
        RegisterModuleDependences("main", "OnPageStart", "keycloak", "KeycloakWeb", "onPageStart", 100);
        RegisterModuleDependences('main', 'OnBeforeProlog', 'keycloak', 'KeycloakWeb', 'onBeforeProlog', 40);
        RegisterModuleDependences('main', 'OnBeforeUserLogout', 'keycloak', 'KeycloakWeb', 'onBeforeUserLogout', 20);

        if (count($this->errors) > 0) {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }

        return true;
    }

    function UnInstallDB($arParams = array())
    {
        COption::RemoveOption('keycloak');

        UnRegisterModuleDependences('main', 'OnBeforeProlog', 'keycloak', 'KeycloakWeb', 'onBeforeProlog');
        UnRegisterModuleDependences("main", "OnPageStart", "keycloak", "KeycloakWeb", "onPageStart");
        UnRegisterModuleDependences("main", "OnBeforeUserLogout", "keycloak", "KeycloakWeb", "onBeforeUserLogout");
        UnRegisterModule("keycloak");

        return true;
    }

	function DoInstall()
	{
        global $DB, $DOCUMENT_ROOT, $APPLICATION;

		$APPLICATION->ResetException();

        if ($this->InstallDB()) {
            $this->InstallFiles();
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("KEYCLOAK_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/keycloak/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $DOCUMENT_ROOT, $step;

		$step = intval($step);

		if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("KEYCLOAK_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/keycloak/install/unstep1.php");
        } elseif ($step == 2) {
			$APPLICATION->ResetException();

            if ($this->UnInstallDB()) {
                $this->UnInstallFiles();
            }

            $APPLICATION->IncludeAdminFile(Loc::getMessage("KEYCLOAK_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/keycloak/install/unstep2.php");
		}
	}

    function getInstallPath($path = '')
    {
        global $DOCUMENT_ROOT;

        return $DOCUMENT_ROOT."/bitrix/modules/keycloak/install/".$path;
    }
}
?>