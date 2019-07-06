<?php
require_once '../includes/CleanString.php';
class Serializer {
   
    static function Serialize($QueryResults) {
        $output = "";
        
        if(!$QueryResults)
            $output = '{"result" : "fail"}';
        else 
        {        
            $output = '{ "data" : [';
            
            while ($row = $QueryResults->fetch_assoc()) {
                $output = $output . json_encode($row) . ",";
            }
            $output = substr($output, 0, -1);//remove the last comma
            $output = $output . '],';
            $output = $output . '"result" : "success"';
            $output = $output . '}';
        }
        return $output;
    }
    
    static function JsonToInsert($TableName, $JsonObject){
        
        $Query = "";
        $json = json_decode($JsonObject, TRUE);
        
        $Query = "INSERT INTO " . CleanString($TableName) . " (";
        
        $allKeys = array_keys($json);
        
        for($i = 0; $i < count($allKeys); $i++)
        {
            $Query = $Query.CleanString($allKeys[$i]);
            if($i != count($allKeys) - 1)
                $Query = $Query.", ";
        }
        
        $Query = $Query.") VALUES(";
        
        for($i = 0; $i < count($json); $i++)
        {
            $Query = $Query.CleanString($json[$allKeys[$i]]);
            if($i != count($json) - 1)
                $Query = $Query.", ";
        }
        
        $Query = $Query.");";
        
        return $Query;
    }
    
    static function JsonToUpdate($TableName, $JsonObject, $Criteria){
        
        $Query = "";
        $json = json_decode($JsonObject, TRUE);
       
        $Query = "Update " . CleanString($TableName) . " SET ";
        
        $allKeys = array_keys($json);
        
        for($i = 0; $i < count($json); $i++)
        {
            $Query = $Query.CleanString($allKeys[$i])." = ".CleanString($json[$allKeys[$i]]);
            if($i != count($json) - 1)
                $Query = $Query.", ";
        }
        
        if($Criteria)
        {
            $Query = $Query . " WHERE " . CleanString($Criteria);
        }
        
        $Query = $Query.";";
            
        return $Query;
    }
}
?>