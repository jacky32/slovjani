<?php
class User extends ApplicationRecord
{
  private $username;
  private $email;
  private $password;

  protected $db_attributes = ['username', 'email', 'password'];

  public function __construct($data = [])
  {
    parent::__construct($data, $this->db_attributes);
    if (isset($data['username'])) {
      $this->set_username($data['username']);
    }
    if (isset($data['email'])) {
      $this->set_email($data['email']);
    }
    if (isset($data['password'])) {
      $this->set_password($data['password']);
    }
  }

  // function validate()
  // {
  //   if ($this->get_email() == null) throw new Exception("Email cannot be empty");
  //   if (strlen($this->get_username()) > 255) throw new Exception("Name cannot be longer than 255 characters");
  //   if (strlen($this->get_password()) < 6) throw new Exception("Password must be at least 6 characters long");
  // }

  // function save()
  // {
  //   $this->validate();
  //   $sql = "INSERT INTO users (name, email, password) VALUES ('" . $this->get_username() . "', '" . $this->get_email() . "', '" . password_hash('salt', $this->get_password()) . "');";
  //   $this->connection->query($sql);
  // }

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
