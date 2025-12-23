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
    if (isset($data['username'])) {
      $this->username = $data['username'];
    }
    if (isset($data['email'])) {
      $this->email = $data['email'];
    }
    if (isset($data['password'])) {
      $this->password = $data['password'];
    }
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

  public static function add() {}
}
