<?php
require_once __DIR__ . '/vendor/autoload.php';

use booosta\Framework as b;
b::load();

#class App extends booosta\usersystem\Webappadmin
class App extends booosta\webapp\Webapp
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
#$app->auth_user();
$app();
