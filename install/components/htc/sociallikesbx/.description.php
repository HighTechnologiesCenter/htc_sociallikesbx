<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("AUTHORIZATION_THROUGH_SOCIAL_SERVICES"),
	"DESCRIPTION" => GetMessage("AUTHORIZATION_THROUGH_SOCIAL_SERVICES"),
	"ICON" => "/images/socserv_authrize.jpg",
	"PATH" => array(
			"ID" => "HTC",
			"CHILD" => array(
				"ID" => "socserv",
				"NAME" => GetMessage("SOCIAL_SERVICES")
			)
		),	
);
?>