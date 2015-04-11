<?php
namespace Siw\Service\Gitbucket;

use Siw\Model\Payload   as Payload;
use Siw\Util\StringUtil as StringUtil;

class Webhook {

  const COMMIT              = 'commit';
  const OPEN_PULL_REQUEST   = 'open_pull_request';
  const CLOSE_PULL_REQUEST  = 'close_pull_request';
  const REOPEN_PULL_REQUEST = 'reopen_pull_request';
  const OPEN_ISSUE          = 'open_issue';
  const CLOSE_ISSUE         = 'close_issue';
  const REOPEN_ISSUE        = 'reopen_issue';
  const COMMENT_ISSUE       = 'comment_issue';

  const ACTION_OPEN   = 'opened';
  const ACTION_CLOSE  = 'closed';
  const ACTION_REOPEN = 'reopened';
  const ACTION_CREATE = 'created';

  public $type;

  private $payload;

  public function __construct(Payload $payload) {
    $this->payload = $payload;
    $this->type = $this->getType();
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

      case self::OPEN_ISSUE:
        $attachments[] = [
          'fallback'   => 'GitBucket webhook',
          'color'      => '#F29513',
          'pretext'    => $this->getIssueText('created', false),
          'title'      => "#{$data['number']} {$data['issue']['title']}",
          'title_link' => "{$data['repository']['html_url']}/issues/{$data['number']}",
          'text'       => $data['issue']['body']
        ];
        break;

      case self::CLOSE_ISSUE:
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color'    => '#E3E4E6',
          'text'     => $this->getIssueText('closed')
        ];
        break;

      case self::REOPEN_ISSUE:
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color'    => '#F29513',
          'text'     => $this->getIssueText('re-opened')
        ];
        break;

      case self::COMMENT_ISSUE:
        $number = $data['issue']['number'];
        $pretext = "[{$data['repository']['full_name']}]"
          . " New comment on issue <{$data['repository']['html_url']}/issues/{$number}|#{$number}: {$data['issue']['title']}>";
        $attachments[] = [
          'fallback' => 'GitBucket webhook',
          'color'    => '#FAD5A1',
          'pretext'  => $pretext,
          'title'    => "Comment by {$data['sender']['login']}",
          'text'     => $data['comment']['body']
        ];
        break;

      default:
        break;
    }
    return $attachments;
  }

  private function getType() {
    $data = $this->payload->data;
    if (isset($data['commits']) && 0 < count($data['commits'])) {
      return self::COMMIT;
    }
    if (isset($data['pull_request'])) {
      if ($data['action'] === self::ACTION_OPEN) {
        return self::OPEN_PULL_REQUEST;
      }
      if ($data['action'] === self::ACTION_CLOSE) {
        return self::CLOSE_PULL_REQUEST;
      }
      if ($data['action'] === self::ACTION_REOPEN) {
        return self::REOPEN_PULL_REQUEST;
      }
    }
    if (isset($data['issue'])) {
      if ($data['action'] === self::ACTION_OPEN) {
        return self::OPEN_ISSUE;
      }
      if ($data['action'] === self::ACTION_CLOSE) {
        return self::CLOSE_ISSUE;
      }
      if ($data['action'] === self::ACTION_REOPEN) {
        return self::REOPEN_ISSUE;
      }
      if ($data['action'] === self::ACTION_CREATE) {
        return self::COMMENT_ISSUE;
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

  private function getIssueText($action, $addTitle = true) {
    $data = $this->payload->data;
    $sender = $data['sender'];
    $text = "[{$data['repository']['full_name']}] Issue {$action}";
    if ($addTitle) {
      $number = $data['number'];
      $text .= ": <{$data['repository']['html_url']}/issues/{$number}|#{$number} {$data['issue']['title']}>";
    }
    return StringUtil::deleteLineSeparator("{$text} by <{$sender['html_url']}|{$sender['login']}>");
  }
}