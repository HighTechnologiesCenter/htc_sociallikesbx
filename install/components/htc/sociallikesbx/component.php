<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Application as Application;
CModule::IncludeModule('iblock');
CModule::IncludeModule('htc.sociallikes');

$arResult = array();

$arParams["ELEMENT_ID"] = (int)$arParams["ELEMENT_ID"];
if ($arParams["ELEMENT_ID"] == 0)
{
	ShowError(GetMessage("NOT_SPECIFIED_ITEM_IDENTIFIER"));
	return;
}

$post = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $fieldNotChanged  = array(
        "USER_LOGIN",
        "USER_PASSWORD",
        "backurl",
        "auth_service_id",
    );
    foreach($_POST as $key => $value)
    {
        if(!in_array($key, $fieldNotChanged) && !is_array($value))
        {
            $post[ htmlspecialcharsbx($key) ] = htmlspecialcharsbx($value);
        }
    }
}

$arResult['SOCIAL_NETWORKS'] = $arParams['SOCIAL_NETWORKS'];
$arResult['~SOCIAL_NETWORKS'] = array();
foreach ($arResult['SOCIAL_NETWORKS'] as $socialNetwork)
{
    array_push($arResult['~SOCIAL_NETWORKS'], strtoupper($socialNetwork));
}

$application = Application::getInstance();
$context = $application->getContext();

$arResult["CURRENT_URL"] = $context->getServer()->getRequestUri();


