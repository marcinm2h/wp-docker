<?php
/*
  Plugin Name: WP calorie calculator
  Version: 0.1.1
  Description: Calorie calculator
  Author: Adrian Rajczyk and Marcin Moch
 */

define('RMLC_PATH', plugin_dir_path( __FILE__));
require RMLC_PATH . 'model/calorie_calc.php';

$capability = 'ccalc_admin';

function add_ccalc_capability($role_name) {
    $role = get_role($role_name);
    if ($role) {
        $role->add_cap($capability, true );
    }
}

function ccalc_install() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $ccalc_tablename = $prefix . "calorie_calc";
    $ccalc_db_version = "1.0";
 
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ccalc_tablename . "'") != $ccalc_tablename) {
        $query = "CREATE TABLE " . $ccalc_tablename . " ( 
        id int(9) NOT NULL AUTO_INCREMENT, 
        exercise TEXT NOT NULL,  
        kcal int(9) NOT NULL,  
        PRIMARY KEY  (id)
        )";
 
        $wpdb->query($query);
 
        add_option("ccalc_db_version", $ccalc_db_version);
    }
    foreach (array("administrator", "consultant") as $role) {
        add_ccalc_capability($role);
    }
}
register_activation_hook(__FILE__, 'ccalc_install');

function ccalc_uninstall() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $ccalc_tablename = $prefix . "calorie_calc";
    $query ='DROP TABLE '.$ccalc_tablename;
        $wpdb->query($query);
}
register_deactivation_hook(__FILE__, 'ccalc_uninstall');

function ccalc_plugin_menu() {
    add_menu_page(
        'Calorie Calculator',
        'Calorie Calculator',
        $capability,
        'ccalc_exercises'
    );
	add_submenu_page(
        'ccalc_settings',
        __('Exercises'),
        __('Exercises'),
        'edit_posts',
        'ccalc_exercises',
        'ccalc_exercises',
        null
    );
}
add_action('admin_menu', 'ccalc_plugin_menu');

function ccalc_exercises() {
    $model = new CalorieCalc();
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
        <td>' . __('exercise') . '</td><td>' . __('kcal/min') . '</td><td>' . __('delete') . '</td>
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


/**
 *	API
 *	GET  /calorie-calc/v1/list - get current exercises with kcal per hour
 *	POST /calorie-calc/v1/export - export exercises data to CSV file
 */
add_action('rest_api_init', function() {
  register_rest_route('calorie-calc/v1', 'list', array(
                'methods'  => 'GET',
                'callback' => 'get_calc_data'
      ));
	  
});

add_action('rest_api_init', function() {
  register_rest_route('calorie-calc/v1', 'export', array(
                'methods'  => 'POST',
                'callback' => 'export_calc_data'
      ));
	  
});

/**
 * Create API for get exercises from database
 * $result - json in format:
 *[
 *  {
 *    "id": "1",
 *    "exercise": "test",
 *    "kcal": "4542"
 *  }
 *]
 */
function get_calc_data (){
	$model = new CalorieCalc();
    $result = $model->getAll();

    return new WP_REST_Response($result, 200);
 }
 
 /**
  * Create API for export exercises data
  * $request - json format:
  *			"exercises: : [
  *				{ 
  *					"name" : "exercise_name", 
  *					"time: : "time_in_minutes", 
  *					"kcal" : "kcal"
  *				}
  *			]
  */
function export_calc_data($request){
    $header = array('id', 'name', 'minutes', 'kcal/min', 'kcal');
	$out = array($header);
    $id = 0;

	foreach($request['exercises'] as $exercise) {
		array_push(
            $out,
            array(
                ++$id,
                $exercise['name'],
                $exercise['minutes'],
                $exercise['kcal/min'],
                $exercise['kcal']
            )
        );
    }

	return new WP_REST_Response(array_to_csv_download($out, "exercises.csv"), 200);
}

function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    $resource = fopen('php://output', 'w');
    foreach ($array as $line) {
        fputcsv($resource, $line, $delimiter);
    }
    $resource = fclose();

	return $resource;
}

function ccalc_render_client() {
    $template = '<div id="ccalc_root"></div>';
    wp_enqueue_script('preact/htm', 'https://unpkg.com/htm@3.0.1/preact/standalone.umd.js');
    wp_enqueue_script('ccalc-client-js', plugins_url('client/index.js', __FILE__));

    return $template;
}
add_shortcode('ccalc_client', ccalc_render_client);

?>