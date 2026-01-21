<?php
class User extends ApplicationRecord
{
  public $username;
  public $email;
  public $password;

  protected static array $db_attributes = ['username', 'email', 'password'];

  public function __construct($data = [])
  {
    parent::__construct($data);
  }

  public function validate()
  {
    $this->validates_presence_of(["username", "email", "password"]);
  }

  public function create() {}

  public function update() {}

  public static function find($id)
  {
    if (empty($id) || !is_numeric($id)) {
      return null;
    }
    $sql = "SELECT * FROM users WHERE id = " . $id . ";";
    error_log("[MySQL] " . $sql);
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    if ($row = $result->fetch_assoc()) {
      $user = new User(["id" => $row['id'], "username" => $row['username'], "email" => $row['email'], "password" => $row['password']]);
      return $user;
    } else {
      return null;
    }
  }

  public static function add() {}
}
