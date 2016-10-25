<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Model extends CI_Model
{
	private $table;
	private $primary_key;

	/**
	 * $table: (String) (optional, if database table name and model name are same. eg: database.user & user_model)
	 * $primary_key: (String) (optional)
	 */
	public function __construct($table = NULL, $primary_key = NULL) {
		parent::__construct();
		
		$this->setTableName($table);
		$this->setPrimaryKey($primary_key);
	}

	/*public function setDbName()
	{
		$this->db_name = $this->db->query('SELECT DATABASE() AS db_name')->row()->db_name;
	}

	public function getTableNames()
	{
		$this->setDbName();
		
		$this->db->select('table_name');
		$this->db->where('table_schema', $this->db_name);
		$query = $this->db->get('information_schema.tables');
		return $query->result_array();
	}*/

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

	/**
	 * Set the primary key for current table
	 */
	private function setPrimaryKey($primary_key)
	{
		if($primary_key == NULL) {
			$this->primary_key = $this->db->query("SHOW KEYS FROM `" . $this->table . "` WHERE Key_name = 'PRIMARY'")->row()->Column_name;
		} else {
			$this->primary_key = $primary_key;
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

	public function getByPK($id)
	{
		return $this->get_record( array($this->primary_key => $id) );
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

	public function updateByPK($data, $id)
	{
		return $this->update($data, array($this->primary_key => $id));
	}

	public function delete($where)
	{
		return $this->db->delete($this->table, $where);
	}

	public function deleteByPK($id)
	{
		return $this->delete( array($this->primary_key => $id) );
	}

	public function incrementByPK($field, $id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->set($field, $field . '+1', FALSE);
		return $this->db->update($this->table);
	}
}