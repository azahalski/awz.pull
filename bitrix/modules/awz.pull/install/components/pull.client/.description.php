<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => Loc::getMessage("AWZ_PULL_CLIENT_PARAM_DESCR_NAME"),
	"DESCRIPTION" => Loc::getMessage("AWZ_PULL_CLIENT_PARAM_DESCR_NAME"),
	"ICON" => "/images/user_authform.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "awz",
		"NAME" => Loc::getMessage("AWZ_PULL_CLIENT_PARAM_DESCR_GROUP"),
		"CHILD" => array(
			"ID" => "awzpull",
			"NAME" => Loc::getMessage("AWZ_PULL_CLIENT_PARAM_DESCR_GROUP_USER")
		)
	),
);
