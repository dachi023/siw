<?php
require('../vendor/autoload.php');

$p = new Siw\Util\Payload($_REQUEST['payload']);

$repository  = $p->repository['name'];
$branch      = $p->branch;
$count       = count($p->commits);
$pusher      = $p->pusher['name'];
$text        = str_replace("\n", '', "[{$repository}:{$branch}] {$count} new commits by {$pusher}:");

$commits = array_map(function($val) {
  $url     = $val['url'];
  $id      = $val['id'];
  $message = $val['message'];
  $author  = $val['author']['name'];
  return str_replace("\n", '', "<{$url}|{$id}>: {$message} - {$author}");
}, $p->commits);

$config   = parse_ini_file('../conf/gitbucket.ini');
$getParam = function($key) use ($config) {
  return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $config[$key];
};

Siw\Util\Slack::post(
  Siw\Util\Slack::METHOD_CHAT_POST_MESSAGE,
  [
    'token'       => $getParam('token'),
    'channel'     => $getParam('channel'),
    'username'    => $getParam('username'),
    'icon_url'    => $getParam('icon_url'),
    'icon_emoji'  => $getParam('icon_emoji'),
    'text'        => $text,
    'attachments' => json_encode([
      [
        'fallback' => 'Pushed GitBucket',
        'color'    => 'good',
        'text'     => implode("\n", $commits)
      ]
    ])
  ]
);
