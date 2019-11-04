<?php
/**
 * Created by PhpStorm.
 * User: mkelsey
 * Date: 2019-10-31
 * Time: 20:41
 */

namespace App\Models;


class User_Model {
  protected $email;
  protected $name;
  protected $authTimestamp;
  protected $expireTimestamp;
  protected $image;
  protected $emailVerified;
  protected $firebaseUserID;

  public function loadByFirebaseToken($token) {
    if (!$token) {
      return false;
    }

    $this->email = $token->email;
    $this->name = $token->name;
    $this->authTimestamp = $token->auth_time;
    $this->expireTimestamp = $token->exp;
    $this->image = $token->picture;
    $this->emailVerified = $token->email_verified;
    $this->firebaseUserID = $token->user_id;

    return true;
  }
}