<?php 
    function CleanString($input)
    {
        $input = str_replace("'", "\'", $input);
        return $input;
    }
?>