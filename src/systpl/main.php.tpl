<?php
include_once 'lib/framework.incl.php';

class App extends booosta\usersystem\Webappadmin
{
  #protected $fields = 'name,edit,delete';
  #protected $header = 'Name,Edit,Delete';
  protected $use_subtablelink = false;
  {fkfields}
  {checkbox_fields}
  {nullfields}
  {sub_key}
  {idfield}
  {sub_idfield}
}

$app = new App('{name}');
{super-subtable}
$app->auth_user();
$app();
?>
