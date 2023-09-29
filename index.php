<!DOCTYPE html>
<html>
<head>
  <title>mSensorsData</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/spectre.min.css">
  <link rel="stylesheet" href="static/spectre-icons.min.css">
  <link rel="icon" href="static/favicon.ico">
  <meta http-equiv="refresh" content="300">
  <!-- <link rel="icon" href="data:,"> -->

</head> 

<body>

  <div class="container grid-md">

    <header class="navbar">
      <section class="navbar-section">
        <a href="./" class="navbar-brand mr-2">SensData</a>
      </section>
      <section class="navbar-section">
        <a href="#" class="btn btn-link">data</a>
        <a href="#" class="btn btn-link">log</a>
      </section>
    </header>
    <br>
<?php
  require("config.php");
  require("err_msg.php");

  $result = $db->exec("create temp table if not exists temp_s 
                        (id integer, name text, type text, measure text, note text, cur_val real, 
                        prev_val real, date datetime);");
  $result = $db->exec("insert into temp_s(id, name, type, measure, note) select id, name, type, measure, note from sens where status=1;");
  $result = $db->exec("update temp_s set date = (select max(date) from data where sens_id = temp_s.id);");
  $result = $db->exec("update temp_s set cur_val = (select value from data where data.sens_id=temp_s.id and data.date=temp_s.date);");
  $result = $db->exec("update temp_s set prev_val = (select value from data where data.sens_id=temp_s.id and datetime(data.date)=datetime(temp_s.date, '-5 minutes'));");
  
  $result = $db->query("select id, name, type, measure, note, cur_val, prev_val, date from temp.temp_s order by id;");
  
  if ( $result === false )
  {
    err_msg($db->lastErrorMsg());
    $db->close();
    exit;
  }

  $col = 1;

  while( $res = $result->fetchArray(1) )
  {

    if ( $col === 1 )
    {
      echo '<div class="columns m-2">';
      echo "\r\n";
    }

    $id = $res["id"];

    echo '<div class="column">';
    //echo "\r\t";
    echo '<div class="panel">';
    echo '<div class="panel-header"><div class="panel-title">';
    echo "<b class='tooltip' data-tooltip='" . $res["date"] . "''>" . $res["name"] . 
    "</b></div></div>";
    //echo "\r\n";
    echo '<div class="panel-body">';
    echo "\t\r\n<a class='h1' href='./lastgraph.php?id=" .$id . "'>" . $res["cur_val"] . "</span></a>&nbsp;";
    echo $res["measure"] . "</div>\r\n";

    echo '<div class="panel-footer">';
    echo $res["note"] . " " . $res["type"];
    
    $cv = $res["cur_val"];
    $pv = $res["prev_val"];

    // var_dump($cv, $pv);

    echo "<a href='./lastdata.php?id=".$id."'>";
    echo '&nbsp;<i class="icon ';
    
    if ( $cv > $pv ) {
      echo 'icon-upward"></i>';
    } elseif ( $cv == $pv ) {
      echo 'icon-resize-horiz"></i>';
    } else {
      echo 'icon-downward"></i>';
    }
    echo "</a></div></div></div>\r\n";

    $col++;

    if ( $col === 4)
    {
      echo "</div>\n";
      $col = 1;
    }

  }

  $db->close();
  
?>
<!--
  </main>
    <footer>
      <main class="container" style="text-align: center;">
        <small>v1.05 01.11.2022 <a href="mailto:i@kruee.su">i@kruee.su</a></small>
      </main>
    </footer>
  -->
</div>
</div>
  </body>
</html>
