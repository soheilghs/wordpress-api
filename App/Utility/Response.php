<?php

namespace App\Utility;

class Response {

  public static function json($data) {
    wp_send_json($data);
  }
}