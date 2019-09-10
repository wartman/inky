<?php
namespace Inky\Core;

class Action {

  private $name;
  private $priority;
  private $accepted_args;
  private $deps = [];
  private $callbacks = [];

  public function __construct(
    $name,
    $priority = 10,
    $accepted_args = 1
  ) {
    $this->name = $name;
    $this->priority = $priority;
    $this->accepted_args = $accepted_args;
  }

  public function get_name() {
    return $this->name;
  }

  public function get_args() {
    return $this->accepted_args;
  }

  public function get_priority() {
    return $this->priority;
  }

  public function inject($dep) {
    $this->deps[] = $dep;
    return $this;
  }

  public function add(callable $callback) {
    $this->callbacks[] = $callback;
    return $this;
  }

  public function commit() {
    add_action($this->get_name(), function () {
      $this->run_callbacks(func_get_args());
    }, $this->get_priority(), $this->get_args());
    return $this;
  }

  public function trigger() {
    $args = func_get_args();
    array_unshift($args, $this->get_name());
    call_user_func_array('do_action', $args);
  }

  public function run_during($name) {
    add_action($name, function () {
      do_action($this->get_name(), func_get_args());
    });
  }

  private function run_callbacks($args) {
    $injected = array_merge($this->deps, $args);
    foreach ($this->callbacks as $callback) {
      call_user_func_array($callback, $injected);
    }
  }

}
