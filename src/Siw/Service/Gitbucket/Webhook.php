<?php
namespace Siw\Service\Gitbucket;

use Siw\Model\Payload   as Payload;
use Siw\Util\StringUtil as StringUtil;

class Webhook {

  const COMMIT              = 'commit';
  const OPEN_PULL_REQUEST   = 'open_pull_request';
  const CLOSE_PULL_REQUEST  = 'close_pull_request';
  const REOPEN_PULL_REQUEST = 'reopen_pull_request';

  const ACTION_OPEN   = 'opened';
  const ACTION_CLOSE  = 'closed';
  const ACTION_REOPEN = 'reopened';

  public $type;

  private $payload;

  public function __construct(Payload $payload) {
    $this->payload = $payload;
    $this->setType();
  }

  public function getAttachments() {
    $attachments = [];
    $data = $this->payload->data;
    switch ($this->type) {
      case self::COMMIT:
        $count = count($data['commits']);
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color' => '#4183c4',
          'pretext' => StringUtil::deleteLineSeparator(
            "[{$data['repository']['name']}:{$this->payload->getBranch()}] {$count} new commits by {$data['pusher']['login']}:"
          ),
          'text' => implode("\n", array_map(
            function($val) {
              return StringUtil::deleteLineSeparator(
                "<{$val['url']}|{$val['id']}>: {$val['message']} - {$val['author']['name']}"
              );
            },
            $data['commits'])
          )
        ];
        break;

      case self::OPEN_PULL_REQUEST:
        $attachments[] = [
          'fallback'   => 'GitBucket webhook',
          'color'      => '#6CC644',
          'pretext'    => $this->getPullRequestText('submitted', false),
          'title'      => "#{$data['number']} {$data['pull_request']['title']}",
          'title_link' => $data['pull_request']['html_url'],
          'text'       => $data['pull_request']['body']
        ];
        break;

      case self::CLOSE_PULL_REQUEST:
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color'    => '#E3E4E6',
          'text'     => $this->getPullRequestText('closed')
        ];
        break;

      case self::REOPEN_PULL_REQUEST:
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color'    => '#6CC644',
          'text'     => $this->getPullRequestText('re-opened')
        ];
        break;

      default:
        break;
    }
    return $attachments;
  }

  private function setType() {
    $data = $this->payload->data;
    if (isset($data['commits']) && 0 < count($data['commits'])) {
      $this->type = self::COMMIT;
      return;
    }
    if (isset($data['pull_request'])) {
      if ($data['action'] === self::ACTION_OPEN) {
        $this->type = self::OPEN_PULL_REQUEST;
        return;
      }
      if ($data['action'] === self::ACTION_CLOSE) {
        $this->type = self::CLOSE_PULL_REQUEST;
        return;
      }
      if ($data['action'] === self::ACTION_REOPEN) {
        $this->type = self::REOPEN_PULL_REQUEST;
        return;
      }
    }
  }

  private function getPullRequestText($action, $addTitle = true) {
    $data = $this->payload->data;
    $pr = $data['pull_request'];
    $sender = $data['sender'];
    $text = "[{$data['repository']['full_name']}] Pull request {$action}";
    if ($addTitle) {
      $text .= ": <{$pr['html_url']}|#{$data['number']} {$pr['title']}>";
    }
    return StringUtil::deleteLineSeparator("{$text} by <{$sender['html_url']}|{$sender['login']}>");
  }
}