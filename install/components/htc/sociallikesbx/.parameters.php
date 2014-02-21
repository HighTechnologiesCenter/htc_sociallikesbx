<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CModule::IncludeModule("iblock"))
{
    return;
}

$iblocksTypes = CIBlockParameters::GetIBlockTypes();

$iblocks=array();
$selectedResultDB = CIBlock::GetList(
                        Array('SORT' => 'ASC'),
                        Array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
                    );
while($iblock = $selectedResultDB->Fetch())
{
    $iblocks[ $iblock['ID'] ] = '[' . $iblock['ID'] . '] ' . $iblock['NAME'];
}

$socialNetworks = array(
    'VKontakte' => 'ВКонтакте',
    'Facebook' => 'Facebook',
    'Odnoklassniki' => 'Одноклассники'
);

$arComponentParameters = array(
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $iblocksTypes,
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("IBLOCK"),
            "TYPE" => "LIST",
            "VALUES" => $iblocks,
            "REFRESH" => "Y",
        ),
        "ELEMENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
        ),
        "SOCIAL_NETWORKS" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SOCIAL_NETWORKS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $socialNetworks,
        ),

        "ALLOWED_VOTE_FOR_MULTIPLE_ITEMS" => array(
            "NAME" => GetMessage("ALLOWED_VOTE_FOR_MULTIPLE_ITEMS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "ALLOWED_CANCEL_VOTE_FOR_ELEMENT" => array(
            "NAME" => GetMessage("ALLOWED_CANCEL_VOTE_FOR_ELEMENT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "LINK_TO_WALL_POST" => array(
            "NAME" => GetMessage("LINK_TO_WALL_POST"),
            "TYPE" => "TEXT",
        ),
        "LINK_NAME_TO_WALL_POST" => array(
            "NAME" => GetMessage("LINK_NAME_TO_WALL_POST"),
            "TYPE" => "TEXT",
        ),
        "MESSAGE_TO_WALL_POST" => array(
            "NAME" => GetMessage("MESSAGE_TO_WALL_POST"),
            "TYPE" => "TEXT",
        ),
        "PICTURE_URL_TO_WALL_POST" => array(
            "NAME" => GetMessage("PICTURE_URL_TO_WALL_POST"),
            "TYPE" => "TEXT",
        )
    ),
);
?>