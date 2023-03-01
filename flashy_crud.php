<?php
session_start();
defined('MAIN_FOLDER') || define ('MAIN_FOLDER', '/flashy');
class flashyCrud {


// The private function

private function read_file($file_name){
    return file_get_contents($file_name);
}

private function save_file($file_name, $file_content){
    return file_put_contents($file_name, $file_content, LOCK_EX);
}

/**
 * @date 2023-03-01
 * @param {any} $file_name - The name of the file
 * @param {any} $file_columns - The fields of the DB (comma separated values to create the first record)
 * @param {any} $return=false - Set true if need to return to other function 
 * @param {any} $destroy_existing_file=false - Turn to true to delete existing file with same name
 * @returns According to param 3, to main page (if false) or to calling function (if true)
 */
private function create_file($file_name, $file_columns, $return = false, $destroy_existing_file = false){
    $columns = explode(',', $file_columns); // convert to array
    $columns = array_map('trim', $columns); // clean spaces
    $file_content = json_encode([$columns]); // create basic file
    
    if(!str_ends_with($file_name, '.json')){
        $file_name = $file_name . '.json'; // add json extension if missing
    }
    
    if($destroy_existing_file){
        unlink($file_name); //if 4th param is true remove old file
    }

    if(!file_exists($file_name)){
        if(file_put_contents($file_name, $file_content, LOCK_EX)){
            $_SESSION['msg'] = "File $file_name created successfully";
            $_SESSION['created'] = "created";
        }
    }  else {
        $_SESSION['msg'] = "Unable to create file $file_name";
        $_SESSION['created'] = "created";
    }
    
    if(!$return) {
        header("Location: ". MAIN_FOLDER);
        die();
    } else {
        return;
    }
    
}

/**
 * @date 2023-03-01
 * @param {any} $file_name - The name of the file
 * @param {any} $new_record_content - An array to add to the end of the file 
 * @param {any} $return=false - Set true if need to return to other function 
 * @returns According to param 3, to main page (if false) or to calling function (if true)
 */
private function add_record($file_name, $new_record_content, $return = false){
    $content = $this->read_file($file_name);
    $content_arr = json_decode($content, true);
    
    unset($new_record_content['file_name']);

    $new_sorted_record_array = [];
    for($i = 0; $i < count($new_record_content); $i++){
        if(isset($new_record_content[$i])){
            $new_sorted_record_array[] = $new_record_content[$i];
        } else {
            $_SESSION['msg'] = 'Error adding record';
            $_SESSION['add'] = 'add';
        }
        
    }
    
    $content_arr[] = $new_sorted_record_array;
    $file_content = json_encode($content_arr);

    file_put_contents($file_name, $file_content, LOCK_EX);
    $_SESSION['msg'] = 'Record added successfully';
    $_SESSION['action'] = 'add';
    $_SESSION['row'] = count($content_arr);

    if(!$return) {
        header("Location: ". MAIN_FOLDER);
        die();
    } else {
        return;
    }
}

// Public functions 

function read($file_name){
    return $this->read_file($file_name);
}

function save($file_name, $file_content){
    return $this->save_file($file_name);
}

function create($file_name, $file_columns, $return = false, $destroy_existing_file = false){
    return $this->create_file($file_name, $file_columns, $return, $destroy_existing_file);
}

function add($file_name, $record_content, $return = false){
    return $this->add_record($file_name, $record_content, $return);
}


/**
 * @date 2023-03-01
 * @param {any} $file_name - The name of the file
 * @param {any} $key - The name of the column to look for
 * @param {any} $value - The value of such column
 * @returns json containing the filtered DB
 */
function read_by_key($file_name, $key, $value){
    $content = $this->read_file($file_name);
    $content_arr = json_decode($content, true);
    
    $key_index = array_keys($content_arr[0], $key);
    if(!count($key_index)){
        return json_encode(['status'=>'error','msg' => "key $key not found"]);
    }
    $key_index = $key_index[0];
    $new_content  = [$content_arr[0]];
    
    foreach($content_arr as $db_key => $db_record){
        if($db_record[$key_index] == $value){
            $new_content[] = $content_arr[$db_key];
        }
    }
   
    return json_encode($new_content);
}

/**
 * @date 2023-03-01
 * @param {any} $file_name - The name of the file
 * @param {any} $row_id - The row to update
 * @param {any} $new_arr - The new values
 * @returns to index
 */
function update_by_key($file_name, $row_id, $new_arr){
    $content = $this->read_file($file_name); // get the whole file
    $content_arr = json_decode($content, true); // parse the file into array
 
    unset($new_arr['file_name'], $new_arr['row_id'], $new_arr['update']);
    $new_file_content  = [];

    foreach($content_arr as $db_record_key=>$db_record){
        if($db_record[0] == $row_id){ // found the key to be replaced
            $new_file_content[] = array_merge([$row_id], $new_arr);
        } else {
            $new_file_content[] = $db_record; // keep the old record
        }
    }

    $this->save_file($file_name, json_encode($new_file_content));
    $_SESSION['msg'] = 'done';
    $_SESSION['row'] = $row_id;
    $_SESSION['action'] = 'updated';
    header("Location: ". MAIN_FOLDER);
    die();
}

/**
 * @date 2023-03-01
 * @param {any} $file_name - The name of the file
 * @param {any} $row_id - The row to update
 * @param {any} $new_arr - The new values
 * @returns to index
 */
function delete_by_key($file_name, $row_id, $new_arr){
    $content = $this->read_file($file_name); // get the whole file
    $content_arr = json_decode($content, true); // parse the file into array
 
    unset($new_arr['file_name'], $new_arr['row_id'], $new_arr['delete']);
    $new_file_content  = [];

    foreach($content_arr as $db_record_key=>$db_record){
        if($db_record[0] == $row_id){ // found the key to be deleted
            unset($content_arr[$db_record_key]);
        } else {
            $new_file_content[] = $db_record; // keep the old record
        }
    }

    $this->save_file($file_name, json_encode($new_file_content));
    $_SESSION['msg'] = 'done';
    $_SESSION['row'] = count($new_file_content);
    $_SESSION['action'] = 'deleted';
    header("Location: ". MAIN_FOLDER);
    die();
}


}

if(isset($_SERVER['PATH_INFO'])){
    $flashy = new flashyCrud();
    switch($_SERVER['PATH_INFO']){
        case '/update':
            if(isset($_POST['update'])) {
                $flashy->update_by_key($_POST['file_name'], $_POST['row_id'], $_POST);
            }
            if(isset($_POST['delete'])) {
                $flashy->delete_by_key($_POST['file_name'], $_POST['row_id'], $_POST);
            }
        break;
        case '/create':
            $flashy->create($_POST['file_name'], $_POST['columns']);
            break;
        case '/add':
            $flashy->add($_POST['file_name'], $_POST);
            break;
        default:
            break;
            
    }

}


?>