<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Awz\AutForm\CodesTable;
use Awz\AutForm\Events;
use Awz\AutForm\Helper;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Errorable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\Security;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Awz\Pull\App;
use Awz\Pull\ChannelsTable;

Loc::loadMessages(__FILE__);

class AwzPullClientComponent extends CBitrixComponent implements Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    /** @var  \Bitrix\Main\HttpRequest */
    protected $request;

    /** @var Context $context */
    protected $context;

    public $arParams = array();
    public $arResult = array();

    public $userGroups = array();

    /**
     * Create default component params
     *
     * @param array $arParams параметры
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $this->errorCollection = new ErrorCollection();
        $this->arParams = &$arParams;

        if(!$arParams['TYPE'] && Loader::includeModule('awz.pull'))
            $arParams['TYPE'] = ChannelsTable::CN_PRIVATE;

        if($this->arParams['USER_ID'])
            $this->arParams['USER_ID'] = (int) $this->arParams['USER_ID'];

        return $arParams;
    }

    /**
     * Show public component
     *
     * @throws LoaderException
     */
    public function executeComponent(): void
    {
        if(!Loader::includeModule('awz.pull'))
        {
            ShowError(Loc::getMessage('AWZ_PULL_MODULE_NOT_INSTALL'));
            return;
        }

        $link = Option::get(App::MODULE_ID, 'ws_url', '', '');
        if(!$link){
            ShowError(Loc::getMessage('AWZ_PULL_MODULE_NOT_WS_URL'));
            return;
        }
        $channelId = '';
        if($this->arParams['USER_ID']){
            $channelId = ChannelsTable::getId($this->arParams['USER_ID'], $this->arParams['TYPE']);
        }else{
            $userId = CurrentUser::get()?->getId();
            if($userId){
                $channelId = ChannelsTable::getId($userId, $this->arParams['TYPE']);
            }
        }

        if($channelId)
            $channelId = App::signChannel($channelId);
        $this->arResult['CHANNEL_ID'] = $channelId;
        $this->arResult['LINK'] = str_replace('#CHANNEL_ID#', $channelId, $link);

        $this->includeComponentTemplate();
    }

    /**
     * Добавление ошибки
     *
     * @param string|Error $message
     * @param int $code
     */
    public function addError($message, int $code=0)
    {
        if($message instanceof Error){
            $this->errorCollection[] = $message;
        }elseif(is_string($message)){
            $this->errorCollection[] = new Error($message, $code);
        }
    }

    /**
     * Массив ошибок
     *
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     *
     * @param string|int $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}

