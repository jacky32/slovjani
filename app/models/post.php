<?php
class Post extends ApplicationRecord
{
  public $id;
  public $author_id;
  public $name;
  public $body;
  public $author;
  public $created_at;

  protected $db_attributes = ['id', 'name', 'body', 'author_id'];

  static array $relations  = [
    'belongs_to' => [
      'author' => [
        'class_name' => User::class,
        'foreign_key' => 'author_id'
      ]
    ]
  ];


  public function __construct($data = [])
  {
    parent::__construct($data, $this->db_attributes, self::$relations);
  }


  // Methods

  // function get_author()
  // {
  //   $sql = "SELECT username FROM users WHERE id = " . $this->author_id . ";";
  //   error_log("[MySQL] " . $sql);
  //   $result = $this->connection->query($sql);
  //   if ($row = $result->fetch_assoc()) {
  //     return $row['username'];
  //   } else {
  //     return " - ";
  //   }
  // }

  function validate()
  {
    $this->validates_presence_of(["name", "body", "author_id"]);
    // if ($this->get_name() == null) throw new Exception("Name cannot be empty");
    // if ($this->get_body() == null) throw new Exception("Body cannot be empty");
    // if ($this->get_author_id() == null) throw new Exception("Author cannot be empty");
    // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  }

  // TODO: use prepared statements to prevent SQL injection
  // https://www.php.net/manual/en/pdo.prepared-statements.php
  // TODO: Move SQL actions to ApplicationRecord
  function save()
  {
    $this->validate();
    $sql = "INSERT INTO posts (name, body, author_id) VALUES
    ('" . $this->name . "', '" . $this->body . "', '" . $this->author_id . "');";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
  }

  function destroy()
  {
    $sql = "DELETE FROM posts WHERE id = " . $this->id . ";";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
  }

  public static function find($id)
  {
    if (empty($id) || !is_numeric($id)) {
      return null;
    }
    $sql = "SELECT * FROM posts WHERE id = " . $id . ";";
    error_log("[MySQL] " . $sql);
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
    error_log("[MySQL] " . $sql);
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
