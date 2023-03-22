<?php
$rowtpls = [
  'new' => [
    'text' => '{BTEXTAREA|{fieldname}|10|texttitle::{Fieldname}}',
    'tinyint' => '{BCHECKBOX|{fieldname}|texttitle::{Fieldname}}',
    'enum' => "{BSELECT|{fieldname}|texttitle::{Fieldname}\n{options}}",
    'int' => '{BTEXT|{fieldname}|texttitle::{Fieldname}}',
    'foreignkey' => '{BFORMGRP|{Fieldname}}{%list{null}_{fieldname}}{/BFORMGRP}',
    'default' => '{BTEXT|{fieldname}|texttitle::{Fieldname}}',
  ],

  'edit' => [
    'text' => "{BTEXTAREA|{fieldname}|10|texttitle::{Fieldname}\n{*{fieldname}}}",
    'tinyint' => '{BCHECKBOX|{fieldname}|{%{fieldname}}|texttitle::{Fieldname}}',
    'enum' => "{BSELECT|{fieldname}|{%{fieldname}}|texttitle::{Fieldname}\n{options}}",
    'int' => '{BTEXT|{fieldname}|{*{fieldname}}|texttitle::{Fieldname}}',
    'foreignkey' => '{BFORMGRP|{Fieldname}}{%list{null}_{fieldname}}{/BFORMGRP}',
    'default' => '{BTEXT|{fieldname}|{*{fieldname}}|texttitle::{Fieldname}}',
  ],

  'menuitem' => "'{tablename}' => '{scriptname}',",
];
