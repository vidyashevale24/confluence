<?php
    require_once 'api.php';
    $obj = new Api();
    $obj->update_page();

    /********************* INPUT ************************************************
    {
        "id":"3244037",
        "type":"page",
        "title":"my first neosft page",
        "space":
            {
            "key":"NEOSOFT"
            },
            "body":
            {
                "storage":
                    {
                        "value":"<p>This is the vid content for the new page</p>",
                        "representation":"storage"
                    }
            },
        "version":{"number":3}
    }
    ********************************************************************************/
?>