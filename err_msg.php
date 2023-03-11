<?php

function err_msg($msg="error")
{
  $s = "<main class='container'><article><header>ERROR</header>$msg</article>
      <button onclick='history.back();'>back</button></main></body></html>";

  echo $s;
}

?>

