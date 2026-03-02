<?php

class Pagination
{
  public $resources = [];
  public $current_page = 1;
  public $per_page = 10;
  public $total_pages = 1;
  public $total_count = 0;
  public $previous_page = null;
  public $next_page = null;

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


    // $total_pages = ceil($total_posts / $per_page);
    // if ($current_page < 1) {
    //   $current_page = 1;
    // } else if ($current_page > $total_pages) {
    //   $current_page = $total_pages;
    // }
    // $previous_page = $current_page > 1 ? $current_page - 1 : null;
    // $next_page = ($current_page * $per_page) < $total_posts ? $current_page + 1 : null;
    // $posts = Post::where()->orderBy('created_at', 'DESC')->limit($per_page)->offset(($current_page - 1) * $per_page)->get();
  }
}
