<?php

/**
 * Pagination value object containing page metadata and page resources.
 */
class Pagination
{
  public $resources = [];
  public $current_page = 1;
  public $per_page = 10;
  public $total_pages = 1;
  public $total_count = 0;
  public $previous_page = null;
  public $next_page = null;

  /**
   * Calculates pagination metadata and fetches the records for the requested page.
   *
   * @param QueryBuilder $query        A QueryBuilder instance with any pre-applied conditions.
   * @param int|null     $current_page The requested page number (1-based). Clamped to valid range.
   * @param int|null     $start_id     When provided and $current_page is null, the page containing
   *                                   this record ID is resolved automatically.
   */
  public function __construct(QueryBuilder $query, int|null $current_page = 1, int|null $start_id = null)
  {
    $this->total_count = $query->count();
    $this->total_pages = ceil($this->total_count / $this->per_page) ?: 1;

    if ($start_id !== null && $current_page === null) {
      $ids = $query->pluck('id');
      $position = array_search($start_id, $ids);
      $current_page = $position !== false ? (int) ceil(($position + 1) / $this->per_page) : 1;
    }

    $this->current_page = $current_page ? max(1, min($current_page, $this->total_pages)) : 1;
    $this->previous_page = $this->current_page > 1 ? $this->current_page - 1 : null;
    $this->next_page = ($this->current_page * $this->per_page) < $this->total_count ? $this->current_page + 1 : null;
    $offset = ($this->current_page - 1) * $this->per_page;
    $this->resources = $query->limit($this->per_page)->offset($offset)->get();
  }
}
