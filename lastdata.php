<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Sensor latest data</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/spectre.min.css">
  <link rel="stylesheet" href="static/spectre-icons.min.css">
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

<div class="container grid-md">

    <header class="navbar">
      <section class="navbar-section">
        <a href="./" class="navbar-brand mr-2">SensData</a>
      </section>
      <section class="navbar-section">
        <a href="#end" class="btn btn-link"><i class="icon icon-downward"></i></a>
      </section>
    </header>
    <br>

    <p class="h4">
      <?php echo $res["name"], " (", $res["note"], " ", $res["type"], ", ", $res["measure"], ")"; ?>
    </p>

    <table class="table table-striped table-hover"> 
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
  <br>
    <footer id="end"></footer>
    <div class="text-center">
      <a href="#"><i class="icon icon-2x icon-upward"></i></a>
    </div>
  </div>
  
</body>
</html>