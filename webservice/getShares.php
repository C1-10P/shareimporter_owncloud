<?php


if(isset( $_GET["user"]) && isset($_GET["api_key"])) {
       $user = $_GET["user"];
       $api_key = $_GET["api_key"];


    if ($api_key == "1234" && preg_match ( "/^[a-zA-Z0-9]+$/" , $user)) 
          {
 

       $obj = new \stdClass;
       $obj->username = $user;
       $obj->shares = array();
       $obj->shares[0] = new \stdClass;
       $obj->shares[0]->mountpoint = "testmount";
       $obj->shares[0]->host = "testhost";
       $obj->shares[0]->share = "testshare";
       $obj->shares[0]->type = "smb";
       $obj->shares[1] = new \stdClass;
       $obj->shares[1]->mountpoint = "testmountXXX" . rand();
       $obj->shares[1]->host = "testhost";
       $obj->shares[1]->share = "testshare2";
       $obj->shares[1]->type = "smb";
       $json = json_encode($obj);

       echo $json;
          }

}

?>
