<?php

namespace Inky\Core;

class Action {

  private $name;
  private $callback;
  private $priority;
  private $accepted_args;
  private $inject_manager;

  public function __construct(
    $name,
    callable $callback,
    $priority,
    $accepted_args = 1,
    $inject_manager = false
  ) {
    $this->name = name;
    $this->callback = $callback;
    $this->priority = $priority;
    $this->accepted_args = $accepted_args;
    $this->inject_manager = $inject_manager;
  }

  public function get_name() {
    return $this->name;
  }

  public function get_name_for(ComponentManager $manager) {
    if ($this->name[0] == '@') {
      return str_replace('@', "${manager_id}_", $this->name);
    }
    return $this->name;
  }

  public function get_callback() {
    return $this->callback;
  }

  public function get_args() {
    return $this->accepted_args;
  }

  public function get_priority() {
    return $this->priority;
  }

  public function is_injectable() {
    return $this->inject_manager;
  }

  public function as_injectable() {
    $this->inject_manager = true;
    return $this;
  }

}