<?php
if (!check_bitrix_sessid())
{
    return;
}?>

<form action="<?= $APPLICATION->GetCurPage()?>" method="post">
    <?= bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
    <input type="hidden" name="id" value="htc_sociallikes">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?echo CAdminMessage::ShowMessage(GetMessage("HTC_WARNING")); ?>
    <p>
        <input type="checkbox" name="save_hlblock" id="save_hlblock" value="Y" checked>
        <label for="save_hlblock"><?= GetMessage("HTC_UNINSTALL_SAVE_HLBLOCK")?></label>
    </p>
    <input type="submit" name="inst" value="<?= GetMessage("HTC_SAVE")?>">
</form><br /><br />

<form action="<?= $APPLICATION->GetCurPage()?>">

    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">

<form>