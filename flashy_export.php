<?php

class flashyExport {

    function __construct(){
        if(!class_exists('flashyCrud')){
            include('flashy_crud.php');
            $this->flashy = new flashyCrud();
        }
    }

    private function read_file($file_name){
        return file_get_contents($file_name);
    }

    function read($file_name){
        return $this->read_file($file_name);
    }

    /**
     * @date 2023-03-01
     * @param {any} $file_name - The name of the file
     * @param {any} $filter_fields - The columns to export
     * @returns To main page showing the new data 
     */
    function export($file_name, $filter_fields){
       unset($filter_fields['file_name']);

       $file_contents = $this->flashy->read($file_name);
       $file_contents_arr = json_decode($file_contents, true);

       $new_header = array_intersect_key($file_contents_arr[0], $filter_fields); // the new columns that we should keep

       // create file with the new headers
       $export_file_name = 'export_' . $file_name; // add prefix to original file name
       $this->flashy->create($export_file_name, implode(',',$new_header), true, true);
        
       unset($file_contents_arr[0]); // remove first record of file, the created file already contains it
       
       // Add every remaining value to the created
       $counter = 1;
       foreach($file_contents_arr as $file_record){
            $this->flashy->add($export_file_name, array_values(array_intersect_key($file_record, $filter_fields)), true);
            $counter++;
       }
 
       $_SESSION['file_name'] = $export_file_name;
       $_SESSION['msg'] = 'created successfully';
       $_SESSION['row'] = $counter;
       header("Location: /flashy");
        
    }


}

if(isset($_SERVER['PATH_INFO'])){
    $flashy_export = new flashyExport();
    
    switch($_SERVER['PATH_INFO']){
        case '/export':
            $flashy_export->export($_POST['file_name'], $_POST);
            break;
        default:
            break;
    }
}

?>