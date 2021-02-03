<?php 
    function RunQuery($query)
    {
        $servername = "localhost";
        $username = "";
        $password = "";
        $dbname = "";
        // Create MySQL connection
        $database = mysqli_connect($servername, $username, $password, $dbname);
        
        while($database->more_results() && $database->next_result())
        {
            $extraResult = $database->use_result();
            if($extraResult instanceof mysqli_result){
                $extraResult->free();
            }
        
        }
        $result = $database->query($query);
        
        if (!$result) {
            $_SESSION["error"] = trigger_error('Invalid query: ' . $database->error);
        }

        return $result;
    }
?>