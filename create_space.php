<?php
    require_once 'api.php';
    $obj = new Api();
    $obj->create_space();
   
    /********************* INPUT *******************
    { 
        "key":"NEOSOFT", 
        "name":"Neosoft",
        "type":"global", 
        "description": {
                        "plain": 
                            {
                                "value": "Neosoft Space for neo",
                                "representation":"plain"
                            }
                        }
    }
    ***********************************************/
?>