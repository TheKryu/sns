<?php

function err_msg($msg="error")
{
  $s = "<div class='toast toast-error'>
  <button class='btn btn-clear float-right'></button>
  $msg </div>";

  echo $s;
}

?>

