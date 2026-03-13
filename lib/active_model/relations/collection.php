<?php

/**
 * Collection - Array wrapper with Rails-like collection methods
 */
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
  private array $items;

  public function __construct(array $items = [])
  {
    $this->items = $items;
  }

  /**
   * Get first item or null
   */
  public function first(): ?object
  {
    return $this->items[0] ?? null;
  }

  /**
   * Get last item or null
   */
  public function last(): ?object
  {
    return empty($this->items) ? null : $this->items[array_key_last($this->items)];
  }

  /**
   * Check if collection is empty
   */
  public function isEmpty(): bool
  {
    return empty($this->items);
  }

  /**
   * Check if collection has items
   */
  public function isNotEmpty(): bool
  {
    return !$this->isEmpty();
  }

  /**
   * Get count
   */
  public function count(): int
  {
    return count($this->items);
  }

  /**
   * Map over items
   */
  public function map(callable $callback): self
  {
    return new self(array_map($callback, $this->items));
  }

  /**
   * Filter items
   */
  public function filter(callable $callback): self
  {
    return new self(array_values(array_filter($this->items, $callback)));
  }

  /**
   * Find item by callback
   */
  public function find(callable $callback): ?object
  {
    foreach ($this->items as $item) {
      if ($callback($item)) {
        return $item;
      }
    }
    return null;
  }

  /**
   * Pluck a single attribute from each item
   */
  public function pluck(string $attribute): array
  {
    return array_map(fn($item) => $item->{$attribute} ?? null, $this->items);
  }

  /**
   * Get all items as array
   */
  public function toArray(): array
  {
    return $this->items;
  }

  /**
   * Each iterator
   * TODO: Nahradit foreach v Controllers a dalších místech, kde se iteruje přes kolekce, tímto each() pro konzistentnější styl a možnost přidat další funkce do řetězce.
   */
  public function each(callable $callback): self
  {
    foreach ($this->items as $key => $item) {
      $callback($item, $key);
    }
    return $this;
  }

  // IteratorAggregate
  public function getIterator(): \Traversable
  {
    return new \ArrayIterator($this->items);
  }

  // TODO: Remove unused methods
  // ArrayAccess
  public function offsetExists(mixed $offset): bool
  {
    return isset($this->items[$offset]);
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->items[$offset] ?? null;
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    if ($offset === null) {
      $this->items[] = $value;
    } else {
      $this->items[$offset] = $value;
    }
  }

  public function offsetUnset(mixed $offset): void
  {
    unset($this->items[$offset]);
  }
}
