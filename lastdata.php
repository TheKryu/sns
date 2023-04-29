<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Sensor latest data</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/pico.min.css">
  <link rel="stylesheet" href="static/custom.css">
  <link rel="icon" href="static/favicon.ico">
  <meta http-equiv="refresh" content="300">
</head> 
<body>
  
<?php

  require('err_msg.php');
  require('config.php');

  $sid = htmlspecialchars($_GET["id"]);
  if ( empty($sid) )
  {
    err_msg('Wrong id!');
    exit;
  }

  $today = date("Y-m-d");

  $result = $db->query("select s.name, s.type, s.measure, s.note from sens s where s.id=" . $sid);

  $res = $result->fetchArray();

  if ( empty($res["name"]) )
  {
    err_msg('No data found!');
    $db->close();
    exit;
  }

?>

    <nav class="container-fluid">
      <ul>
        <!-- <li onclick="location='./'" style="cursor:pointer;"><kbd>SenSData</kbd></li> -->
        <li><kbd><?php echo $res["name"], " (", $res["note"], " ", $res["type"], ", ", $res["measure"], ")"; ?></kbd></li>
      </ul>
      <ul>
        <!--        <li><button style="padding: 10px;" onclick="window.location='./'"><?php echo $res["name"], " (", $res["type"], ", ", $res["measure"], ")"; ?></button></li>
        --> 
        <!-- <li><a href='./'><?php echo $res["name"], " (", $res["type"], ", ", $res["measure"], ")"; ?></a></li> -->
        <li><button onclick="history.back();">back</button></li>
      </ul>
    </nav>

    <main class="container">

    <a href="#footer">bottom</a><br>

    <figure>

    <table role="grid"> 
      <thead>
        <tr><th>no</th><th>date-time</th><th>value</th></tr>
      </thead>
      <tbody>

<?php

  $result = $db->query('select d.date, s.name, s.type, d.value, s.measure from data d 
                        left join sens s on s.id = d.sens_id 
                        where d.sens_id=' . $sid . ' and 
                        date(d.date)="' . $today . '" order by d.date desc;');

  $i = 1;

  while( $res = $result->fetchArray())
  {
    echo "\t<tr>";
    echo "<td>" . $i . "</td><td>" . $res["date"] . "</td><td>". $res["value"] . ", " . $res["measure"] . "</td>";
    echo "</tr>\n";

    $i++;
  }

  $db->close();

  if ( $i === 1 )
  {
    err_msg("No data found!");
    exit;
  }

?>
      </tbody>
    </table>
    </figure>
    <footer id="footer"></footer>
    <a href="#">top</a>
  </main>
  
</body>
</html>