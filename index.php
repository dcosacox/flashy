<?php
include "flashy_crud.php";
include "flashy_export.php";

$flashy = new flashyCrud();
$flashy_export = new flashyExport();

// ---------------- Part 1 -----------------------
// ---------------- Sample for loading full table -----------------------

echo "<h2>Getting full table</h2>";
$contents1 = $flashy->read('json1.json');
$contents = json_decode($contents1, true);
echo "<table>";
foreach($contents as $row){
    echo "<tr>";
    foreach($row as $column){
        echo "<td>$column</td>";
    }
    echo "</tr>";
}
echo "</table>";

// ------------ Sample for loading by id -----------------------

echo "<h2>Getting table when id = 2</h2>";
$contents2 = $flashy->read_by_key('json1.json', 'id', 2);
$contents = json_decode($contents2, true);
echo "<table>";
foreach($contents as $row){
    echo "<tr>";
        foreach($row as $column){
            echo "<td>$column</td>";
        }
    echo "</tr>";
}
echo "</table>";

// ------------ Sample for loading by id -----------------------

echo "<h2>Getting table when last_name = Mor</h2>";
$contents3 = $flashy->read_by_key('json1.json', 'last_name', 'Mor');
$contents = json_decode($contents3, true);
echo "<table>";
foreach($contents as $row){
    echo "<tr>";
        foreach($row as $column){
            echo "<td>$column</td>";
        }
    echo "</tr>";
}
echo "</table>";

// ------------ Sample for Editable table (add/update/delete records) -----------------------

echo "<h2>Editing table by row ID</h2>";
$contents4 = $flashy->read('json2.json');
$contents = json_decode($contents4, true);
echo "<table id='db_table'>";
$counter = 1;
foreach($contents as $table_key=>$row){
    echo "<tr>";
    if($table_key != 0){
        echo "<form method='POST' action='flashy_crud.php/update'>";
    }
        $columns = count($row);
        $last_id = 1;
        foreach($row as $key=>$column){
            if($table_key != 0 && $key != 0){
            echo "<td><input name='{$key}' value='{$column}'></td>";
            } else {
                echo "<th>$column</th>";
                $last_id = max($last_id, $column);
            }
        }
        if($table_key != 0){
            echo "<td><input type='hidden' name='file_name' value='json2.json'/>";
            echo "<input type='hidden' name='row_id' value='{$row[0]}'/><input type='submit' name='update' value='Update'><input type='submit' name='delete' value='Delete'></form></td>";
        }
        if(isset($_SESSION['msg']) && isset($_SESSION['row']) && $row[0] == $_SESSION['row'] && in_array($_SESSION['action'], ['updated','deleted'])){
            echo "<td style='color:red;'>". $_SESSION['msg'] . "</td>";
            session_destroy();
        }
        $counter++;
    echo "</tr>";
}
echo "<tr>";
echo "<form method='POST' action='flashy_crud.php/add'>";
$last_id++;
foreach($row as $key=>$column){

    if($table_key != 0 && $key != 0){
        echo "<td><input name='{$key}' value=''></td>";
        } else {
            echo "<th>{$last_id}</th>";
        }
        
    
}
    echo "<td><input type='hidden' name='file_name' value='json2.json'/>";
    echo "<input type='hidden' name='0' value='{$last_id}'/>
            <input type='submit' value='Add row' /></form></td>";
if(isset($_SESSION['msg']) && isset($_SESSION['added'])){
    echo "<td style='color:red;'>". $_SESSION['msg'] . "</td>";
    session_destroy();
}
echo "</tr></table>";

// -------------------- Sample for create empty table --------------------------

echo "<h2>Create file</h2>";
echo "<form method='POST' action='flashy_crud.php/create'>";
echo "<input type='text' name='file_name' placeholder='File name'/>";
echo "<input style='width:300px;' name='columns' type='text' placeholder='Enter column names comma separated'/>";
echo "<input type='submit' caption='Submit'>";
if(isset($_SESSION['msg']) && isset($_SESSION['created'])){
    echo "<td style='color:red;'>". $_SESSION['msg'] . "</td>";
    session_destroy();
}
echo "</form>";

// ---------------- Part 2 ------- using export class -----------

echo "<h1>Export class</h1>";
echo "<h2>Choose the records to export</h2>";
echo "<h3>The file json3.json has the following records</h3>";

echo "<table border='1'>";
echo "<form method='POST' action='flashy_export.php/export'>";

$contents5 = $flashy->read('json3.json');

$table_keys = json_decode($contents5)[0];
foreach($table_keys as $key=>$value) {
    echo "<tr>";
    echo "<th>$value</th>";
    echo "<td>Export <input type='checkbox' name='{$key}'></td>";
    echo "</tr>";
}
echo "<input type='hidden' name='file_name' value='json3.json'/>";
echo "</table>";
echo "<input type='submit' value='Export settings' />";
echo "</form>";

if(isset($_SESSION['file_name'])){
    echo "<span style='color:red;'>". $_SESSION['file_name'] . ' ' . $_SESSION['msg'] . "</span></br>";
    echo "The file data is: " . $flashy->read($_SESSION['file_name']);
}
?>