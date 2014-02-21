<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$site = CSite::GetByID(SITE_ID)->GetNext();
/**
 * Добавляем open graph metadata в head
 */
$APPLICATION->AddHeadString(
    sprintf(GetMessage('METADATA'),
    ((!empty($arParams['LINK_NAME_TO_WALL_POST'])) ? $arParams['LINK_NAME_TO_WALL_POST'] : ''),
    ((!empty($arParams['LINK_TO_WALL_POST'])) ? $arParams['LINK_TO_WALL_POST'] : ''),
    ((!empty($arParams['PICTURE_URL_TO_WALL_POST'])) ? $arParams['PICTURE_URL_TO_WALL_POST'] : ''),
    ((!empty($arParams['MESSAGE_TO_WALL_POST'])) ? $arParams['MESSAGE_TO_WALL_POST'] : ''),
    ((!empty($site['NAME'])) ? $site['NAME'] : '')
));

if(CModule::IncludeModule("socialservices"))
{
    /**
     * Получаем доступные соц. сервисы c помощью стандартного модуля "Социальные сервисы"
     */
    global $USER;
    $userId = $USER->GetID();

    if (!empty($userId) && !empty($arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"]))
    {
        $selectedResultUserDB = CUser::GetByID($userId);
        $userData = $selectedResultUserDB->Fetch();

        if (!empty($userData['XML_ID']) && $userData['EXTERNAL_AUTH_ID'] == 'socservices')
        {
            switch ($arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"])
            {
                case 'VKontakte':
                    $appId = trim(CSocServVKontakte::GetOption("vkontakte_appid"));

                    if (!empty($appId) && !empty($userId))
                    {
                        /**
                         * Подключаем js api vk
                         */
                        $APPLICATION->IncludeFile($templateFolder . "/js/JSAPIVkontakte.php", array(
                            'appId' => $appId,
                            'message' => $arParams['MESSAGE_TO_WALL_POST'],
                            'attachments' => $arParams['LINK_TO_WALL_POST'],
                            'userId' => $userData['XML_ID']
                        ));
                    }
                    break;
                case 'Facebook':
                    $appId = trim(CSocServVKontakte::GetOption("facebook_appid"));

                    if (!empty($appId) && !empty($userId))
                    {
                        /**
                         * Подключаем js api facebook
                         */
                        $APPLICATION->IncludeFile($templateFolder . "/js/JSAPIFacebook.php", array(
                            'appId' => $appId,
                            'message' => $arParams['MESSAGE_TO_WALL_POST'],
                            'linkUrl' => $arParams['LINK_TO_WALL_POST'],
                            'name' => $arParams['LINK_NAME_TO_WALL_POST'],
                            'picture' => $arParams['PICTURE_URL_TO_WALL_POST']
                        ));
                        /**
                         * Добавляем мета тег в head
                         */
                        $APPLICATION->AddHeadString('<meta content="' . $appId . '" property="fb:app_id">');
                    }
                    break;
                case 'Odnoklassniki':
                    /**
                     * Подключаем js для Одноклассников
                     */
                    $APPLICATION->IncludeFile($templateFolder . "/js/JSAPIOdnoklassniki.php", array(
                        'message' => $arParams['MESSAGE_TO_WALL_POST'],
                        'linkUrl' => $arParams['LINK_TO_WALL_POST']
                    ));
                    break;
            }
        }
    }
}
?>