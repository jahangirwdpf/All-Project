<?php
ini_set('date.timezone', 'Europe/London');
ob_start();
$r_times = array();
$r_times[__LINE__] = microtime() . date("i:s");
error_reporting(0);
session_start();
header("Access-Control-Allow-Origin: *");

include '../language.php';

//db configaration
include '../config/configuration.php';

//SQL Query Class File
include '../class/class.my_sql.php';
$sql = new MYSQL_OPERATIONS();

//form validation here
include_once '../class/class.api.validation.php';
$validate = new validation();


$requested_uri = $_SERVER['REQUEST_URI'];
$requested_uri = explode('ooapi/', $requested_uri);
$requested_uri = explode('/', $requested_uri[1]);

$page = $requested_uri[0];
$page_value = @$requested_uri[1];
$data = array();
$currency = $config['currency'];
$r_times[__LINE__] = microtime() . date("i:s");
function safe_json_encode($value, $options = 0, $depth = 512)
{
    $encoded = json_encode($value, $options, $depth);
    if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
        $encoded = json_encode(utf8ize($value), $options, $depth);
    }
    return $encoded;
}

function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

function writeTxt($txt)
{
    $myfile = fopen("apilog.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $txt);
    $txt = "Jane Doe\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}
function textFileWrite($data, $fileName = "error.txt")
{
    $handle = fopen($fileName, 'a');
    if (is_array($data)) {
        foreach ($data as $key => $p) {
            @$new_data = @$key . '->' . $p . "\n";
            fwrite($handle, $new_data);
        }
    } else {
        if ($fileName == 'replyed.txt') {
            $data = date('d H:I:s A ') . $data;
        }
        fwrite($handle, $data . "\n");
    }
    fclose($handle);
}

if ($page == 'create_user') {
    // $name = $_GET['name'];


    // initialization
    $first_name = $sql->post('first_name');
    $last_name = $sql->post('last_name');
    $email_id = $sql->post('email_id');

    $data['status'] = 1;
    $data['code']    = 200;
    $data['message']    = 'success';


    $valid = true;
    $message = "";

    // validation
    if($sql->post('first_name') == null){
        $valid = false;
        $message .= " -> first name is required ";
    }

    if($sql->post('last_name') == null){
        $valid = false;
        $message .= " -> last name is required ";
    }

    if($sql->post('email_id') == null){
        $valid = false;
        $message .= " -> email is required ";
    }

    if(!$valid){

        $data['status'] = 0;
        $data['code']    = 403;
        $data['message']    = 'Invalid Request! '.$message;

    }
    else
    {
        // execution
        $insert = $sql->insert('user', array('first_name' => $first_name, 'last_name' => $last_name, 'email_id' => $email_id));

        if (!$insert) {
            $data['status'] = 0;
            $data['code']    = 500;
            $data['message']    = 'Internal Server Error!';
        } else {
            $data['status'] = 1;
            $data['code']    = 200;
            $data['message']    = 'User Created Successfully';
        }

    }

}elseif ($page == "get_all_user_list") {

    $data['status'] = 1;
    $data['code']    = 200;
    $data['message'] = "User List Genereted";
    $data['user_list']  = $sql->selectAll($table = 'user', $where = '');;

} elseif ($page == "minutes") {

    $data['status'] = 1;
    $data['msg']    = 'success';
    $data['message'] = "Hello Anupam";

} elseif ($page == "minute_update") {
    $data['status'] = 1;
    $data['msg']    = 'success';
    $id = $_POST['id'];
    $minute = $_POST['minute'];

    $update = $sql->update('tbl_minutes', array('minute' => $minute), array('id' =>  $id));

    if (!$update) {
        $data['status'] = 0;
        $data['msg']    = 'error';
    }
} elseif ($page == "add_minute") {
    $data['status'] = 1;
    $data['msg']    = 'success';
    $minute = $_POST['minute'];

    $insert = $sql->insert('tbl_minutes', array('minute' => $minute, 'status' => 1));

    if (!$insert) {
        $data['status'] = 0;
        $data['msg']    = 'error';
    }
}elseif ($page == "user_login") {
    $data['status'] = 1;
    $data['msg']    = 'success';


    $customer_email       = $sql->post('login_email');
    $customer_password    = $sql->post('login_password');
    $ipaddress            = $_POST['ip'];
    $device_id            = $_POST['deviceId'];
    $push_id              = $_POST['pushId'];
    $auth_code            = md5(uniqid(rand(), true));
    $timeNdate            = $general->getCurrentTimeNDate();
    $config = array(
        array(
            'field'   => $customer_email,
            'label'   => 'Customer email',
            'rules'   => 'need',
            'email'   => 'valid',
        ),
        array(
            'field'   => $customer_password,
            'label'   => 'Password',
            'rules'   => 'need',
            'min_length'        =>    '6',
            'max_length'        =>    '16',
            'alpha_numeric'     =>     TRUE
        )
    );

    $condition = $validate->set_rules($config);

    if ($condition == true) {
        $user_sql = $sql->query("SELECT  count(*) as `count_user`, id, name, email, phone, postcode, address  FROM `tbl_users` where `email` = '" . $customer_email . "' and `password` = '" . $customer_password . "' and `status` = '1'");

        if ($user_sql[0]['count_user'] == 1) {
            $table = 'tbl_user_login_log';
            $userlogdata = array(
                'uid'               =>  $user_sql[0]['count_user'],
                'admin_ip'          =>  $ipaddress,
                'login_time'        =>  $timeNdate,
                'auth_code'         =>  $auth_code,
                'device_id'         =>  $device_id
            );

            $record = $sql->insert($table, $userlogdata);
            $data['user']['auth_code'] = $auth_code;
            $data['user']['id'] = $user_sql[0]['id'];
            $data['user']['name'] = $user_sql[0]['name'];
            $data['user']['email'] = $user_sql[0]['email'];
            $data['user']['phone'] = $user_sql[0]['phone'];
            $data['user']['address'] = $user_sql[0]['address'];
            $data['user']['postcode'] = $user_sql[0]['postcode'];
            $data['user']['house_no'] = $user_sql[0]['house_no'];
            $home->save_device_push_id($device_id, $push_id, $user_sql[0]['id'], $timeNdate);
        } else {
            $data['status'] = 0;
            $data['msg'] =  "Username or password wrong.";
        }
    } else {
        $data['status'] = 0;
        $data['msg'] =  'error';
        $data['errors'] = $condition;
    }
} elseif ($page == 'user_registration') {
    $data['status'] = 1;
    $data['msg']    = 'success';
    $customer_name               =    $_POST['name'];
    $customer_email              =    $_POST['email'];
    $customer_password           =    $_POST['password'];
    $customer_phone              =    $_POST['phone'];

    $postcode                    =    $_POST['postcode'];
    $address                     =    $_POST['address'];
    $entry_date                  =    $order->getCurrentTimeNDate();

    $condition = true;
    $exist = $sql->getRow('tbl_users', 'email', $customer_email);

    if ($exist == false) {


        if ($condition == true) {
            $table_filed = array(
                'name'        =>    $customer_name,
                'email'       =>    $customer_email,
                'password'    =>    $customer_password,
                'phone'       =>    $customer_phone,
                'postcode'    =>    $postcode,
                'address'     =>    $address,
                'entry_date'  =>    $entry_date,
            );

            $insert_customer = $sql->insert('tbl_users', $table_filed, '', 'ID');

            if (is_numeric($insert_customer)) {
                $data['userid'] = $insert_customer;
                $email->emailbody_new_customer($customer_email);
            } else {
                $data['status'] = 0;
                $data['msg']    = 'error';
                $data['post'] = $_POST;
            }
        } else {
            $data['status'] = 0;
            $data['msg']    = 'Some problem there. please try again later';
            $data['errors'] = $condition;
            $data['post'] = $_POST;
        }
    } else {
        $data['status'] = 0;
        $data['msg']    = 'User already exist! Please check your email address and try again.';
        $data['post'] = $_POST;
    }
} elseif ($page == 'user_profile_update') {
    $data['status'] = 1;
    $data['msg']    = 'Profile Updated Successfully';
    $customer_id                 =    $_POST['user_id'];
    $customer_name               =    $_POST['name'];
    $customer_phone              =    $_POST['phone'];
    $postcode                    =    $_POST['postcode'];
    $address                     =    $_POST['address'];
    $condition = true;
    if ($condition == true) {
        $table_filed = array(
            'name'        =>    $customer_name,
            'phone'       =>    $customer_phone,
            'postcode'    =>    $postcode,
            'address'     =>    $address,
        );
        $where = array(
            'id' => $customer_id
        );
        $update_customer = $sql->update('tbl_users', $table_filed, $where);
        if ($update_customer == true) {
            $data['userid'] = $customer_id;
        } else {
            $data['status'] = 0;
            $data['msg']    = 'error';
            $data['post'] = $_POST;
        }
    } else {
        $data['status'] = 0;
        $data['msg']    = 'error';
        $data['errors'] = $condition;
        $data['post'] = $_POST;
    }
} elseif ($page == 'change_password') {
    $data['status'] = 1;
    $data['msg']        = 'Password Updated.';
    $customer_id        =    $_POST['user_id'];
    $current_password   =    $_POST['current_password'];
    $new_password       =    $_POST['new_password'];
    $existusers = $sql->selectAll('tbl_users', 'where id=' . $customer_id . ' and password=' . $current_password);
    if (count($existusers) == 1) {
        $table_filed = array(
            'password'        =>    $new_password,
        );
        $where = array(
            'id' => $customer_id
        );
        $update_customer = $sql->update('tbl_users', $table_filed, $where);
        if ($update_customer == true) {
            $data['userid'] = $customer_id;
        } else {
            $data['status'] = 0;
            $data['msg']    = 'error';
            $data['post'] = $_POST;
        }
    } else {
        $data['status'] = 0;
        $data['msg']        = 'Invalid user or current password.';
    }
} elseif ($page == 'userinfo') {
}



if (count($data) > 0) {
    $data['r_time'] = $r_times;
    header('Content-type:application/json;charset=utf-8');
    $data = safe_json_encode($data);
    echo stripslashes($data);
}