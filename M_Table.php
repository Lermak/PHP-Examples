<!-- 
Superclass that all other tables will be derived from in the databse
All tables should have a CreationData, CreatedBy, LastUpdated, and UpdatedBy column

When updating the database please ensure that those columns are present in all tables
 -->
<?php
class Table{
    
    public $CreationDate;
    public $CreatedBy;
    public $LastUpdated;
    public $UpdatedBy;
    
    public function __construct()
    {
        //an item made from the cunstructor is new, creation and update times are now
        $d=mktime(11, 14, 54, 8, 12, 2014);
        $this->CreationDate = date("Y-m-d h:i:sa", $d);
        $this->LastUpdated = date("Y-m-d h:i:sa", $d);
        
        //the 
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        //validate that a user is actually logged into the system.
        if(!isset($_SESSION["Token"]))
            throw new Exception("No user has logged in.");
            
        $this->CreatedBy = $_SESSION["Token"];
        $this->UpdatedBy = $_SESSION["Token"];
    }
}
?>