<?php
/**
 * Database model
 *
 * @author Adrian Rajczyk
 */
class CalorieCalc {
 
    private $tableName;
    private $wpdb;
 
    public function __construct() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->tableName = $prefix . "calorie_calc";
        $this->wpdb = $wpdb;
    }
 
    /**
     * Get all records from database
     * @global $wpdb $wpdb
     * @return array
     */
    public function getAll() {
        $query = "SELECT * FROM  " . $this->tableName . " ORDER BY id DESC;";
        return $this->wpdb->get_results($query, ARRAY_A);
    }
 
    /**
     * Insert new record to database
     * @global $wpdb $wpdb
     * @param array $data
     */
    public function add($data) {
        $this->wpdb->insert($this->tableName, $data, array('%s', '%s'));
    }
 
    /**
     * Delete all records from database
     * @global $wpdb $wpdb
     */
    public function deleteAll() {
        $sql = "TRUNCATE TABLE " . $this->tableName;
        $this->wpdb->query($sql);
    }
 
}
 
?>