<?php
    require_once 'api.php';
    $obj = new Api();
    $obj->create_page_with_task();

    /********************* INPUT *******************
    {
      "type":"page",
      "title":"Page with task.",
      "body":
            { 
                "storage":
                    {
                        "value":"<ac:task-list><ac:task><ac:task-status>incomplete</ac:task-status><ac:task-body><ac:link><ri:user ri:username='admin' /></ac:link>do something</ac:task-body></ac:task></ac:task-list>",
                        "representation":"storage"
                    }
            },
       "space":{"key":"NEOSOFT"}
    }
    ***********************************************/
?>