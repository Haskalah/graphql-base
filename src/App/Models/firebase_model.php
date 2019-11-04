<?php
/**
 * Created by PhpStorm.
 * User: mkelsey
 * Date: 2019-10-31
 * Time: 17:25
 */

namespace App\Models;

use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Factory;

class Firebase_Model {
  protected $auth;

  function __construct() {
    $factory = (new Factory())
        ->withServiceAccount(__DIR__ . '/../../../../warcraft-firebase-admin-sdk.json');

    $this->auth = $factory->createAuth();
  }

  /**
   * Authenticate the given firebase token and return user details.
   */
  public function authenticate() {
    $token = null;
    foreach (getallheaders() as $name => $value) {
      if ($name === "authorization") {
        $token = $value;
        break;
      }
    }

    if (!$token) {
      return null;
    }

    try {
      $verifiedToken = $this->auth->verifyIdToken($token);
      return $verifiedToken->getClaims();
    } catch (InvalidToken $e) {
      return null;
    }
  }


}