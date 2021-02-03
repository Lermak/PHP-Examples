<!-- 
The superclass that will act as the base for any table that contains a Name, DisplayName and Description column
 -->
<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require_once $root.'/CarProgress/Model/DB_Models/M_Table.php';

class LookupTable extends Table
{
    public $Name;
    Public $DispalyName;
    public $Description;
    
    public function __construct($name, $display, $desc)
    {
        $root = realpath($_SERVER["DOCUMENT_ROOT"]);
        require_once $root.'/CarProgress/Controller/Includes/CleanString.php';
        parent::__construct();
        $error = "";
        
        if(strlen($name) > 50)//name in the DB is varchar(50)
            $error = $error . "Name must be 50 characters or less.\n";
            $this->Name = CleanString($name);           
                
        if(strlen($display) > 100)//displayname in the DB is varchar(100)
            $error = $error . "Display Name must be 100 characters or less.\n";
        $this->DisplayName = CleanString($display);
                    
        if(strlen($desc) > 250)//description in the DB is varchar(250)
            $error = $error . "Description must be 250 characters or less.\n";
        $this->Description = CleanString($desc);
        
        if($error != "")//if any of the data passes fails to parse, throw an error that holds what elements failed
            throw new Exception($error);
    }
}
?>