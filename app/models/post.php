<?php
class Post extends ApplicationRecord
{
  public $id;
  public $author_id;
  public $name;
  public $body;
  public $author;
  public $created_at;

  protected static array $db_attributes = ['id', 'name', 'body', 'author_id'];

  protected static array $relations  = [
    'belongs_to' => [
      'author' => [
        'class_name' => User::class,
        'foreign_key' => 'author_id'
      ]
    ]
  ];

  protected static array $validations = [
    "presence" => ["name", "body", "author_id"]
    //   // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

  // TODO: use prepared statements to prevent SQL injection
  // https://www.php.net/manual/en/pdo.prepared-statements.php
  // TODO: Move SQL actions to ActiveModel


  function create()
  {
    $sql = "INSERT INTO posts (name, body, author_id) VALUES
    ('" . $this->name . "', '" . $this->body . "', '" . $this->author_id . "');";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
    $this->id = $this->connection->insert_id;
  }
}
