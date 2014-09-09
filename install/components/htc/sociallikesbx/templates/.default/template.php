<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(is_array($arResult['AUTH_SERVICES'])) {?>
    <div class="soc-buttons-cont"><?
        /*
        * Имя пользователя
        */
        if (! empty($arResult['USER']['NAME'])) {?>
            <span class="soc-user-name"><?= $arResult['USER']['NAME'] . ' ' . $arResult['USER']['LAST_NAME']?></span><?
        }?>

        <form name="bx_auth_services" action="<?= $arResult['CURRENT_URL']?>" target="_top" method="post"class="bx_auth_services">
            <input type="hidden" name="auth_service_id" value="" /><?php

            $isUserCanVote = true;

            foreach($arResult['AUTH_SERVICES'] as $service) {
                $numberVotes = intval($arResult['NUMBER_VOTES'][strtoupper($service['ID'])]);
                if(isset($service['FORM_HTML']) && ! empty($service['FORM_HTML'])) {
                    /**
                     * Если пользователь не авторизован через $service выводим ссылку для авторизации в соц сети
                     */?>
                    <div id="bx_auth_serv_<?= $service['ID']?>" class="js-to-vote js-<?= strtolower($service['ID'])?> soc-<?= strtolower($service['ID'])?>-button soc-button">
                        <?= $service['FORM_HTML']?>
                        <span class="js-number-vote soc-number-vote"><?= $numberVotes?></span>
                    </div><?php
                }
                elseif (isset($service['USER_CAN_VOTE'])) {
                    /**
                     * Если пользователь авторизован через $service выводим ссылку для шаринга
                     */
                    if ($service['USER_CAN_VOTE'] == 'N' || ($service['USER_CAN_VOTE'] == 'Y' && $service['IS_SELECTED_SOCIAL_NETWORK'] == 'Y')) {
                        $isUserCanVote = false;
                    }?>

                    <a href="javascript:void(0)" onclick="toVote(); BX.util.popup('<?= $service['URL_FOR_SHARE']?>', 580, 400)" class="js-to-vote js-<?= strtolower($service['ID'])?> soc-<?= strtolower($service['ID'])?>-button soc-button <?= ($service['USER_CAN_VOTE'] == 'N') ? 'voted' : ''; ?>">
                        <span class="js-number-vote soc-number-vote"><?= $numberVotes?></span>
                    </a><?php

                    $arResult['URL_FOR_SHARE'] = $service['URL_FOR_SHARE'];
                }
            }?>

        </form><?
        /**
         * Сообщение о возможности / не возможности проголосовать за элемент
         */ ?>
        <span class="soc-vote-title">
            <span class="js-user-can-vote <?= ($isUserCanVote) ? '' : 'soc-vote-title-hidden' ?>"><?= Loc::getMessage('USER_CAN_VOTE') ?></span>
            <span class="js-user-can-not-vote <?= (! $isUserCanVote) ? '' : 'soc-vote-title-hidden' ?>"><?= GetMessage('USER_CAN_NOT_VOTE')?></span>
        </span>
    </div>
    <div id="js-popup" class="soc-popup">
        <div class="soc-popup-content">
            <h2><?= Loc::getMessage('YOUR_VOTE_COUNTED') ?></h2>
            <p><?= Loc::getMessage('SHARE_WITH_FRIENDS') ?></p>
            <a href="javascript:void(0)" id="js-share" class="soc-button-popup"><?= Loc::getMessage('YES') ?></a>
            <a href="javascript:void(0)" id="js-close-popup" class="soc-button-popup"><?= Loc::getMessage('NO') ?></a>
        </div>
    </div><?php
}

