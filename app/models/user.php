<?php
class User extends ApplicationRecord
{
  public $username;
  public $email;
  public $password;

  protected $db_attributes = ['username', 'email', 'password'];

  public function __construct($data = [])
  {
    parent::__construct($data, $this->db_attributes);
  }

  public static function all()
  {
    $users = [];
    $sql = "SELECT * FROM users;";
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    while ($row = $result->fetch_assoc()) {
      $user = new User(["username" => $row['username'], "email" => $row['email'], "password" => $row['password']]);
      $users[] = $user;
    }

    return $users;
  }

  public static function find($id)
  {
    if (empty($id) || !is_numeric($id)) {
      return null;
    }
    $sql = "SELECT * FROM users WHERE id = " . $id . ";";
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
