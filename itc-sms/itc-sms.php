<?php
/**
Plugin name: itc-sms
Version: 1.0
Description: Gestione liste newsletter via sms e/o email
Author: Francesco Casadei Lelli
**/


/** ***************** **/
/**  INIT AND GLOBALS **/
/** ***************** **/

/** Deny direct access **/
defined('ABSPATH') or die('Not today');

/** WP_List_Table inclusion **/
if(!class_exists( 'WP_List_Table' )){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/** CSV FILE NAME (date based) **/
global $itc_sms_csv_name;
$date_array = getdate();
// File name format: 'itc iscritti gg/mm/aaaa.csv'
$itc_sms_csv_name = "itc iscritti " . $date_array['mday'] . "-" . $date_array['mon'] . "-" . $date_array['year'] . ".csv";

/** TABLE NAME **/
global $itc_sms_table_name;
$itc_sms_table_name = "itc_sms_users";


/** ************ **/
/**  MAIN CLASS  **/
/** ************ **/

class itc_sms{

    /**  PROPERTIES  **/
    /** Static var for Singleton pattern **/
    private static $itc_sms_instance = null;


    /**  SINGLETON PATTERN  **/
    public static function getInstance(){
        if(self::$itc_sms_instance == null){
            $c = __CLASS__;
            self::$itc_sms_instance = new $c;
        }
        return self::$itc_sms_instance;
    }


    /** ******************* **/
    /**  CLASS CONSTRUCTOR  **/
    /** ******************* **/

    /** Init actions, hooks, filters and Ajax requests **/
    private function __construct(){	
		/** Includes php files **/
		add_action('admin_menu', array($this,'itc_html_output_load'));
		
		/** Add menu pages **/
		add_action('admin_menu', array($this,'itc_safe_hook'));

        /** Includes scripts **/
        add_action('admin_enqueue_scripts', array($this,'itc_sms_script_load'));

        /** AJAX requests **/
        add_action('wp_ajax_send_data', array($this,'send_data'));
        add_action('wp_ajax_delete_data', array($this,'delete_data'));
        add_action('wp_ajax_modify_data', array($this,'modify_data'));
        add_action('wp_ajax_export_data', array($this,'export_data'));
        add_action('wp_ajax_import_data', array($this,'import_data'));

        /** TABLE CREATION **/
        /** Globals **/
        global $itc_sms_mysqli, $itc_sms_table_name;

        // Connection to database
        $itc_sms_mysqli = itc_sms::db_connection();
        
        // Query
        $query = "CREATE TABLE $itc_sms_table_name(
              ID INT NOT NULL AUTO_INCREMENT,
              Nome VARCHAR(30) NOT NULL,
              Cognome VARCHAR(30) NOT NULL,
              Telefono VARCHAR(13) NOT NULL,
              email VARCHAR(40),
              del INT(1) NOT NULL,
              PRIMARY KEY(ID))";

        // Send query
        $itc_sms_mysqli->query($query);
    }


    /** ************************* **/
    /**  SCRIPT AND FILES LOADER  **/
    /** ************************* **/

    function itc_sms_script_load(){
        wp_enqueue_script('ajax-script', plugins_url('/js/itc-sms-script.js', __FILE__), array('jquery'));
        wp_localize_script('ajax-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
	
	function itc_html_output_load(){
		require_once("inc/itc-sms-html.php");
	}


    /** **************** **/
    /**  MENU AND PAGES  **/
    /** **************** **/

    /** Add menu and submenus to the admin bar **/
    public function itc_safe_hook(){
        /** Main menu **/
        add_menu_page(
            __('ITC SMS', 'text-domain'),
            __('ITC SMS', 'text-domain'),
            'manage_options',
            'itc-sms-menu',
            'itc_sms::display_registered_user',
            'dashicons-id-alt',
            20
        );
        /** [overriding main menu] Submenu: visualise registered user **/
        add_submenu_page(
            'itc-sms-menu',
            __('Iscritti', 'text-domain'),
            __('Iscritti', 'text-domain'),
            'manage_options',
            'itc-sms-menu',
            'itc_sms::display_registered_user'
        );
        /** Submenu: register user **/
        add_submenu_page(
            'itc-sms-menu',
            __('Nuovo iscritto', 'text-domain'),
            __('Nuovo iscritto', 'text-domain'),
            'manage_options',
            'itc-sms-new-subscriber',
            'itc_sms::display_insert_user'
        );
        /** Submenu: export data **/
        add_submenu_page(
            'itc-sms-menu',
            __('Esporta dati', 'text-domain'),
            __('Esporta dati', 'text-domain'),
            'manage_options',
            'itc-sms-export',
            'itc_sms::display_export_user'
        );
        /** Submenu: import data **/
        add_submenu_page(
            'itc-sms-menu',
            __('Importa dati', 'text-domain'),
            __('Importa dati', 'text-domain'),
            'manage_options',
            'itc-sms-import',
            'itc_sms::display_import_user'
        );
    }


    /** ******************** **/
    /**  DATABASE FUNCTIONS  **/
    /** ******************** **/

    /** Connection to database **/
    public function db_connection(){
        global $itc_sms_mysqli;

        $itc_sms_mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($itc_sms_mysqli->connect_error) return ('');
        else return $itc_sms_mysqli;
    }

    /** Add into the table the received data **/
    public function send_data(){
        global $itc_sms_mysqli, $itc_sms_table_name;

        if (isset($_POST["name"])) {
            $name = trim($_POST["name"]);
            $surname = trim($_POST["surname"]);
            $telephone = trim($_POST["telephone"]);
            $email = trim($_POST["email"]);

            // Query creation
            $query = "INSERT 
                      INTO $itc_sms_table_name (Nome,Cognome,Telefono,email,del)
                      VALUES ('$name','$surname','$telephone','$email',0)";

            // Query send
            $itc_sms_mysqli->query($query);
        }
    }

    /** Modify selected user's data **/
    public function modify_data(){
        global $itc_sms_mysqli, $itc_sms_table_name;

        if(isset($_POST["mod_id"])){
            $id = $_POST["mod_id"];
            $name = trim($_POST["new_name"]);
            $surname = trim($_POST["new_surname"]);
            $telephone = trim($_POST["new_telephone"]);
            $email = trim($_POST["new_email"]);

            // Query creation
            $query = "UPDATE $itc_sms_table_name
                      SET Nome = '$name', 
                          Cognome = '$surname', 
                          Telefono = '$telephone', 
                          email = '$email' 
                      WHERE ID = $id";

            // Query send
            $itc_sms_mysqli->query($query);
        }
    }

    /** Remove selected user from database **/
    public function delete_data(){
        global $itc_sms_mysqli, $itc_sms_table_name;

        if(isset($_POST["del_id"])){
            $del_arr = array();
            $del_arr = $_POST["del_id"];

            $i = 0;
            do{
                $del_id = $del_arr[$i];

                // Query creation
                $query = "UPDATE $itc_sms_table_name
                          SET del = 1
                          WHERE ID = $del_id";

                // Query send
                $itc_sms_mysqli->query($query);

            } while(++$i < count($del_arr));
        }
    }

    /** Create a csv file of the table **/
    public function export_data(){
        global $itc_sms_csv_name, $itc_sms_mysqli, $itc_sms_table_name;

        if(isset($_POST["sub_flag"])){
            $sub_flag = $_POST["sub_flag"];

            // Query creation
            if($sub_flag){
                $query = "SELECT * FROM $itc_sms_table_name WHERE del != 1
                          ORDER BY Nome ASC";
            }
            else{
                $query = "SELECT * FROM $itc_sms_table_name
                          ORDER BY Nome ASC";
            }

            // Send query
            $export_data = $itc_sms_mysqli->query($query);

            // File creation
            $fp = fopen($itc_sms_csv_name, 'w');
            $title_array = ["ID","Nome","Cognome","Telefono","email","Iscritto"];
            fputcsv($fp,$title_array,',','"');
            while ($row = mysqli_fetch_array($export_data,MYSQLI_ASSOC)){
                if($row['del'] == "0") $row['del'] = "si";
                else $row['del'] = "no";
                fputcsv($fp, array_values($row), ',', '"');
            }
            fclose($fp);
            echo 0;
        } else echo 1;
    }

    /** Receive a csv file and updates the user's table **/
    public function import_data(){
        global $itc_sms_mysqli, $itc_sms_table_name;

        if(isset($_POST["file"])){
            $data_string = $_POST["file"];
            // From string to array
            $data_array = explode(",",$data_string);
            $n_cell = count($data_array);

            for($i = 6; $i < $n_cell; $i+=6){
                // Data extraction
                $name = trim($data_array[$i+1]);
                $surname = trim($data_array[$i+2]);
                $telephone = trim($data_array[$i+3]);
                $email = trim($data_array[$i+4]);

                // del normalisation
                $del_pos = $i+5;
                switch(trim($data_array[$del_pos])){
                    case "si": $data_array[$del_pos] = 0; break;
                    case "sì": $data_array[$del_pos] = 0; break;
                    case "Sì": $data_array[$del_pos] = 0; break;
                    case "Si": $data_array[$del_pos] = 0; break;
                    default:
                        $data_array[$del_pos] = 1;
                }

                // Query creation
                $query = "UPDATE $itc_sms_table_name
                          SET Nome = '$name', 
                              Cognome = '$surname', 
                              Telefono = '$telephone', 
                              email = '$email', 
                              del = $data_array[$del_pos]
                          WHERE ID = $data_array[$i]";

                // Send query
                $itc_sms_mysqli->query($query);
            }
        }
    }


    /** ******************* **/
    /**  DISPLAY FUNCTIONS  **/
    /** ******************* **/

    /** Register user box **/
    public function display_insert_user(){
		html_new_user();
    }

    /** Gets data and calls the registered users table function **/
    public function display_registered_user(){
		html_table_intestation();
        /** Table rendering */
        render_wp_admin_table();
        /** Modify user section **/
        html_modify_module();
    }

    /** Export page render **/
    public function display_export_user(){
        global $itc_sms_csv_name;
		html_export_user($itc_sms_csv_name);
	}

    /** Import page render **/
    public function display_import_user(){
        global $itc_sms_csv_name;
		html_import_user($itc_sms_csv_name);
        
    }

}


/** *********************** **/
/**  TABLE CLASS EXTENSION  **/
/** *********************** **/

class wp_admin_table extends WP_List_Table{


    /** ******************** **/
    /**  CLASS CONSTRUCTION  **/
    /** ******************** **/

    public function __construct() {

        parent::__construct( array(

            'singular'  => 'iscritto',     //singular name of the listed records

            'plural'    => 'iscritti',    //plural name of the listed records

            'ajax'      => true

        ) );
    }


    /** ******************** **/
    /**  COLUMNS PROPERTIES  **/
    /** ******************** **/

    public function get_columns(){
        $columns = array(
            'cb'        =>  '<input type=checkbox',
            'Name'      =>  'Nome',
            'Cognome'   =>  'Cognome',
            'Telefono' =>  'Telefono',
            'email'     =>  'email'
        );
        return $columns;
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="user[]" value="%s" />
                    <input style="display: none" id="name-%s" value="%s" />
                    <input style="display: none" id="surname-%s" value="%s" />
                    <input style="display: none" id="telephone-%s" value="%s" />
                    <input style="display: none" id="email-%s" value="%s" />',
            $item['ID'],$item['ID'],$item['Nome'],$item['ID'],$item['Cognome'],$item['ID'],$item['Telefono'],$item['ID'],$item['email']
        );
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'Name'  => array('Nome',false),
            'Cognome' => array('Cognome',false)
        );
        return $sortable_columns;
    }

    public function column_default($item, $column_name) {
        switch( $column_name ) {
            case 'Nome':
            case 'Cognome':
            case 'Telefono':
            case 'email':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    public function usort_reorder($a, $b) {
        // If no sort, default to title
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'Nome';
        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp( $a[$orderby], $b[$orderby] );
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    public function column_name($item) {
        $actions = array(
            'edit'    => sprintf('<a onclick="mod_user(%u)" href="#/edit/%u">Modifica</a>',$item['ID'],$item['ID']),
            'delete'  => sprintf('<a onclick="del_user(%u,%i)" href="">Rimuovi</a>',$item['ID'],0),
        );
        return sprintf('%1$s %2$s', $item['Nome'], $this->row_actions($actions));
    }


    /** ************** **/
    /**  BULK ACTIONS  **/
    /** ************** **/

    public function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Rimuovi selezionati'
        );
        return $actions;
    }


    /** ***************** **/
    /**  DEFAULT ACTIONS  **/
    /** ***************** **/

    /** If no users are found **/
    public function no_items() {
        _e( 'Nessun iscritto trovato' );
    }


    /** ********************* **/
    /**  GET AND SEARCH DATA  **/
    /** ********************* **/

    public function table_data(){
        global $itc_sms_mysqli, $itc_sms_table_name;

        if(isset($_POST['s'])){
            $search = $_POST['s'];
            $search = trim($search);

            $db_data = $itc_sms_mysqli->query("SELECT * FROM $itc_sms_table_name WHERE del=0 AND (Nome LIKE '$search%' OR Cognome LIKE '$search%')");
        } else $db_data = $itc_sms_mysqli->query("SELECT * FROM $itc_sms_table_name WHERE del=0");

        $n_row = mysqli_num_rows($db_data);

        if($n_row > 0){
            $i = 0;
            do{
                $data[$i] = mysqli_fetch_assoc($db_data);
                if(!strcmp($data[$i]['email'],"")){
                    $data[$i]['email'] = "-";
                }

            } while(++$i < $n_row);
        } else $data = array();

        mysqli_free_result($db_data);

        return $data;
    }


    /** ******************* **/
    /**  TABLE CONSTRUCTOR  **/
    /** ******************* **/

    public function prepare_items(){
        $columns = $this->get_columns();

        $hidden = array();

        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $db_data = $this->table_data();

        usort( $db_data, array( &$this, 'usort_reorder' ) );

        $total_users = count($db_data);

        $this -> set_pagination_args( array('total_items' => $total_users, 'per_page' => 0, 'total_pages' => 1));

        $this->items = $db_data;
    }

}


/** ********************* **/
/**  CLASS INSTANTIATION  **/
/** ********************* **/

/** Main class instantiation **/
$itc_sms_class = itc_sms::getInstance();

/** Table class extension instantiation **/
function render_wp_admin_table(){
    /** Class instantiation **/
    $wp_admin_table = new wp_admin_table();

    /** Gets data and prepares the table **/
    $wp_admin_table->prepare_items();

    /** Search box form **/
    ?><form name="search" method="post"><?php
    $wp_admin_table->search_box('Cerca', 'search');
    ?></form><?php

    /** Outputs the table HTML **/
    $wp_admin_table->display();
}