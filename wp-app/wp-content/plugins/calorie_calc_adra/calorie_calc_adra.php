<?php
/*
  Plugin Name: WP calorie calculator created by Adrian Rajczyk
  Version: 0.1
  Description: Plugin adds calorie calculator
  Author: Adrian Rajczyk

 */
 
 /**
 * Tworzy tabele bazy danych dla pluginu
 * 
 * @global $wpdb
 */
 
define( 'RMLC_PATH', plugin_dir_path( __FILE__ ) );
require RMLC_PATH.'model/calorie_calc.php';

function ccalc_install() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $ccalc_tablename = $prefix . "calorie_calc";
 
    $ccalc_db_version = "1.0";
 
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ccalc_tablename . "'") != $ccalc_tablename) {
        $query = "CREATE TABLE " . $ccalc_tablename . " ( 
        id int(9) NOT NULL AUTO_INCREMENT, 
        exercise varchar(250) NOT NULL,  
        kcal int(9) NOT NULL,  
        PRIMARY KEY  (id)
        )";
 
        $wpdb->query($query);
 
        add_option("ccalc_db_version", $ccalc_db_version);
    }
}
register_activation_hook(__FILE__, 'ccalc_install');

/**
 * Usuwa tabele bazy danych
 *
 * @global $wpdb $wpdb
 */
function ccalc_uninstall() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $ccalc_tablename = $prefix . "calorie_calc";
    $query ='DROP TABLE '.$ccalc_tablename;
        $wpdb->query($query);
}
register_deactivation_hook(__FILE__, 'ccalc_uninstall');

/**
 * Dodaje pozycje do menu w PA
 */
function ccalc_plugin_menu() {
    add_menu_page('Calorie Calculator', 'Calorie Calculator', 'administrator', 'ccalc_settings');
	add_submenu_page('ccalc_settings', __('Exercises'),  __('Exercises'), 'edit_posts', 'ccalc_exercises', 'ccalc_exercises', null);
}
add_action('admin_menu', 'ccalc_plugin_menu');

function ccalc_exercises() {
    $model=new CalorieCalc();
    if (isset($_POST['ccalc_exercises'])) {
        $model->deleteAll();
        foreach ($_POST['ccalc_exercises'] as $exc) {
            $model->add(array('exercise' => $exc['exercise'], 'kcal' => $exc['kcal']));
        }
    }
    $results = $model->getAll();
 
    echo '<h2>' . __('Exercises') . '</h2>';
    echo '<form action="?page=ccalc_exercises" method="post">';
    echo '<table class="form-table" style="width:auto;" cellpadding="10">
        <thead>
        <tr>
        <td>' . __('Exercise') . '</td><td>' . __('Kcal') . '</td><td>' . __('Delete') . '</td>
        </tr>
        </thead>
        <tbody class="items">';
    $i=0;
    foreach ($results as $row) {
        echo '<tr>
            <td><input name="ccalc_exercises['.$i.'][exercise]" type="text" value="' . $row['exercise'] . '" /></td>';
        echo '<td><input name="ccalc_exercises['.$i.'][kcal]" type="number" value="' . $row['kcal'] . '" /></td>';
        echo '<td><a class="delete" href="">' . __('Delete') . '</a></td>
            </tr>';
        $i++;
    }
    echo '</tbody><tr><td colspan="4"><a class="add" href="">' . __('Add') . '</a></td></tr>';
    echo '<tr><td colspan="4"><input type="submit" value="' . __('Save') . '" /></td></tr>';
    echo '</table>';
    echo '</form>';
     
    echo '
        <script type="text/javascript">
        jQuery(document).ready(function($) {
        $("table .delete").click(function() {
        $(this).parent().parent().remove();
        return false;
        });
        $("table .add").click(function() {
        var count = $("tbody.items tr").length+1;
        var code=\'<tr><td><input type="text" name="ccalc_exercises[\'+count+\'][exercise]" /></td><td><input type="text" name="ccalc_exercises[\'+count+\'][kcal]" /></td><td><a class="delete" href="">' . __('Delete') . '</a></td></tr>\';
        $("tbody.items").append(code);
        return false;
        });
        });
        </script>
        ';
}

add_action('rest_api_init', function () {
  register_rest_route( 'calorie-calc/v1', 'export', array(
                'methods'  => 'GET',
                'callback' => 'export_calc_data'
      ));
	  
});
function export_calc_data($request){
	  $out = array_to_csv_download(array(
		array(1,2,3,4), // this array is going to be the first row
		array(1,2,3,4)), // this array is going to be the second row
		"numbers.csv");
	  return new WP_REST_Response(200, $out);
	  }
function array_to_csv_download($array, $filename = "export.csv", $delimiter=",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'";');

    // open the "output" stream
    // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
    $f = fopen('php://output', 'w');

    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
	$f = fclose();
	return $f;
}   
   
?>