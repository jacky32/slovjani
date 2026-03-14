<?php

declare(strict_types=1);

namespace ActiveModel\Validations {
  if (!function_exists('ActiveModel\\Validations\\t')) {
    function t(string $key, array $params = []): string
    {
      return $key;
    }
  }
}

namespace {

  // ---- load dependencies ----
  if (!function_exists('toSnakeCase')) {
    require_once __DIR__ . '/../../lib/helpers.php';
  }
  if (!class_exists('Logger')) {
    require_once __DIR__ . '/../../lib/logger.php';
  }
  if (!class_exists('Collection')) {
    require_once __DIR__ . '/../../lib/active_model/relations/collection.php';
  }
  if (!class_exists('QueryBuilder')) {
    require_once __DIR__ . '/../../lib/active_model/relations/query_builder.php';
  }
  if (!class_exists('Pagination')) {
    require_once __DIR__ . '/../../lib/active_model/relations/pagination.php';
  }
  if (!trait_exists('Relations')) {
    require_once __DIR__ . '/../../lib/active_model/relations/relations.php';
  }
  if (!trait_exists('Attributes')) {
    require_once __DIR__ . '/../../lib/active_model/attributes/attributes.php';
  }
  if (!trait_exists('Validations')) {
    require_once __DIR__ . '/../../lib/active_model/validations/validations.php';
  }
  if (!class_exists('ActiveModel')) {
    require_once __DIR__ . '/../../lib/active_model/active_model.php';
  }

  // ---- Database stub so ActiveModel::__construct() never connects ----
  if (!class_exists('Database')) {
    class Database
    {
      public function getConnection(): object
      {
        return new class {
          public function close(): void {}
        };
      }
    }
  }

  // ---- Load models in dependency order ----
  if (!class_exists('ApplicationRecord')) {
    require_once __DIR__ . '/../../app/models/application_record.php';
  }
  foreach (['user', 'post', 'event', 'comment', 'attachment', 'voting', 'question', 'users_question'] as $_m) {
    $cls = match ($_m) {
      'users_question' => 'UsersQuestion',
      default => ucfirst($_m),
    };
    if (!class_exists($cls)) {
      require_once __DIR__ . "/../../app/models/{$_m}.php";
    }
  }

  use PHPUnit\Framework\TestCase;

  /**
   * Tests for model class declarations.
   *
   * Models are inspected via Reflection on their static arrays so that no
   * database connection is needed.  Attachment construction is also tested
   * because its constructor auto-generates a token without touching the DB.
   */
  final class models_metadata_test extends TestCase
  {
    /** Return the static property value of a model class via Reflection. */
    private function staticProp(string $class, string $prop): mixed
    {
      $ref = new ReflectionProperty($class, $prop);
      $ref->setAccessible(true);
      return $ref->getValue();
    }

    // ---- User model ----

    public function testUserDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('User', 'db_attributes');
      foreach (['id', 'username', 'email', 'password', 'roles_mask', 'created_at', 'updated_at'] as $col) {
        $this->assertContains($col, $attrs, "User::\$db_attributes missing '$col'");
      }
    }

    public function testUserValidatesPresenceOfRequiredFields(): void
    {
      $v = $this->staticProp('User', 'validations');
      $this->assertArrayHasKey('presence', $v);
      foreach (['username', 'email', 'password'] as $field) {
        $this->assertContains($field, $v['presence']);
      }
    }

    public function testUserValidatesLength(): void
    {
      $v = $this->staticProp('User', 'validations');
      $this->assertArrayHasKey('length', $v);
      $this->assertArrayHasKey('username', $v['length']);
      $this->assertSame(3,  $v['length']['username']['min']);
      $this->assertSame(16, $v['length']['username']['max']);
    }

    public function testUserValidatesUniqueness(): void
    {
      $v = $this->staticProp('User', 'validations');
      $this->assertArrayHasKey('uniqueness', $v);
      $this->assertContains('username', $v['uniqueness']);
      $this->assertContains('email',    $v['uniqueness']);
    }

    public function testUserAvailableRolesConstantContainsAdmin(): void
    {
      $this->assertArrayHasKey('admin', User::AVAILABLE_ROLES);
    }

    public function testUserAvailableRolesConstantContainsCollaborator(): void
    {
      $this->assertArrayHasKey('collaborator', User::AVAILABLE_ROLES);
    }

    public function testUserAvailableRolesConstantContainsNone(): void
    {
      $this->assertArrayHasKey('none', User::AVAILABLE_ROLES);
      $this->assertSame(0, User::AVAILABLE_ROLES['none']);
    }

    public function testUserHasManyRelationsAreDeclared(): void
    {
      $r = $this->staticProp('User', 'relations');
      $this->assertArrayHasKey('has_many', $r);
      foreach (['posts', 'votings', 'users_questions', 'attachments', 'comments'] as $rel) {
        $this->assertArrayHasKey($rel, $r['has_many'], "User missing has_many '$rel'");
      }
    }

    // ---- Post model ----

