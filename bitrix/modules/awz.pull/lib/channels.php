<?php
namespace Awz\Pull;

use Bitrix\Main\ORM;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Data\Cache;

Loc::loadMessages(__FILE__);

class ChannelsTable extends ORM\Data\DataManager
{

    const CN_PRIVATE = 'private';
    const CN_PUBLIC = 'public';

    const CACHE_TIME = 3600;
    const LIFE_TIME = 43200;
    const CACHE_DIR = "/awz/awz.pull/channels/";

	public static function getFilePath(){
		return __FILE__;
	}

	public static function getTableName(){
		return 'b_awz_pull_chanels';
	}
	
	public static function getMap(){
		return [
            (new ORM\Fields\StringField('ID'))
                ->configureTitle(Loc::getMessage('AWZ_PULL_CHANELS_ENTITY_ID_FIELD'))
                ->configureAutocomplete(false)->configurePrimary(true),
            (new ORM\Fields\IntegerField('USER'))
                ->configureTitle(Loc::getMessage('AWZ_PULL_CHANELS_ENTITY_USER_FIELD')),
            (new ORM\Fields\DatetimeField('DATE_EXPIRED'))
                ->configureTitle(Loc::getMessage('AWZ_PULL_CHANELS_ENTITY_DATE_EXPIRED_FIELD'))
                ->configureRequired(),
            (new ORM\Fields\StringField('TYPE'))
                ->configureTitle(Loc::getMessage('AWZ_PULL_CHANELS_ENTITY_TYPE_FIELD'))
                ->configureRequired(),
        ];
	}

	public static function getId(int $userId, string $channelType=self::CN_PRIVATE): string
    {
		$channelId = '';

		$cache_id = "chanel_".$userId.'_'.$channelType;
		$obCache = Cache::createInstance();
		
		if( $obCache->initCache(self::CACHE_TIME,$cache_id,self::CACHE_DIR)){
            $channelId = $obCache->GetVars();
		}elseif( $obCache->startDataCache()){
            $oldChanel = self::getList(['select'=>['ID'],'filter'=>[
                "USER" => $userId,
                "TYPE" => $channelType,
                ">DATE_EXPIRED"=>DateTime::createFromTimestamp(time()+self::CACHE_TIME)
            ],'limit'=>1, 'order'=>['DATE_EXPIRED'=>'desc']])->fetch();
            if($oldChanel){
                $channelId = $oldChanel['ID'];
            }else{
                $channelId = self::createRandId();
                $arFields = array(
                    "ID" => $channelId,
                    "USER" => $userId,
                    "DATE_EXPIRED" => DateTime::createFromTimestamp(time()+self::LIFE_TIME),
                    "TYPE" => $channelType
                );
                self::add($arFields);
            }
			$obCache->endDataCache($channelId);
		}
		
		return $channelId;
	}
	
	public static function createRandId(): string
    {
        $server = Application::getInstance()->getContext()->getServer();
        $channelId = Random::getString(32);
        $channelId .= $server->getRemoteAddr().$server->getServerName().$server->getServerAddr();
		return md5($channelId);
	}

    public static function onBeforeDelete(\Bitrix\Main\Event $event){
        $id = $event->getParameter('id');
        if(is_array($id)) $id = $id['ID'];
        App::send($id, ['module'=>'awz.pull', 'command'=>'delete channel']);
    }

    public static function deleteExpired(){
        $endTime = time()+20;
        $r = self::getList([
            'select'=>['ID'],
            'filter'=>['<DATE_EXPIRED'=>DateTime::createFromTimestamp(time())]
        ]);
        while($data = $r->fetch()){
            self::delete($data);
            if($endTime>time()) break;
        }
        return "\\Awz\\Pull\\ChannelsTable::deleteExpired();";
    }
	
}