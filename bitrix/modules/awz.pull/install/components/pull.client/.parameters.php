<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if(!Loader::includeModule('awz.pull')) return;

$arComponentParameters = [
    "GROUPS" => [
        "DEF" => [
            "NAME" => Loc::getMessage('AWZ_PULL_CLIENT_PARAM_GROUP_DEF'),
            "SORT"=>100
        ]
    ],
    "PARAMETERS" => [
        "TYPE"=> [
            "PARENT" => "DEF",
            "NAME" => Loc::getMessage('AWZ_PULL_CLIENT_PARAM_LABEL_TYPE'),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "",
        ],
        "USER"=> [
            "PARENT" => "DEF",
            "NAME" => Loc::getMessage('AWZ_PULL_CLIENT_PARAM_LABEL_TITLE_USER'),
            "TYPE" => "STRING",
            "DEFAULT"=>""
        ]
    ],
];