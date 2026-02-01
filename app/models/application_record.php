<?php
abstract class ApplicationRecord extends ActiveModel
{
  public function __construct($data = [])
  {
    parent::__construct($data);
  }
}