global $USER;
if ($USER->IsAuthorized())
{
    $userData = CUser::GetByID($USER->GetID())->Fetch();

    if ($userData['EXTERNAL_AUTH_ID'] == 'socservices')
    {
        $isUserAuthorizedSocialNetwork = true;

        $arResult["USER"] = array(
            "NAME" => htmlspecialcharsEx($userData['NAME']),
            "LAST_NAME" => htmlspecialcharsEx($userData['LAST_NAME']),
            "USER_LOGIN" => htmlspecialcharsEx($userData['LOGIN']),
			"PERSONAL_WWW" => $userData['PERSONAL_WWW']
        );

        if (substr_count($arResult["USER"]["USER_LOGIN"], 'VKuser') > 0)
        {
            $arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"] = 'VKontakte';
        }
        elseif (substr_count($arResult["USER"]["USER_LOGIN"], 'FB_') > 0)
        {
            $arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"] = 'Facebook';
        }
        elseif (substr_count($arResult["USER"]["USER_LOGIN"], 'OKuser') > 0)
        {
            $arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"] = 'Odnoklassniki';
        }

        /**
         * Если пользователь авторизован через соц. сервисы обрабатываем входящие данные: проголосовать/удалить голос
         */
        if (!CModule::IncludeModule('highloadblock'))
        {
			ShowError(GetMessage('NOT_FOUND_HIGHLOAD_IBLOCK'));
			return;
        }
        $hlblock = HL\HighloadBlockTable::getList(array(
            "filter" => array('TABLE_NAME' => CSocialLikesConstants::TABLE_HIGHLOAD_IBLOCK_VOTE)
        ))->fetch();

        if ((int)$hlblock['ID'] == 0)
        {
			ShowError(GetMessage('NOT_FOUND_HIGHLOAD_IBLOCK'));
			return;
        }

        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        /**
         * Если запрещено голосование за несколько элементов
         */
        $arResult['USER_CAN_VOTE'] = 'Y';
        if ($arParams['ALLOWED_VOTE_FOR_MULTIPLE_ITEMS'] != 'Y' && $arParams['ALLOWED_CANCEL_VOTE_FOR_ELEMENT'] != 'Y')
        {
            /**
             * Проверяем голосовал ли пользователь за выбранный элемент
             */
            $selectedResultUserVoiceDB = $entityDataClass::getList(array(
                'filter'=>array(
                    'UF_USER_ID' => $USER->GetID()
                ),
                'select'=>array('ID')
            ));
            if ($userVoice = $selectedResultUserVoiceDB->fetch())
            {
                $arResult['USER_CAN_VOTE'] = 'N';
            }
        }

        /**
         * Если пользователь отправил запрос на голосование и он может добавлять/удалять свой голос
         */
        if ($post['vote'] == 'Y' && $arResult['USER_CAN_VOTE'] == 'Y')
        {
            /**
             * Проверяем есть ли в ИБ поля для количества голосов
             */
            $selectedResultIblockPropertiesDB = CIBlock::GetProperties(
                $arParams['IBLOCK_ID']
            );
            $socialNetworkTemp = $arResult['~SOCIAL_NETWORKS'];
            while($property = $selectedResultIblockPropertiesDB->Fetch())
            {
                $code = strtoupper($property['CODE']);
                if (in_array($code, $socialNetworkTemp))
                {
                    $key = array_search($code, $socialNetworkTemp);
                    unset($socialNetworkTemp[$key]);
                }
            }

            foreach ($socialNetworkTemp as $socialNetwork)
            {
                $fields = Array(
                    "NAME" => $socialNetwork,
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "CODE" => $socialNetwork,
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $arParams['IBLOCK_ID']
                );

                $iblockProperty = new CIBlockProperty;
                $propertyID = $iblockProperty->Add($fields);
            }

            /**
             * Получаем id записи(голоса) за выбранный элемент
             */
            $selectedResultVoiceUserDB = $entityDataClass::getList(array(
                'filter'=>array(
                    'UF_USER_ID' => $USER->GetID(),
                    'UF_ELEMENT_ID' => $arParams["ELEMENT_ID"]
                ),
                'select'=>array('ID'),
                'order' => array('ID' => 'DESC')
            ));

            if ($voiceUser = $selectedResultVoiceUserDB->fetch())
            {
                if ($arParams['ALLOWED_CANCEL_VOTE_FOR_ELEMENT'] == 'Y' && !empty($voiceUser['ID']))
                {
                    /**
                     * Если разрешено удалять свой голос ("Больше не нравится") и
                     * пользователь уже голосовал за этот элемент - удаляем запись(голос) из HL инфоблока
                     */
                    $result = $entityDataClass::delete($voiceUser['ID']);
                    $teller = -1; // переменная для счетчика голосов. Значение счетчика нужно уменьшить на 1
                    $arResult['USER_CAN_VOTE'] = 'Y';
                }
            }
            else
            {
                /**
                 * Добавляем голос пользователя в highload инфоблок
                 */
                //$postId = (int)$post["post_id"]; // Идентификатор поста на стене соц. сети
                $result = $entityDataClass::add(array(
                    'UF_USER_ID' => $USER->GetID(),
					'UF_USER_NAME' => sprintf('%s %s', $arResult["USER"]['NAME'], $arResult["USER"]['LAST_NAME']),
					'UF_WEB_USER_PAGE' => $arResult['USER']['PERSONAL_WWW'],
                    'UF_ELEMENT_ID' => $arParams["ELEMENT_ID"],
                    'UF_DATE' => date("d.m.Y H:i:s"),
                    'UF_SOCIAL_NETWORK' => $arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"]
                ));
                $teller = 1; // переменная для счетчика голосов. Значение счетчика нужно увеличить на 1
                $arResult['USER_CAN_VOTE'] = 'N';
            }

            if (isset($result) && $result->isSuccess())
            {
                /**
                 * Получаем количество голосов за данный элемент через текущую соц. сеть
                 */
                $codeSocialNetwork = strtoupper($arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"]);
                $selectedResultNumberVotesSocialNetworkDB = CIBlockElement::GetProperty(
                    $arParams["IBLOCK_ID"],
                    $arParams["ELEMENT_ID"],
                    "SORT",
                    "ASC",
                    array("CODE" => $codeSocialNetwork)
                );
                if ($votesSocialNetwork = $selectedResultNumberVotesSocialNetworkDB->GetNext())
                {
                    $value = (int)$votesSocialNetwork['VALUE'] + $teller; // получаем измененное количество голосов
                    CIBlockElement::SetPropertyValueCode(
                        $arParams["ELEMENT_ID"],
                        $codeSocialNetwork,
                        (($value >= 0) ? $value : 0)
                    );
                }
            }
        }

    }
}


$arResult["AUTH_SERVICES"] = false;
$arResult["CURRENT_SERVICE"] = false;

if(CModule::IncludeModule("socialservices"))
{
    /**
     * Получаем доступные соц. сервисы c помощью стандартного модуля "Социальные сервисы"
     */
    $authManager = new CSocServAuthManager();
    $services = $authManager->GetActiveAuthServices($arResult);

    if(!empty($services))
    {
        /**
         * Выводим на экран только сcылки для авторизации выбранных соц сетей (VKontakte, Facebook, Odnoklassniki)
         */
        foreach ($services as $serviceId => &$service)
        {
            if ($service['ID'] == $arResult["USER"]["SOCIAL_NETWORK_AUTH_USER"])
            {
                $service = array(
                    'ID' => $service['ID'],
                    'USER_CAN_VOTE' => $arResult['USER_CAN_VOTE']
                );
                unset($arResult['USER_CAN_VOTE']);
            }
            if (!in_array($service['ID'], $arParams['SOCIAL_NETWORKS']))
            {
                unset($services[$serviceId]);
            }
        }

        $arResult["AUTH_SERVICES"] = $services;

        if(!empty($_REQUEST["auth_service_id"]) && isset($arResult["AUTH_SERVICES"][$_REQUEST["auth_service_id"]]))
        {
            $arResult["CURRENT_SERVICE"] = $_REQUEST["auth_service_id"];

            if(!empty($_REQUEST["auth_service_error"]))
            {
                $arResult['ERROR_MESSAGE'] = $authManager->GetError($arResult["CURRENT_SERVICE"], $_REQUEST["auth_service_error"]);
            }
            else
            {
                /**
                 * Записываем в куки - пользователь желает проголосовать
                 */
                setcookie('IS_USER_WANTS_VOTE', true, time() + 180, "/");
                $USER->Logout();
                if (!$authManager->Authorize($_REQUEST["auth_service_id"]))
                {
                    $exception = $APPLICATION->GetException();
                    if ($exception)
                    {
                        $arResult['ERROR_MESSAGE'] = $ex->GetString();
                    }
                }
            }
        }
    }
}

/**
 * Общее количество голосов за элемент
 */
$selectedResultIblockPropertiesDB = CIBlockElement::GetProperty(
    $arParams["IBLOCK_ID"],
    $arParams["ELEMENT_ID"],
    "SORT",
    "ASC"
);
while ($property = $selectedResultIblockPropertiesDB->GetNext())
{
    if (in_array(strtoupper($property['CODE']), $arResult['~SOCIAL_NETWORKS']))
    {
        $arResult['NUMBER_VOTES'][$property['CODE']]  = (int)$property['VALUE'];
    }
}

if ($post['vote'] == 'Y')
{
    $APPLICATION->RestartBuffer();

    header('Content-type: text/plain');
    echo json_encode($arResult);

    die();
}

$this->IncludeComponentTemplate();
