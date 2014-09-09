<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Добавляем open graph metadata в head
 */
$APPLICATION->AddHeadString(
    sprintf(GetMessage('METADATA'),
    (! empty($arParams['LINK_NAME_TO_WALL_POST'])) ? $arParams['LINK_NAME_TO_WALL_POST'] : '',
    (! empty($arParams['LINK_TO_WALL_POST'])) ? $arParams['LINK_TO_WALL_POST'] : '',
    (! empty($arParams['PICTURE_URL_TO_WALL_POST'])) ? $arParams['PICTURE_URL_TO_WALL_POST'] : '',
    (! empty($arParams['MESSAGE_TO_WALL_POST'])) ? $arParams['MESSAGE_TO_WALL_POST'] : '',
    (! empty($arResult['SITE']['NAME'])) ? $arResult['SITE']['NAME'] : ''
));

if (isset($arResult['USER']['IS_NOW_USER_LOGGED']) && $arResult['USER']['IS_NOW_USER_LOGGED'] == 'Y' &&
    isset($arResult['URL_FOR_SHARE']) && ! empty($arResult['URL_FOR_SHARE'])) {
    $APPLICATION->AddHeadString(
        '<script type="text/javascript">
            BX.ready(function(){

                BX("js-popup").style.display = "block";

                BX.bind(BX("js-close-popup"), "click", function() {
                   BX("js-popup").remove();
                });

                BX.bind(BX("js-share"), "click", function() {
                    BX("js-popup").remove();
                    BX.util.popup("' . $arResult['URL_FOR_SHARE'] . '", 580, 400);
                });
            });
        </script>'
    );
}

