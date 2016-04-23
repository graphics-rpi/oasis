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
            session_start();
            require_once('../php/config.inc.php');
            
            $path = $_GET["path"];
            $pieces = explode("/",$path);
            $pieces = explode("_",$pieces[5]);

            //0 is sim, 1 is date, 2 is time, 3 is weather
            $date = $pieces[1];
            $time = $pieces[2];
            $timezone = $pieces[3];
            $weather = $pieces[4];

            $mon    = substr($date,0,2);
            $dy     = substr($date,2,2);
            $hr    = substr($time,0,2);
            $mi    = substr($time,2,2);

            if(strlen($timezone) == 6){
              $tzs     = substr($timezone,0,1);
              $tzhr    = substr($timezone,2,2);
              $tzmi    = substr($timezone,4,2);
            }
            else if(strlen($timezone) == 5){
              $tzs     = "+";
              $tzhr    = substr($timezone,1,2);
              $tzmi    = substr($timezone,3,2);
            }
            
            $output = $output."'<b>Simulation Info</b><br>";
            $output = $output."Date: ".$mon."/".$dy."<br>";
            $output = $output."Time: ".$hr.":".$mi."<br>";
            $output = $output."Timezone: GMT ".$tzs.$tzhr.":".$tzmi."<br>";
            $output = $output."Weather: ".$weather."<br>'";
        
            
            $id = $_GET["id"];
            $command = "SELECT title FROM model_meta WHERE id=$1";
            $res = pg_query_params($command,array($id));
        
            if (pg_num_rows($res) > 0){
                $title = pg_fetch_row($res);
                echo "<script>      
                        var model_info=".$output.";
                        var path='".$_GET["path"]."';
                        var model_title='".$title[0]."';
                        var model_num='".$id."';    
                        $('#container').viewer('".$_GET["path"]."','".$title[0]."',true,model_info);
                      </script>";
            }
        
        
           
        ?>
        
        
        <script>
//            function changeview(type){
//                var temppath = path.substr(0,38);
//                temppath+=model_num;
//                if(type==1){
//                    temppath+="/slow/";
//                }                
//                else if(type==2){
//                    temppath+="/results/sim_0101_1300_-10500_CLEAR_ncv/";
//                }
//                else if(type==3){
//                    temppath+="/results/sim_0101_1300_-10500_CLEAR_fcv/";
//                }
//                else {
//                    return;
//                }
//                
//                var container = document.getElementById("container");
//                while (container.hasChildNodes()) {
//                    container.removeChild(container.lastChild);
//                }
//                
//                $('#container').viewer(temppath,model_title,true,"");
//            }
            
            var temppath = path.substr(39,44);
            //not just a model
            if(path!="slow"){
                
            }
            
            
        </script>
    </body>
</html>
