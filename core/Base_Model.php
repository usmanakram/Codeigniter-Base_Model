<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Model extends CI_Model
{
	private $table;

	/**
	 * $table: optional (if database table name and model name are same. eg: database.user & user_model)
	 */
	public function __construct($table = NULL) {
		parent::__construct();
		$this->setTableName($table);
	}

	/**
	 * Set the table name
	 */
	private function setTableName($table)
	{
		if ($table == NULL) {
			$this->table = preg_replace('/(_m|_model)?$/', '', strtolower(get_class($this)));
		} else {
			$this->table = $table;
		}
	}

	private function get($where)
	{
		if ($where) {
			$this->db->where($where);
		}
		return $this->db->get($this->table);
	}

	public function get_record($where = false)
	{
		$query = $this->get($where);
		return $query->row_array();
	}

	public function get_records($where = false)
	{
		$query = $this->get($where);
		return $query->result_array();
	}

	public function getByID($id)
	{
		return $this->get_record(array('id' => $id));
	}

	public function getByFieldName($name, $value)
	{
		return $this->get_records(array($name => $value));
	}

	public function getAll()
	{
		return $this->get_records();
	}

	public function insert($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function update($data, $where) {
		return $this->db->update($this->table, $data, $where);
	}

	public function updateByID($data, $id)
	{
		return $this->update($data, array('id' => $id));
	}

	public function delete($where)
	{
		return $this->db->delete($this->table, $where);
	}

	public function deleteByID($id)
	{
		return $this->delete(array('id' => $id));
	}
}