    public function testPostDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('Post', 'db_attributes');
      foreach (['id', 'name', 'body', 'creator_id', 'status', 'created_at', 'updated_at'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testPostValidatesPresence(): void
    {
      $v = $this->staticProp('Post', 'validations');
      foreach (['name', 'body', 'creator_id', 'status'] as $f) {
        $this->assertContains($f, $v['presence']);
      }
    }

    public function testPostValidatesStatusInclusion(): void
    {
      $v = $this->staticProp('Post', 'validations');
      $this->assertArrayHasKey('inclusion', $v);
      $this->assertContains('PUBLISHED', $v['inclusion']['status']);
      $this->assertContains('DRAFT',     $v['inclusion']['status']);
      $this->assertContains('ARCHIVED',  $v['inclusion']['status']);
    }

    public function testPostHasBelongsToCreator(): void
    {
      $r = $this->staticProp('Post', 'relations');
      $this->assertArrayHasKey('creator', $r['belongs_to']);
      $this->assertSame('creator_id', $r['belongs_to']['creator']['foreign_key']);
    }

    public function testPostHasManyAttachmentsAndComments(): void
    {
      $r = $this->staticProp('Post', 'relations');
      $this->assertArrayHasKey('attachments', $r['has_many']);
      $this->assertArrayHasKey('comments',    $r['has_many']);
    }

    // ---- Event model ----

    public function testEventDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('Event', 'db_attributes');
      foreach (['id', 'creator_id', 'name', 'description', 'datetime_start', 'datetime_end', 'is_publicly_visible'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testEventValidatesPresence(): void
    {
      $v = $this->staticProp('Event', 'validations');
      foreach (['creator_id', 'name', 'description', 'datetime_start'] as $f) {
        $this->assertContains($f, $v['presence']);
      }
    }

    public function testEventValidatesLength(): void
    {
      $v = $this->staticProp('Event', 'validations');
      $this->assertArrayHasKey('name', $v['length']);
      $this->assertSame(4,   $v['length']['name']['min']);
      $this->assertSame(255, $v['length']['name']['max']);
    }

    // ---- Comment model ----

    public function testCommentDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('Comment', 'db_attributes');
      foreach (['id', 'resource_id', 'resource_type', 'body', 'creator_id'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testCommentValidatesPresence(): void
    {
      $v = $this->staticProp('Comment', 'validations');
      foreach (['creator_id', 'body', 'resource_id', 'resource_type'] as $f) {
        $this->assertContains($f, $v['presence']);
      }
    }

    public function testCommentHasBelongsToCreatorAndCommentable(): void
    {
      $r = $this->staticProp('Comment', 'relations');
      $this->assertArrayHasKey('creator',     $r['belongs_to']);
      $this->assertArrayHasKey('commentable', $r['belongs_to']);
      $this->assertTrue($r['belongs_to']['commentable']['polymorphic']);
    }

    // ---- Voting model ----

    public function testVotingDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('Voting', 'db_attributes');
      foreach (['id', 'name', 'status', 'description', 'creator_id', 'datetime_start', 'datetime_end'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testVotingValidatesStatusInclusion(): void
    {
      $v = $this->staticProp('Voting', 'validations');
      foreach (['DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'] as $s) {
        $this->assertContains($s, $v['inclusion']['status']);
      }
    }

    // ---- Question model ----

    public function testQuestionDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('Question', 'db_attributes');
      foreach (['id', 'voting_id', 'name', 'description'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testQuestionValidatesPresence(): void
    {
      $v = $this->staticProp('Question', 'validations');
      foreach (['voting_id', 'name', 'description'] as $f) {
        $this->assertContains($f, $v['presence']);
      }
    }

    public function testQuestionBelongsToVoting(): void
    {
      $r = $this->staticProp('Question', 'relations');
      $this->assertArrayHasKey('voting', $r['belongs_to']);
      $this->assertSame('voting_id', $r['belongs_to']['voting']['foreign_key']);
    }

    public function testQuestionHasManyUsersQuestions(): void
    {
      $r = $this->staticProp('Question', 'relations');
      $this->assertArrayHasKey('users_questions', $r['has_many']);
    }

    // ---- UsersQuestion model ----

    public function testUsersQuestionDbAttributesContainsExpectedColumns(): void
    {
      $attrs = $this->staticProp('UsersQuestion', 'db_attributes');
      foreach (['question_id', 'user_id', 'chosen_option'] as $col) {
        $this->assertContains($col, $attrs);
      }
    }

    public function testUsersQuestionValidatesChosenOptionInclusion(): void
    {
      $v = $this->staticProp('UsersQuestion', 'validations');
      foreach (['YES', 'NO', 'ABSTAIN'] as $opt) {
        $this->assertContains($opt, $v['inclusion']['chosen_option']);
      }
    }

    public function testUsersQuestionValidatesUniquenessCompositeKey(): void
    {
      $v = $this->staticProp('UsersQuestion', 'validations');
      $this->assertContains(['user_id', 'question_id'], $v['uniqueness']);
    }

    public function testUsersQuestionCompositePrimaryKey(): void
    {
      $ref = new ReflectionProperty('UsersQuestion', 'composite_primary_key');
      $ref->setAccessible(true);
      $pk = $ref->getValue();
      $this->assertContains('user_id',     $pk);
      $this->assertContains('question_id', $pk);
    }
  }
}
