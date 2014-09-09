<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	'NAME' => Loc::getMessage('VOTE_THROUGH_SOCIAL_SERVICES_NAME'),
	'DESCRIPTION' => Loc::getMessage('VOTE_THROUGH_SOCIAL_SERVICES_DESC'),
	'ICON' => '/images/socserv_authrize.jpg',
	'PATH' => array(
			'ID' => 'HTC',
			'CHILD' => array(
				'ID' => 'socserv',
				'NAME' => Loc::getMessage('SOCIAL_SERVICES')
			)
		)
);