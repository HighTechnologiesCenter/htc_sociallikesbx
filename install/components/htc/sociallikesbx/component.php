<?php if (! defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Application as Application;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

global $APPLICATION, $USER;

if (! CModule::IncludeModule('iblock')) {
    ShowError(Loc::getMessage('ERROR_NOT_CONNECT_MODULE_IBLOCK'));
    return;
}
if (! CModule::IncludeModule('socialservices')) {
    ShowError(Loc::getMessage('ERROR_NOT_CONNECT_MODULE_SOCIALSERVICES'));
    return;
}
if (! CModule::IncludeModule('highloadblock')) {
    ShowError(Loc::getMessage('ERROR_NOT_FOUND_HIGHLOAD_IBLOCK'));
    return;
}
if (! CModule::IncludeModule('htc.sociallikes')) {
    ShowError(Loc::getMessage('ERROR_NOT_CONNECT_MODULE_SOCIALLIKES'));
    return;
}


$hlblock = HL\HighloadBlockTable::getList(array(
    'filter' => array('TABLE_NAME' => \Htc\SocialLikes\Constants::TABLE_HIGHLOAD_IBLOCK_VOTE)
))->fetch();

if (intval($hlblock['ID']) == 0) {
    ShowError(Loc::getMessage('ERROR_NOT_FOUND_HIGHLOAD_IBLOCK'));
    return;
}

$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$arResult = array();

$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
if ($arParams['ELEMENT_ID'] == 0) {
    ShowError(Loc::getMessage('ERROR_NOT_SPECIFIED_ITEM_IDENTIFIER'));
    return;
}

$post = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fieldNotChanged  = array(
        'USER_LOGIN',
        'USER_PASSWORD',
        'backurl',
        'auth_service_id',
    );
    foreach($_POST as $key => $value) {
        if(! in_array($key, $fieldNotChanged) && ! is_array($value)) {
            $post[ htmlspecialcharsbx($key) ] = htmlspecialcharsbx($value);
        }
    }
}

$arResult['SOCIAL_NETWORKS'] = $arParams['SOCIAL_NETWORKS'];
$arResult['~SOCIAL_NETWORKS'] = array();
foreach ($arResult['SOCIAL_NETWORKS'] as $socialNetwork) {
    array_push($arResult['~SOCIAL_NETWORKS'], strtoupper($socialNetwork));
}

$application = Application::getInstance();
$context = $application->getContext();

$arResult['CURRENT_URL'] = $context->getServer()->getRequestUri();

/**
 * Если пользователь отправил запрос на авторизацию
 */
$authManager = new CSocServAuthManager();
if(! empty($_REQUEST['auth_service_id'])) {
    $arResult['CURRENT_SERVICE'] = $_REQUEST['auth_service_id'];

    $USER->Logout();
    $_SESSION['IS_NOW_USER_LOGGED'] = 'Y';
    if (! $authManager->Authorize($_REQUEST['auth_service_id'])) {
        $_SESSION['IS_NOW_USER_LOGGED'] = 'N';
        $exception = $APPLICATION->GetException();
        if ($exception) {
            $arResult['ERROR_MESSAGE'] = $ex->GetString();
        }
    }
}

$arResult['SITE'] = CSite::GetByID(SITE_ID)->GetNext();
$isNowUserLogged = false;

