<?php
namespace Siw\Util;

class Payload {

  public $payload;
  public $repository;
  public $branch;
  public $pusher;
  public $commits;

  public function __construct($req) {
    $this->payload    = json_decode($req, true);
    $this->repository = $this->payload['repository'];
    $this->branch     = str_replace('refs/heads/', '', $this->payload['ref']);
    $this->pusher     = $this->payload['pusher'];
    $this->commits    = $this->payload['commits'];
  }
}