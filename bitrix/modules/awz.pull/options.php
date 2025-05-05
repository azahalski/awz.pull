<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\UI\Extension;
use Awz\Pull\Access\AccessController;

Loc::loadMessages(__FILE__);
global $APPLICATION;
$module_id = "awz.pull";
if(!Loader::includeModule($module_id)) return;
Extension::load('ui.sidepanel-content');
$request = Application::getInstance()->getContext()->getRequest();
$APPLICATION->SetTitle(Loc::getMessage('AWZ_PULL_OPT_TITLE'));

if($request->get('IFRAME_TYPE')==='SIDE_SLIDER'){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
    require_once('lib/access/include/moduleright.php');
    CMain::finalActions();
    die();
}

if(!AccessController::isViewSettings())
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($request->getRequestMethod()==='POST' && AccessController::isEditSettings() && $request->get('Update'))
{
    Option::set($module_id, "push_key", htmlspecialcharsEx($request->get("push_key")), "");
    Option::set($module_id, "locked", time(), "");
    if(mb_substr($request->get("pull_url"),0,4)=='http' || !$request->get("pull_url")){
        Option::set($module_id, "pull_url", htmlspecialcharsEx($request->get("pull_url")), "");
    }else {
        ShowError(Loc::getMessage('AWZ_PULL_OPT_SERVER_ERR2'));
    }
    if(mb_substr($request->get("ws_url"),0,6)=='wss://' || !$request->get("ws_url")){
        Option::set($module_id, "ws_url", htmlspecialcharsEx($request->get("ws_url")), "");
    }else {
        ShowError(Loc::getMessage('AWZ_PULL_OPT_SERVER_ERR3'));
    }
    if(mb_substr($request->get("push_url"),0,4)=='http' || !$request->get("push_url")){
        Option::set($module_id, "push_url", htmlspecialcharsEx($request->get("push_url")), "");
    }else {
        ShowError(Loc::getMessage('AWZ_PULL_OPT_SERVER_ERR'));
    }
    if(mb_substr($request->get("api_url"),0,4)=='http' || !$request->get("api_url")){
        Option::set($module_id, "api_url", htmlspecialcharsEx($request->get("api_url")), "");
    }else {
        ShowError(Loc::getMessage('AWZ_PULL_OPT_SERVER_ERR4'));
    }
}

$aTabs = array();

$aTabs[] = array(
    "DIV" => "edit1",
    "TAB" => Loc::getMessage('AWZ_PULL_OPT_SECT1'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_PULL_OPT_SECT1')
);

$saveUrl = $APPLICATION->GetCurPage(false).'?mid='.htmlspecialcharsbx($module_id).'&lang='.LANGUAGE_ID.'&mid_menu=1';
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
    <style>.adm-workarea option:checked {background-color: rgb(206, 206, 206);}</style>
    <form method="POST" action="<?=$saveUrl?>" id="FORMACTION">
        <?
        $tabControl->BeginNextTab();
        Extension::load("ui.alerts");
        ?>

        <tr>
            <td colspan="2">
                <div class="ui-alert ui-alert-warning">
                    <span class="ui-alert-message">
                        <?=Loc::getMessage('AWZ_PULL_OPT_DESC')?>
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:240px;"><?=Loc::getMessage('AWZ_PULL_OPT_PUSH_URL_TITLE')?></td>
            <td>
                <?$val = Option::get($module_id, "push_url", "","");?>
                <input type="text" value="<?=$val?>" name="push_url" placeholder="https://..."></td>
            </td>
        </tr>
        <tr>
            <td style="width:240px;"><?=Loc::getMessage('AWZ_PULL_OPT_WS_URL_TITLE')?></td>
            <td>
                <?$val = Option::get($module_id, "ws_url", "","");?>
                <input type="text" value="<?=$val?>" name="ws_url" placeholder="wss://..."></td>
            </td>
        </tr>
        <tr>
            <td style="width:240px;"><?=Loc::getMessage('AWZ_PULL_OPT_API_URL_TITLE')?></td>
            <td>
                <?$val = Option::get($module_id, "api_url", "","");?>
                <input type="text" value="<?=$val?>" name="api_url" placeholder="https://..."></td>
            </td>
        </tr>

        <tr>
            <td style="width:240px;"><?=Loc::getMessage('AWZ_PULL_OPT_PUSH_KEY_TITLE')?></td>
            <td>
                <?$val = Option::get($module_id, "push_key", "","");?>
                <input type="text" value="<?=$val?>" name="push_key"></td>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="ui-alert ui-alert-default">
                    <span class="ui-alert-message">
                        <?=Loc::getMessage('AWZ_PULL_OPT_DESC_KEY')?>
                    </span>
                </div>
            </td>
        </tr>

        <?
        $tabControl->Buttons();
        ?>
        <input <?if (!AccessController::isEditSettings()) echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('AWZ_PULL_OPT_L_BTN_SAVE')?>" />
        <input type="hidden" name="Update" value="Y" />
        <?if(AccessController::isViewRight()){?>
            <button class="adm-header-btn adm-security-btn" onclick="BX.SidePanel.Instance.open('<?=$saveUrl?>');return false;">
                <?=Loc::getMessage('AWZ_PULL_OPT_SECT2')?>
            </button>
        <?}?>
        <?$tabControl->End();?>
    </form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");