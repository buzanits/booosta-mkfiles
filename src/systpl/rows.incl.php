<?php
$rowtpls = [
  'new' => [
    'text' => '{BTEXTAREA|{fieldname}|40|10|textareatitle::{Fieldname}}',
    'tinyint' => '{BCHECKBOX|{fieldname}|texttitle::{Fieldname}}',
    'enum' => "{BFORMGRP|{Fieldname}|size::4}{SELECT|{fieldname}\n{options}}{/BFORMGRP}",
    'int' => '{BTEXT|{fieldname}|texttitle::{Fieldname}}',
    'foreignkey' => '{BFORMGRP|{Fieldname}|size::4}{%list{null}_{fieldname}}{/BFORMGRP}',
    'default' => '{BTEXT|{fieldname}|texttitle::{Fieldname}}',
  ],

  'edit' => [
    'text' => "{BTEXTAREA|{fieldname}|40|10|textareatitle::{Fieldname}\n{*{fieldname}}}",
    'tinyint' => '{BCHECKBOX|{fieldname}|{%{fieldname}}|texttitle::{Fieldname}}',
    'enum' => "{BFORMGRP|{Fieldname}|size::4}{SELECT|{fieldname}|{%{fieldname}}\n{options}}{/BFORMGRP}",
    'int' => '{BTEXT|{fieldname}|{*{fieldname}}|texttitle::{Fieldname}}',
    'foreignkey' => '{BFORMGRP|{Fieldname}|size::4}{%list{null}_{fieldname}}{/BFORMGRP}',
    'default' => '{BTEXT|{fieldname}|{*{fieldname}}|texttitle::{Fieldname}}',
  ],

  'menuitem' => "'{tablename}' => '{%base_dir}{scriptname}',",
  #'menuitem' => '{LINK|{tablename}|{%base_dir}{scriptname}}<br>',
];
?>
