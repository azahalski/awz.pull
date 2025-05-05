<?php

namespace Awz\Pull;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Security\Sign;

class App {

    const MODULE_ID = 'awz.pull';

    const MAX_ERR_SEND = 50;
    const ERR_SEND_TIMEOUT = 600;

    public static function getSecretKey(): string
    {
        $secretKey = Option::get(static::MODULE_ID, 'push_key', '', '');
        return $secretKey;
    }

    public static function jsonEncode($params)
    {
        $option = JSON_UNESCAPED_UNICODE;
        if ($params instanceof \Bitrix\Main\Type\DateTime)
        {
            $params = date('c', $params->getTimestamp());
        }elseif(is_array($params)){
            static::recursiveConvertDateToString($params);
        }

        return \Bitrix\Main\Web\Json::encode($params, $option);
    }

    public static function recursiveConvertDateToString(array &$params)
    {
        array_walk_recursive($params, function(&$item, $key){
            if ($item instanceof \Bitrix\Main\Type\DateTime)
            {
                $item = date('c', $item->getTimestamp());
            }
        });
    }

    public static function sendToUser(int $userId, array $message, array $options = array(), $type = ChannelsTable::CN_PRIVATE)
    {
        return self::send(ChannelsTable::getId($userId, $type), $message, $options);
    }

    public static function send($channelId, $message, array $options = array())
    {
        if (!is_array($channelId))
            $channelId = [$channelId];
        $channelId = implode('/', array_unique($channelId));

        if ($channelId == '' || $message == '')
            return false;

        $defaultOptions = array(
            "method" => "POST",
            "timeout" => 5,
            "dont_wait_answer" => true
        );

        $options = array_merge($defaultOptions, $options);

        if (!in_array($options["method"], Array('POST', 'GET')))
            return false;

        $locked = Option::get(static::MODULE_ID, 'locked', 0, '');
        $locked_cnt = Option::get(static::MODULE_ID, 'locked_cnt', 0, '');

        if($locked>time()){
            Option::set(static::MODULE_ID, 'locked_cnt', 0, '');
            return false;
        }

        $postdata = "\"".base64_encode(self::jsonEncode($message))."\"";

        $httpClient = new \Bitrix\Main\Web\HttpClient([
            "socketTimeout" => (int)$options["timeout"],
            "streamTimeout" => (int)$options["timeout"],
            "waitResponse" => !$options["dont_wait_answer"]
        ]);
        if ((int)$options["expiry"])
        {
            $httpClient->setHeader("Message-Expiry", (int)$options["expiry"]);
        }

        $url = self::getPublishUrl($channelId);
        $httpClient->disableSslVerification();

        print_r([$url, $postdata]);

        $sendResult = $httpClient->query($options["method"], $url, $postdata);
        if ($sendResult)
        {
            $result = $options["dont_wait_answer"] ? '{}': $httpClient->getResult();
        }else{
            $locked_cnt += 1;
            Option::set(static::MODULE_ID, 'locked_cnt', $locked_cnt, '');
            if($locked_cnt>static::MAX_ERR_SEND)
                Option::set(static::MODULE_ID, 'locked', time()+static::ERR_SEND_TIMEOUT, '');
            $result = false;
        }
        return $result;
    }

    public static function getPublishUrl(string $channelId=''){
        $url = Option::get(self::MODULE_ID, 'push_url', '', '');
        if(strpos($url, '#CHANNEL_ID#')!==false){
            $url = str_replace('#CHANNEL_ID#', $channelId, $url);
        }else{
            $url .= ((strpos($url,'?')!==false) ? '&':'?').'CHANNEL_ID='.$channelId;
        }
        return $url;
    }

    public static function signChannel(string $channelId){
        $signatureKey = self::getSecretKey();
        if ($signatureKey === "")
        {
            return $channelId;
        }

        return $channelId.".".static::getSignature($channelId);
    }

    public static function getSignature($value, $signatureKey = null)
    {
        if(!$signatureKey)
        {
            $signatureKey = self::getSecretKey();
        }
        $signatureAlgo = Option::get(self::MODULE_ID, 'signature_algo', 'sha1', '');
        $hmac = new Sign\HmacAlgorithm();
        $hmac->setHashAlgorithm($signatureAlgo);
        $signer = new Sign\Signer($hmac);
        $signer->setKey($signatureKey);
        return $signer->getSignature($value);
    }
}