if ($USER->IsAuthorized()) {

    $userData = CUser::GetByID($USER->GetID())->Fetch();

    $arResult['USER'] = array(
        'NAME' => htmlspecialcharsEx($userData['NAME']),
        'LAST_NAME' => htmlspecialcharsEx($userData['LAST_NAME']),
        'LOGIN' => htmlspecialcharsEx($userData['LOGIN']),
        'PERSONAL_WWW' => $userData['PERSONAL_WWW'],
        'SOCIAL_NETWORK_AUTH_USER' => \Htc\SocialLikes\Helper::getAuthUserNetwork($userData['LOGIN']),
        'EXTERNAL_AUTH_ID' => $userData['EXTERNAL_AUTH_ID'],
        'IS_NOW_USER_LOGGED' => $_SESSION['IS_NOW_USER_LOGGED']
    );

    if ($_SESSION['IS_NOW_USER_LOGGED'] == 'Y') {
        $post['vote'] = 'Y';
        $isNowUserLogged = true;
        $_SESSION['IS_NOW_USER_LOGGED'] = 'N';
    }

    if ($arResult['USER']['EXTERNAL_AUTH_ID'] == \Htc\SocialLikes\Constants::USER_EXTERNAL_AUTH_ID) {
        $isUserAuthorizedSocialNetwork = true;

        /**
         * Если пользователь отправил запрос на голосование и авторизован через соц. сервисы
         * проверяем может ли он добавлять/удалять свой голос
         */
        $isUserCanVote = 'Y';

        $filter['UF_USER_ID'] = $USER->GetID();
        if ($arParams['ALLOWED_VOTE_FOR_MULTIPLE_ITEMS'] != 'N') {
            $filter['UF_ELEMENT_ID'] = $arParams['ELEMENT_ID'];
        }

        $selectedResultUserVoiceDB = $entityDataClass::getList(array(
            'filter' => $filter,
            'select' => array('ID', 'UF_ELEMENT_ID')
        ));
        while ($userVoice = $selectedResultUserVoiceDB->fetch()) {
            if ($userVoice['UF_ELEMENT_ID'] == $arParams['ELEMENT_ID']) {
                if ($arParams['ALLOWED_VOTE_FOR_MULTIPLE_ITEMS'] == 'N') {
                    if ($arParams['ALLOWED_CANCEL_VOTE_FOR_ELEMENT'] == 'Y') {
                        $isUserCanVote = 'Y';
                        break;
                    }
                    else {
                        $isUserCanVote = 'N';
                        break;
                    }
                }
            }

            if ($arParams['ALLOWED_VOTE_FOR_MULTIPLE_ITEMS'] == 'N') {
                $isUserCanVote = 'N';
            }
        }

        $arResult['USER_CAN_VOTE'] = $isUserCanVote;
        /**
         * Если он может добавлять/удалять свой голос
         */
        if ($post['vote'] == 'Y' && $arResult['USER_CAN_VOTE'] == 'Y') {
            /**
             * Проверяем есть ли в ИБ поля для количества голосов
             */
            $selectedResultIblockPropertiesDB = CIBlock::GetProperties($arParams['IBLOCK_ID']);
            $socialNetworkTemp = $arResult['~SOCIAL_NETWORKS'];
            while($property = $selectedResultIblockPropertiesDB->Fetch()) {
                $code = strtoupper($property['CODE']);
                if (in_array($code, $socialNetworkTemp)) {
                    $key = array_search($code, $socialNetworkTemp);
                    unset($socialNetworkTemp[$key]);
                }
            }

            foreach ($socialNetworkTemp as $socialNetwork) {
                $fields = Array(
                    'NAME' => $socialNetwork,
                    'ACTIVE' => 'Y',
                    'SORT' => '500',
                    'CODE' => $socialNetwork,
                    'PROPERTY_TYPE' => 'S',
                    'IBLOCK_ID' => $arParams['IBLOCK_ID']
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
                    'UF_ELEMENT_ID' => $arParams['ELEMENT_ID']
                ),
                'select'=>array('ID'),
                'order' => array('ID' => 'DESC')
            ));

            if ($voiceUser = $selectedResultVoiceUserDB->fetch()) {
                if ($arParams['ALLOWED_CANCEL_VOTE_FOR_ELEMENT'] == 'Y' && ! empty($voiceUser['ID'])) {
                    /**
                     * Если разрешено удалять свой голос ('Больше не нравится') и
                     * пользователь уже голосовал за этот элемент - удаляем запись(голос) из HL инфоблока
                     */
                    $result = $entityDataClass::delete($voiceUser['ID']);
                    $teller = -1; // переменная для счетчика голосов. Значение счетчика нужно уменьшить на 1
                    $arResult['USER_CAN_VOTE'] = 'Y';
                }
            }
            else {
                /**
                 * Добавляем голос пользователя в highload инфоблок
                 */
                $result = $entityDataClass::add(array(
                    'UF_USER_ID' => $USER->GetID(),
                    'UF_USER_NAME' => sprintf('%s %s', $arResult['USER']['NAME'], $arResult['USER']['LAST_NAME']),
                    'UF_WEB_USER_PAGE' => $arResult['USER']['PERSONAL_WWW'],
                    'UF_ELEMENT_ID' => $arParams['ELEMENT_ID'],
                    'UF_DATE' => date('d.m.Y H:i:s'),
                    'UF_SOCIAL_NETWORK' => $arResult['USER']['SOCIAL_NETWORK_AUTH_USER']
                ));
                $teller = 1; // переменная для счетчика голосов. Значение счетчика нужно увеличить на 1
                $arResult['USER_CAN_VOTE'] = 'N';
            }

            if (isset($result) && $result->isSuccess()) {
                /**
                 * Получаем количество голосов за данный элемент через текущую соц. сеть
                 */
                $codeSocialNetwork = strtoupper($arResult['USER']['SOCIAL_NETWORK_AUTH_USER']);
                $selectedResultNumberVotesSocialNetworkDB = CIBlockElement::GetProperty(
                    $arParams['IBLOCK_ID'],
                    $arParams['ELEMENT_ID'],
                    'SORT',
                    'ASC',
                    array('CODE' => $codeSocialNetwork)
                );
                if ($votesSocialNetwork = $selectedResultNumberVotesSocialNetworkDB->GetNext()) {
                    $value = intval($votesSocialNetwork['VALUE']) + $teller; // получаем измененное количество голосов
                    CIBlockElement::SetPropertyValueCode(
                        $arParams['ELEMENT_ID'],
                        $codeSocialNetwork,
                        (($value >= 0) ? $value : 0)
                    );
                }
            }
        }

    }
}

