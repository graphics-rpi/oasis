<!DOCTYPE html>
<html>
    <head>
        <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
        <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script src="../js/objFileContents.js"></script>
        <script src="../js/lib/three.js"></script>
        <script src="../js/lib/THREEx.KeyboardState.js"></script>
        <script src="../js/viewer.js"></script>
        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
        <link href="temp.css" rel="stylesheet" type="text/css">
    </head>
    <body>
       <center id="content">
           <div id="container"></div>
           <div id="modelinfo"></div>
        </center> 
        
        <?php
            $path = $_GET["path"];
            $model_type = $_GET["type"];
            echo "<script>$('#container').viewer('".$path."',true, '".$model_type."')</script>" ;
        ?>
    </body>
</html>
