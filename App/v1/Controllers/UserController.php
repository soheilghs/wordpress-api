<?php

namespace App\v1\Controllers;

use App\Repositories\User\UserRepository;
use App\Utility\Response;

class UserController {

  protected $user_repository;

  public function __construct() {
    $this->user_repository = new UserRepository();
  }

  public function details() {
    $user = $this->user_repository->find(1);
    Response::json($user);
  }
}