$arResult['AUTH_SERVICES'] = false;
$arResult['CURRENT_SERVICE'] = false;

/**
 * Получаем доступные соц. сервисы c помощью стандартного модуля 'Социальные сервисы'
 */
$services = $authManager->GetActiveAuthServices($arResult);

if(! empty($services)) {
    /**
     * Выводим на экран только сcылки для авторизации выбранных соц сетей (VKontakte, Facebook, Odnoklassniki)
     */
    foreach ($services as $serviceId => &$service) {
        if ($service['ID'] == $arResult['USER']['SOCIAL_NETWORK_AUTH_USER']) {
            $service = array(
                'ID' => $service['ID'],
                'USER_CAN_VOTE' => $arResult['USER_CAN_VOTE']
            );
            unset($arResult['USER_CAN_VOTE']);
            $service['IS_SELECTED_SOCIAL_NETWORK'] = 'N';
            $selectedResultVoiceUserDB = $entityDataClass::getList(array(
                'filter'=>array(
                    'ID' => $voiceUser['ID'],
                    'UF_USER_ID' => $USER->GetID(),
                    'UF_ELEMENT_ID' => $arParams['ELEMENT_ID']
                ),
                'select'=>array('ID'),
                'order' => array('ID' => 'DESC')
            ));

            if ($voiceUser = $selectedResultVoiceUserDB->fetch()) {
                $service['IS_SELECTED_SOCIAL_NETWORK'] = 'Y';
            }

            $networkService = \Htc\SocialLikes\Helper::getNetworkService($service['ID']);
            $service['URL_FOR_SHARE'] = $networkService->getShareUrl($arParams['LINK_TO_WALL_POST']);
        }
        if (! in_array($service['ID'], $arParams['SOCIAL_NETWORKS'])) {
            unset($services[$serviceId]);
        }
    }

    $arResult['AUTH_SERVICES'] = $services;
}

/**
 * Общее количество голосов за элемент
 */
$selectedResultIblockPropertiesDB = CIBlockElement::GetProperty(
    $arParams['IBLOCK_ID'],
    $arParams['ELEMENT_ID'],
    'SORT',
    'ASC'
);
while ($property = $selectedResultIblockPropertiesDB->GetNext()) {
    if (in_array(strtoupper($property['CODE']), $arResult['~SOCIAL_NETWORKS'])) {
        $arResult['NUMBER_VOTES'][$property['CODE']]  = intval($property['VALUE']);
    }
}

if ($post['vote'] == 'Y' && ! $isNowUserLogged) {
    $APPLICATION->RestartBuffer();

    header('Content-type: text/plain');
    echo json_encode($arResult);

    die();
}

$this->IncludeComponentTemplate();
