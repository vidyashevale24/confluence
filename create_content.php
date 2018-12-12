<?php
    require_once 'api.php';
    $obj = new Api();
    $obj->create_content();

    /********************* INPUT *******************
    {
        {
            "type":"page",
            "title":"my first neosft page",
            "space":{"key":"NEOSOFT"},
            "body":{
                    "storage":{
                    "value":"<p>This is a new page</p>",
                    "representation":"storage"
                 }
             }
        }
    }
    
    ***********************************************/
