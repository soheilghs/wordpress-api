<?php

namespace App\Repositories\Contracts;

abstract class BaseRepository {

  protected $table;
  protected $primary_key;
  protected $per_page;
  protected $db;
  protected $table_prefix;

  protected $guarded;

  public function __construct() {
    global $wpdb;
    $this->db = $wpdb;
    $this->table_prefix = $wpdb->prefix;
  }

  public function find($id, $columns = null) {
    $columns = $this->columns($columns);
    $row = $this->db->get_row($this->db->prepare("
    SELECT {$columns} 
    FROM {$this->table}
    WHERE {$this->primary_key} = %d
    ", $id));

    if ($row) {
      $row = $this->apply_guard($row);
    }

    return $row;
  }

  public function findBy($criteria = array(), $columns = null,
                         $single_record = false) {
    $method = $single_record ? 'get_row' : 'get_results';
    $columns = $this->columns($columns);
    $query = "SELECT {$columns} FROM {$this->table} WHERE";
    $query .= $this->process_where($criteria);

    return $this->db->{$method}($query);
  }

  public function columns($data) {
    return is_array($data) && count($data) > 0
      ? implode(',', $data) : '*';
  }

  public function process_where($criteria = array()) {
    $query = "";

    foreach ($criteria as $key => $value) {
      $query .= " {$key} = {$value} AND";
    }

    return preg_replace('/AND$/', '', $query);
  }

  public function delete($id) {
    return $this->db->delete($this->table, array(
      $this->primary_key => $id
    ), array('%d'));
  }

  public function update($id, $data = array(), $format = array()) {
    return $this->db->update($this->table, $data, array(
      $this->primary_key => $id
    ), $format, array('%d'));
  }

  protected function apply_guard($row) {
    $raw_data = (array) $row;
    return array_diff_key($raw_data, array_fill_keys($this->guarded, ''));
  }
}