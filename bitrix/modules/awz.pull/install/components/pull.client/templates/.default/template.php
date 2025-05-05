<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var CBitrixComponentTemplate $this
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 */
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs($templateFolder.'/js/reconnecting-websocket.min.js');

$this->setFrameMode(true);

$randStr = 'awz_pull_client_'.$this->randString();
$cmpId = 'awz_pull_client_'.$this->randString();
$frame = $this->createFrame($randStr, false)->begin();
?>
<?if($arResult['CHANNEL_ID']){
    $options = [
        'url'=>$arResult['LINK']
    ];
    ?>
<script type="text/javascript">
    var <?=$cmpId?> = new window.AwzPullClientComponent(<?=CUtil::PHPToJSObject($options)?>);
    /*BX.addCustomEvent('awz.pull.onmessage',
        BX.delegate(function (msg) {
            console.log(msg);
        })
    );*/
</script>
<?}?>
<?
$frame->beginStub();
?><div id="<?=$randStr?>" style="display:none;"></div>
<?
$frame->end();
?>