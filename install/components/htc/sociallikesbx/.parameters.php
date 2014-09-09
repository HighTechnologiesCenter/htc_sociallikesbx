<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?><?

if(! CModule::IncludeModule('iblock') || ! CModule::IncludeModule('htc.sociallikes')) {
    return;
}

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$iblocksTypes = CIBlockParameters::GetIBlockTypes();

$iblocks=array();
$selectedResultIblocksDB = CIBlock::GetList(
                        Array('SORT' => 'ASC'),
                        Array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
                    );
while($iblock = $selectedResultIblocksDB->Fetch()) {
    $iblocks[ $iblock['ID'] ] = '[' . $iblock['ID'] . '] ' . $iblock['NAME'];
}

$socialNetworks = \Htc\SocialLikes\Helper::getSocialNetworks();

$arComponentParameters = array(
    'PARAMETERS' => array(
        'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $iblocksTypes,
            'REFRESH' => 'Y'
        ),
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('IBLOCK'),
            'TYPE' => 'LIST',
            'VALUES' => $iblocks,
            'REFRESH' => 'Y'
        ),
        'ELEMENT_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('IBLOCK_ELEMENT_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => '={$_REQUEST["ELEMENT_ID"]}'
        ),
        'SOCIAL_NETWORKS' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('SOCIAL_NETWORKS'),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES' => $socialNetworks
        ),
        'ALLOWED_VOTE_FOR_MULTIPLE_ITEMS' => array(
            'NAME' => Loc::getMessage('ALLOWED_VOTE_FOR_MULTIPLE_ITEMS'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N'
        ),
        'ALLOWED_CANCEL_VOTE_FOR_ELEMENT' => array(
            'NAME' => Loc::getMessage('ALLOWED_CANCEL_VOTE_FOR_ELEMENT'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N'
        ),
        'LINK_TO_WALL_POST' => array(
            'NAME' => Loc::getMessage('LINK_TO_WALL_POST'),
            'TYPE' => 'TEXT'
        ),
        'LINK_NAME_TO_WALL_POST' => array(
            'NAME' => Loc::getMessage('LINK_NAME_TO_WALL_POST'),
            'TYPE' => 'TEXT'
        ),
        'MESSAGE_TO_WALL_POST' => array(
            'NAME' => Loc::getMessage('MESSAGE_TO_WALL_POST'),
            'TYPE' => 'TEXT'
        ),
        'PICTURE_URL_TO_WALL_POST' => array(
            'NAME' => Loc::getMessage('PICTURE_URL_TO_WALL_POST'),
            'TYPE' => 'TEXT'
        )
    )
);