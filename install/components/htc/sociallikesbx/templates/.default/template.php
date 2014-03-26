<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(is_array($arResult["AUTH_SERVICES"]) && count($arResult["AUTH_SERVICES"]) > 0)
{?>
    <div class="soc-buttons-cont">
        <span class="soc-vote-title"><? $APPLICATION->ShowViewContent('votingStatus'); ?></span>
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
                    if ($service['USER_CAN_VOTE'] == 'N')
                    {
                        $isUserCanVote = false;
                    }?>

                    <div <?= ($service['USER_CAN_VOTE'] == 'Y') ? 'id="js-' . $service["ID"] . '-vote"' : ''; ?> class="<?= $service["ID"]?>-button soc-button <?= ($service['USER_CAN_VOTE'] == 'N') ? 'voted' : ''; ?>">
                        <span class="vote_number"><?= $arResult['NUMBER_VOTES'][strtoupper($service['ID'])]?></span>
                    </div><?php
                }
            }

            $this->SetViewTarget('votingStatus');
                echo (($isUserCanVote) ? GetMessage('USER_CAN_VOTE') : GetMessage('USER_CAN_NOT_VOTE'));
            $this->EndViewTarget();?>

        </form>
    </div><?php
}?>

