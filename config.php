<?php

  define('DB_NAME', '/db/msensors.db');

  if ( !file_exists(DB_NAME) )
  {
    echo "<main class='container'><article><header>ERROR</header>DB file not found!
        <footer><button onclick='location=\'./\''>Try again</button></footer></article></main></body></html>";
    return;
  }

  $db = new SQLite3(DB_NAME, SQLITE3_OPEN_READONLY) or die("Error connect to db ".DB_NAME." !");

?>

