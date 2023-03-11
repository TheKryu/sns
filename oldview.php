<!DOCTYPE html>
<html data-theme="auto">
<head>
  <title>mSensorsData</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/pico.min.css">
  <link rel="stylesheet" href="static/custom.css">
  <link rel="icon" href="static/favicon.ico">
  <!-- <link rel="icon" href="data:,"> -->

</head> 

<body>

    <nav class="container-fluid">
      <ul>
        <li><u onclick="location='./'" style="cursor:pointer;"><kbd>SenSData</kbd></u></li>
      </ul>
       <ul> 
        <li><a href="./">newview</a></li>
        <!-- <li><a href="./newmain"> newview</a></li> -->
        <!-- <li><a href="./sendlog">send log</a></li> -->
       <!-- <li><a href="lastlog.php">last log</a></li> -->
      </ul> 
    </nav>

    <main class="container">

<?php

// ------------------
//  v1.01 01.12.2022
// ------------------

  $dbname ="/db/msensors.db";

  if ( !file_exists($dbname) )
  {
    echo '<article><header>ERROR</header>DB file not found!
        <footer><button onclick="location=\'./\'">Try again</button></footer></article></main></body></html>';
    return;
  }

  $db = new SQLite3($dbname, SQLITE3_OPEN_READONLY) or die("Error connect to db!");
  
  $result = $db->query('select s.id, s.name, s.type, s.measure, s.note, d.value, dt.date from data d
                        join sens s on s.id = d.sens_id 
                        join dates dt on dt.id=d.date_id 
                        where dt.date = (select max(date) from dates);');

  if ( $result === false )
  {
    echo '<article><header>QUERY ERROR!</header>'.$db->lastErrorMsg().
          '<footer><button onclick="location=\'./\'">Try again</button></footer></article></main></body></html>';
    $db->close();
    return;
  }

?>

    <table role="grid"> 
      <thead>
        <tr><th scope="col">id</th><th scope="col">sensor</th><th scope="col">last value</th><th scope="col">updated</th></tr>
      </thead>
      <tbody>

<?php

  while( $res = $result->fetchArray(1) )
  {
    $id = $res["id"];
    echo "<tr><th scope='row'>" . $res["id"] . "</th><td><abbr title='" . $res["type"] . ", " . $res["measure"] . "'>" . $res["name"] .
          "</abbr>&nbsp;[" . $res["note"]. "]</td>
          <td><a href='./lastgraph.php?id=" . $id . "''>" . $res["value"] . " " . $res["measure"] . "</a></td>
          <td><a href='./lastdata.php?id=" . $id . "''>" . $res["date"] . "</a></td></tr>";
  }
  
  $db->close();
  
?>
      </tbody>
    </table>

    </main>
    <footer>
      <main class="container" style="text-align: center;">
        <small>v1.05 01.11.2022 <a href="mailto:i@kruee.xyz">i@kruee.xyz</a></small>
      </main>
    </footer>
  </body>
</html>
