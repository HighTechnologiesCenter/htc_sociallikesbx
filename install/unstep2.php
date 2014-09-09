<?php
if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowNote(GetMessage('HTC_SOCIALLIKES_UNINSTALL_COMPLETED'));?>

<form action="<?= $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
<form>