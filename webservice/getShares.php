<?php
/*
Copyright 2018 C1-10P

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in the
Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to do so, subject to the
following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

if(isset( $_GET["user_name"]) && isset($_GET["api_key"])) {
       $user = $_GET["user_name"];
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
