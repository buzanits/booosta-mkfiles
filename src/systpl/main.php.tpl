<?php
require_once __DIR__ . '/vendor/autoload.php';

use booosta\Framework as b;
b::load();

class App extends booosta\usersystem\Webappadmin
{
  #protected $fields = 'name,edit,delete';
  #protected $header = 'Name,Edit,Delete';
  protected $use_subtablelink = false;
  #protected $urlhandler_action_paramlist = ['test' => 'action/id'];
{super-subtable}
  {fkfields}
  {checkbox_fields}
  {nullfields}
  {sub_key}
  {idfield}
  {sub_idfield}
  {sub_urlhandler}
}

$app = new App('{name}');
$app->auth_user();
$app();
