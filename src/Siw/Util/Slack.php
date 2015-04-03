<?php
namespace Siw\Util;

class Slack {

  const URL_API = 'https://slack.com/api';

  const METHOD_CHAT_POST_MESSAGE = 'chat.postMessage';

  public static function post($method, $data) {
    $curl = curl_init(self::getApiUrl($method));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, []);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $ret = curl_exec($curl);
    curl_close($curl);
    return $ret;
  }

  private static function getApiUrl($method) {
    return self::URL_API . "/{$method}";
  }
}