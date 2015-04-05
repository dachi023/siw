<?php
require('../vendor/autoload.php');

use Siw\Service\Gitbucket\Webhook as Webhook;
use Siw\Model\Payload             as Payload;
use Siw\Util\Slack                as Slack;

if (!isset($_REQUEST['payload'])) {
  return;
}

$webhook = new Webhook(new Payload($_REQUEST['payload']));
if (is_null($webhook->type)) {
  return;
}

$config   = parse_ini_file('../conf/gitbucket.ini');
$getParam = function($key) use ($config) {
  return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $config[$key];
};

Slack::post(
  Slack::METHOD_CHAT_POST_MESSAGE,
  [
    'token'       => $getParam('token'),
    'channel'     => $getParam('channel'),
    'username'    => $getParam('username'),
    'icon_url'    => $getParam('icon_url'),
    'icon_emoji'  => $getParam('icon_emoji'),
    'text'        => '',
    'attachments' => json_encode($webhook->getAttachments())
  ]
);
