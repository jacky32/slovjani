<?php
class Post extends ApplicationRecord
{
  public $id;
  public $author_id;
  public $name;
  public $body;

  protected $db_attributes = ['id', 'name', 'body', 'author_id'];

  public function __construct($data = [])
  {
    parent::__construct($data);
    if (isset($data['id'])) {
      $this->id = $data['id'];
    }
    if (isset($data['body'])) {
      $this->body = $data['body'];
    }
    if (isset($data['name'])) {
      $this->name = $data['name'];
    }
    if (isset($data['author_id'])) {
      $this->author_id = $data['author_id'];
    }
  }

  // Methods

  function get_author()
  {
    $sql = "SELECT username FROM users WHERE id = " . $this->author_id . ";";
    $result = $this->connection->query($sql);
    if ($row = $result->fetch_assoc()) {
      return $row['username'];
    } else {
      return " - ";
    }
  }

  function validate()
  {
    $this->validates_presence_of(["name", "body", "author_id"]);
    // if ($this->get_name() == null) throw new Exception("Name cannot be empty");
    // if ($this->get_body() == null) throw new Exception("Body cannot be empty");
    // if ($this->get_author_id() == null) throw new Exception("Author cannot be empty");
    // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  }

  function save()
  {
    $this->validate();
    $sql = "INSERT INTO posts (name, body, author_id) VALUES
    ('" . $this->name . "', '" . $this->body . "', '" . $this->author_id . "');";
    $this->connection->query($sql);
  }

  function destroy()
  {
    $sql = "DELETE FROM posts WHERE id = " . $this->id . ";";
    $this->connection->query($sql);
  }

  public static function find($id)
  {
    if (empty($id) || !is_numeric($id)) {
      return null;
    }
    $sql = "SELECT * FROM posts WHERE id = " . $id . ";";
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    if ($row = $result->fetch_assoc()) {
      $post = new Post(["id" => $row['id'], "body" => $row['body'], "name" => $row['name'], "author_id" => $row['author_id']]);
      return $post;
    } else {
      return null;
    }
  }

  public static function all()
  {
    $posts = [];
    $sql = "SELECT * FROM posts;";
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    while ($row = $result->fetch_assoc()) {
      $post = new Post(["id" => $row['id'], "body" => $row['body'], "name" => $row['name'], "author_id" => $row['author_id']]);
      $posts[] = $post;
    }

    return $posts;
  }

  public static function add() {}
}
