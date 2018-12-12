<?php
    require_once 'api.php';
    $obj = new Api();
    $obj->create_child();

    /********************* INPUT ************************************************
    {
        "type":"page",
        "title":"new child",
        "ancestors":
            [
                {"id":3244037}
            ],
            "space":
            {
                "key":"NEOSOFT"
            },
            "body":
            {
                "storage":
                {
                    "value":"<p>This is a new child page</p>","representation":"storage"
                }
            }           
    }
    ********************************************************************************/
?>