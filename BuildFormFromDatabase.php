<?php
    $root = $_SERVER['DOCUMENT_ROOT'];

    require_once $root . '/CarProgress/Controller/LoginControllers/validateLogin.php';
    require_once $root . '/CarProgress/Controller/includes/RunQuery.php';
    
    class PageBuilder
    {
        public static function BuildForm($page)
        {
            $form = "<div class=\"Selection\">";
            $query = "CALL GetPageSections('".$_SESSION["Token"]."','$page');";

            $result = RunQuery($query);
            
            $sections = array();
            $index = 0;
            $x = 0;
            $y = 0;
            //if I retrieved a result from my query
            while($row = $result->fetch_assoc()) {
                $sections[$index] = $row["Section"];
                $index++;
            }
            
            for($i = 0; $i < $index; ++$i)
            {
                $query = "CALL GetNumColumnsForPage('$page', '$sections[$i]');";

                $result = RunQuery($query);

                $numColumns = 0;
                $numRows = 0;

                //if I retrieved a result from my query
                while($row = $result->fetch_assoc()) {
                    $numColumns = $row["NumColumns"]  + 1;
                }
                
                $query = "CALL GetNumRowsForPage('$page', '$sections[$i]');";
                
                $result = RunQuery($query);
                
                //if I retrieved a result from my query
                while($row = $result->fetch_assoc()) {
                    $numRows = $row["NumRows"] + 1;
                }
                
                //add 1 to both since spl fixed array is inclusive 0 counted (3) = (0,1,2,3)
                //and the rows and columns are 0 counted. numRows = 0 means there is 1 row, null = none
                $output = array();

                for($x = 0; $x  < $numColumns; ++$x)
                {
                    for($y = 0; $y < $numRows; ++$y)
                    {
                        try {
                                                    
                            $query = "CALL GetFieldTypeFromPageSection('".$_SESSION["Token"]."', '$page', '$sections[$i]', $x, $y)";

                            $result = RunQuery($query);
                            
                            if($result)
                            {
                                //if I retrieved a result from my query
                                while($row = $result->fetch_assoc()) 
                                {
                                    $output[$x][$y] = "";
                                    
                                    if($row["Type"] == "String")
                                    {
                                        if($row["HasAttributes"] == 1)
                                        {
                                            
                                            $query = "CALL GetAttributesFor(".$row["Field"].")";
                                            $subResult = RunQuery($query);
                                            while($subRow = $subResult->fetch_assoc())
                                            {                                            
                                                if($subRow["Type"] == "Button")
                                                    $output[$x][$y] = $output[$x][$y] . "<input type=\"button\" value=\"".$row["Label"]."\" name=\"".$row["Label"]."\">";                                                                   
                                                else if($subRow["Type"] == "TextBox")
                                                {
                                                    $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": <input type=\"text\" value=\"\" name=\"".$row["Label"] ."\"";
                                                    if($row["ReadOnly"] == 1)
                                                        $output[$x][$y] = $output[$x][$y] . " readonly"; 
                                                    
                                                    $output[$x][$y] = $output[$x][$y] . ">";
                                                }
                                                else if($subRow["Type"] == "Image")
                                                {
                                                    $output[$x][$y] = $output[$x][$y] . "<img src=\"../Images/". $subRow["Value"] ."\"></img>";
                                                }
                                                else if($subRow["Type"] == "RichTextBox")
                                                {
                                                    $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": <textarea value=\"\" name=\"".$row["Label"] ."\"></textarea>";
                                                }
                                                else
                                                    $output[$x][$y] = $output[$x][$y] . $row["Label"];
                                            }
                                        }
                                        else
                                            $output[$x][$y] = $output[$x][$y] . $row["Label"];
                                    }
                                    else if($row["Type"] == "SelectList")
                                    {
                                        
                                        if($row["HasAttributes"] == 1)
                                        {
                                            $query = "CALL GetAttributesFor(".$row["Field"].")";
                                            $subResult = RunQuery($query);
                                            
                                            while($subRow = $subResult->fetch_assoc())
                                            {
                                                if($subRow["Type"] == "Table")
                                                    $Table = $subRow["Value"];
                                                else if($subRow["Type"] == "DataColumn")
                                                    $Data = $subRow["Value"];
                                                else if($subRow["Type"] == "DisplayColumn")
                                                    $Display = $subRow["Value"];
                                                else if($subRow["Type"] == "Role")
                                                    $Role = $subRow["Value"];
                                                            
                                            }
                                            $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": ";
                                            
                                            if(ISSET($Table) && ISSET($Display) && ISSET($Data))
                                            {
                                                
                                                $output[$x][$y] = $output[$x][$y] . "<select name=\"" . $row["Label"] . "\">";
                                                $query = "SELECT t.$Data AS Data, t.$Display AS Display FROM $Table t";
                                                if(ISSET($Role) && $Table == "Person")
                                                {
                                                    $query = $query . " INNER JOIN PersonRole pr ON pr.PersonID = t.PersonID INNER JOIN Lu_Role r ON r.RoleID = pr.RoleID WHERE r.Name = '$Role' AND pr.RemovalDate IS null";
                                                }
                                                    $subResult = RunQuery($query);
                                                    
                                                    while($subRow = $subResult->fetch_assoc())
                                                    {
                                                        $output[$x][$y] = $output[$x][$y] . "<option value=\"" . $subRow["Data"] . "\">" . $subRow["Display"] . "</option>";
                                                    }
                                                    
                                                    $output[$x][$y] = $output[$x][$y] . "</select>";
                                                    
                                                    $Table = null;
                                                    $Display = null;
                                                    $Data = null;
                                            }                                       
                                        }
                                        
                                        else
                                            $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": <select name=\"".$row["Label"]."\"></select>";
                                    }
                                    else if($row["Type"] == "Boolean")
                                    {
                                        if($row["HasAttributes"] == 1)
                                        {
                                            $query = "CALL GetAttributesFor(".$row["Field"].")";
                                            $subResult = RunQuery($query);
                                            $fieldType = "";
                                            while($subRow = $subResult->fetch_assoc())
                                            {
                                                if($subRow["Type"] == "RadioButton")
                                                    $fieldType = "radio";
                                                if($subRow["Type"] == "CheckBox")
                                                    $fieldType = "checkbox";
                                                
                                                if($subRow["Type"] == "Table")
                                                    $Table = $subRow["Value"];
                                                else if($subRow["Type"] == "DataColumn")
                                                    $Data = $subRow["Value"];
                                                else if($subRow["Type"] == "DisplayColumn")
                                                    $Display = $subRow["Value"];
                                                else if($subRow["Type"] == "Role")
                                                    $Role = $subRow["Value"];
                                            }
                                            if(ISSET($Table) && ISSET($Display) && ISSET($Data))
                                            {                                           
                                                $query = "SELECT t.$Data AS Data, t.$Display AS Display FROM $Table t";
                                                if(ISSET($Role) && $Table == "Person")
                                                    $query = $query . " INNER JOIN PersonRole pr ON pr.PersonID = t.PersonID INNER JOIN Lu_Role r ON r.RoleID = pr.RoleID WHERE r.Name = '$Role'";
                                                    $subResult = RunQuery($query);
                                                    
                                                    while($subRow = $subResult->fetch_assoc())
                                                    {
                                                        $output[$x][$y] = $output[$x][$y] . "<input type=\"" . $fieldType . "\" name=\"".$row["Label"]."\" value=\"". $subRow["Data"] ."\">" . $subRow["Display"] . "</input>";
                                                    }
                                                    
                                                    $output[$x][$y] = $output[$x][$y] . "</select>";
                                                    
                                                    $Table = null;
                                                    $Display = null;
                                                    $Data = null;
                                            }     
                                            else 
                                                $output[$x][$y] = $output[$x][$y] . "<input type=\"" . $fieldType . "\" name=\"".$sections[$i]."\" value=\"\">" . $row["Label"] . "</input>";
                                                
                                        }
                                        else
                                            $output[$x][$y] = $output[$x][$y] . "<input type=\"checkbox\" name=\"".$row["Label"]."\">";
                                            
                                    }
                                    else if($row["Type"] == "Integer")
                                    {
                                        $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": <input type=\"number\" name=\"".$row["Label"]."\">";
                                    }
                                    else if($row["Type"] == "Date")
                                    {
                                        $output[$x][$y] = $output[$x][$y] . $row["Label"] . ": <input type=\"date\" name=\"".$row["Label"]."\">";
                                    }
                                }
                            }
                        } 
                        catch (Exception $e) 
                        {
                        }                       
                    }
                }   
                
                for($y = 0; $y < $numRows; ++$y)
                {
                    $form = $form . "<div class=\"Row Row$y\">";
                    for($x = 0; $x  < $numColumns; ++$x)
                    {
                        if(isset($output[$x][$y]))
                        {
                            $form = $form . "<div class=\"Field Column$x Column $page " . $sections[$i] . "\">";
                            $form = $form . $output[$x][$y];
                            $form = $form . "</div>";
                            $output[$x][$y] = "";
                        }
                            
                    }
                    $form = $form . "</div><br />";
                }
            }
                    
            return $form . "</div>";
        }               
    }
?>