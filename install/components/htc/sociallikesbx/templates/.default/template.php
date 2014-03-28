<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(is_array($arResult["AUTH_SERVICES"]))
{?>
    <div class="soc-buttons-cont"><?
    if (!empty($arResult['USER']['NAME']))
    {?>
        <span class="soc-user-name"><?= sprintf('%s %s', $arResult['USER']['NAME'], $arResult['USER']['LAST_NAME'])?></span><?
    }?>
    <span class="soc-vote-title">
			<? $APPLICATION->ShowViewContent('votingStatus'); ?>
		</span>
    <form name="bx_auth_services" action="<?= $arResult["CURRENT_URL"]?>" class="bx_auth_services" target="_top" method="post">
        <input type="hidden" name="auth_service_id" value="" /><?php

        $isUserCanVote = true;

        foreach($arResult["AUTH_SERVICES"] as $service)
        {
            if(isset($service["FORM_HTML"]) && !empty($service["FORM_HTML"]))
            {?>
            <div id="bx_auth_serv_<?= $service["ID"]?>" class="<?= $service["ID"]?>-button soc-button">
                <?= $service["FORM_HTML"]?>
                <span class="vote_number"><?= $arResult['NUMBER_VOTES'][strtoupper($service['ID'])]?></span>
                </div><?php
            }
            elseif(isset($service['USER_CAN_VOTE']))
            {
                if ($service['USER_CAN_VOTE'] == 'N' || ($service['USER_CAN_VOTE'] == 'Y' && $service['USER_SELECTED_ITEM'] == 'Y'))
                {
                    /**
                     *	≈сли пользователь не может голосовать, либо уже проголосовал за текущий элемент
                     */
                    $isUserCanVote = false;
                }?>

            <div id="js-<?= $service["ID"]?>-vote" class="<?= $service["ID"]?>-button soc-button <?= ($service['USER_CAN_VOTE'] == 'N') ? 'voted' : ''; ?>">
                <span class="vote_number"><?= $arResult['NUMBER_VOTES'][strtoupper($service['ID'])]?></span>
                </div><?php
            }
        }

        $this->SetViewTarget('votingStatus');?>
        <span class="user_can_vote" style="<?= ($isUserCanVote) ? '' : 'display:none;'?>"><?= GetMessage('USER_CAN_VOTE')?></span>
        <span class="user_can_not_vote" style="<?= ($isUserCanVote) ? 'display:none;' : ''?>"><?= GetMessage('USER_CAN_NOT_VOTE')?></span><?php
        $this->EndViewTarget();?>

    </form>
    </div><?php
}?>

