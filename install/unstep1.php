<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="keycloak">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">

    <?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>

    <p>
        <?echo GetMessage("MOD_UNINST_SAVE")?>
    </p>

    <input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>