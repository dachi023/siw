<?php
namespace Siw\Model;

class Payload {

  public $data = [];
  public $columns = [
    'pusher',
    'ref',
    'commits',
    'repository',
    'action',
    'number',
    'pull_request',
    'sender'
  ];

  public function __construct($req, $decode = true) {
    $payload = $decode ? json_decode($req, true) : $req;
    foreach ($this->columns as $column) {
      if (isset($payload[$column])) {
        $this->data[$column] = $payload[$column];
      }
    }
  }

  public function getBranch() {
    if (!isset($this->data['ref']) || strstr($this->data['ref'], 'refs/heads/') === false) {
      return null;
    }
    return str_replace('refs/heads/', '', $this->payload['ref']);
  }
}