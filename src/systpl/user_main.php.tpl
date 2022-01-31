<?php
require_once __DIR__ . '/vendor/autoload.php';

use booosta\Framework as b;
b::load();

class App extends booosta\usersystem\Webappuser
{
  protected $tpl_default = 'tpl/user_{name}_default.tpl';
  protected $tpl_new = 'tpl/user_{name}_new.tpl';
  protected $tpl_edit = 'tpl/user_{name}_edit.tpl';

  #protected $fields = 'name,edit,delete';
  #protected $header = 'Name,Edit,Delete';
  #protected $sub_fields = 'name,edit,delete';
  #protected $use_datatable = true;
  protected $use_subtablelink = false;

  {fkfields}
  {checkbox_fields}
  {nullfields}

  #protected $auth_actions = true;
  {use_userfield}
  {subscript}
  {sub_key}
  {idfield}
  {sub_idfield}
  {sub_urlhandler}
}

$app = new App('{name}');
{super-subtable}
$app->auth_user();
$app();
