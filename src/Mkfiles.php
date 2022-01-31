<?php
namespace booosta\mkfiles;

use booosta\Framework as b;
b::load();

class Mkfiles extends \booosta\base\Base
{
  protected $prefix, $prefix_;

  public function __construct($prefix = '')
  {
    parent::__construct();
    $this->prefix = $prefix;
    if($prefix) $this->prefix_ = $prefix . '_'; else $this->prefix_ = '';
  } 

  public function set_param($param) { $this->param = $param; }

  private function raise_error($msg)
  {
    if(php_sapi_name() == 'cli'):
      print "$msg\n";
      exit;
    else:
      if(is_object($this->topobj) && is_callable([$this->topobj, 'raise_error'])):
        $this->topobj->raise_error($msg);
      else:
        print "$msg\n";
        exit;
      endif;
    endif;
  }

  public static function invoke($event, $prefix = '')
  {
    $obj = new Mkfiles($prefix);
    $obj();
  }

  public static function invoke_user($event)
  {
    self::invoke($event, 'user');
  }

  public function __invoke()
  {
    global $argv;

    if(php_sapi_name() == 'cli'):
      $param = [];
      if(strstr($argv[3], '=')):     // parameters with = like table=customer
        foreach($argv as $i=>$arg):
          if($i < 3) continue;
          list($var, $val) = explode('=', $arg);
          if(substr($var, 0, 2) == '--') $param[substr($var, 2)] = $val;
        endforeach;
      else:  
        $param['table'] = $argv[2];   // name of the table with the records
        $param['subtable'] = $argv[3];     // name of the table with sub records of the table $name (foreign key)
        if($param['subtable'] == '-') $param['subtable'] = '';
        $param['supertable'] = $argv[4];   // name of the table with super records of the table $name 
      endif;

      $param['table'] = $param['table'] ?? null;
      $param['subtable'] = $param['subtable'] ?? null;
      $param['supertable'] = $param['supertable'] ?? null;

      #print "0: " . ($argv[0] ?? '') . "\n" .  "1: " . ($argv[1] ?? '') . "\n" .  "2: " . ($argv[2] ?? '') . "\n" .  "3: " . ($argv[3] ?? '') . "\n" .  "4: " . ($argv[4] ?? '') . "\n" .  "5: " . ($argv[5] ?? '') . "\n"; 
      #print "table: {$param['table']}, sub: {$param['subtable']}, super: {$param['supertable']}, prefix: $this->prefix\n"; print_r($param);

      if($param['table'] == '') $param['table'] = readline('table name: ');
      if($param['subtable'] == '') $param['subtable'] = readline('subtable name: ');
      if($param['supertable'] == '') $param['supertable'] = readline('supertable name: ');

      if($param['table'] == ''):
        print "Usage: $argv[0] mk{$this->prefix}files tablename [sub_tablename|-] [super_tablename]\n";
        print "or: $argv[0] mk{$this->prefix}files --table=tablename [--subtable=sub_tablename] [--supertable=super_tablename]\n";
        exit;
      endif;
    else:  // run in browser
      if(!is_array($this->param)) return false;
      $param = $this->param;
    endif;

    $checkbox_fields = [];

    #print getcwd(); return;

    if(!is_dir('tpl')) mkdir('tpl', 0755);

    // Template for default
    $tpl = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix}_default.tpl.tpl");
    $tpl = str_replace('{name}', $param['table'], $tpl);
    $tpl = str_replace('{Name}', ucfirst($param['table']), $tpl);
    if($this->prefix == 'user') $tpl = str_replace('{subtable}', $param['subtable'], $tpl);
    $result = file_put_contents("tpl/{$this->prefix_}{$param['table']}_default.tpl", $tpl);
    if($result === false) $this->raise_error("Could not write tpl/{$this->prefix_}{$param['table']}_default.tpl");

    // Template for new
    $rows = '';
    $with_date = false;
    $field_user_found = false;
    $field_name_found = false;
    $first_field = '';
    $idfield = '';
    $rowtpl = file_get_contents('vendor/booosta/mkfiles/src/systpl/_new_row.tpl');

    $DATABASE = $this->config('db_database');
    #print "DB: $DATABASE\n";

    $foreignkeys = $this->makeInstance('Db_foreignkeys');

    #print_r($foreignkeys);
    #print_r($foreigncolumn);
    $fkfields = [];
    $nullfields = [];

    if(file_exists('vendor/booosta/mkfiles/src/systpl/rows.incl.php')) include 'vendor/booosta/mkfiles/src/systpl/rows.incl.php';

    $fields = $this->DB->DB_fields($DATABASE, $param['table']);
    foreach($fields as $field):
      if($field->primarykey && $field->name != 'id') $idfield = $field->name;
      if($field->primarykey || $field->name == 'ser__obj') continue;
      if($param['supertable'] && $field->name == $param['supertable']) continue;
      if($field->name == 'user') $field_user_found = true;

      $ufield = ucfirst($field->name);
      $row = str_replace('{Fieldname}', $ufield, $rowtpl);
      $row = str_replace('{fieldname}', $field->name, $row);

      if($field->null) $nullfields[] = $field->name;

      switch($field->type):
      case 'text':
        if(isset($rowtpls['new']['text'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['text']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BTEXTAREA|$field->name|10|texttitle::$ufield}", $row);
        endif;
      break;
      case 'tinyint':
        if(isset($rowtpls['new']['tinyint'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['tinyint']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BCHECKBOX|$field->name|texttitle::$ufield}", $row);
        endif;

        $checkbox_fields[] = $field->name;
      break;
      case 'enum':
        $options = str_replace("'", '', $field->param);
        $options = str_replace(',', "\n", $options);

        if(isset($rowtpls['new']['enum'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['enum']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $tmp = str_replace('{options}', $options, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BSELECT|$field->name|texttitle::$ufield\n$options}", $row);
        endif;
      break;
      case 'date':
        if(isset($rowtpls['new']['date'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['date']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BDATE|$field->name|texttitle::$ufield}", $row);
        endif;

        $with_date = true;
      break;
      case 'int':
        if($fk_table = $foreignkeys->referenced_table($param['table'], $field->name)):
          if($field->null) $null = '0'; else $null = '';   // if null is allowed use the list0_* variable in template to be able to select 'nothing'
          if(isset($rowtpls['new']['foreignkey'])):
            $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['foreignkey']);
            $tmp = str_replace('{Fieldname}', $ufield, $tmp);
            $tmp = str_replace('{null}', $null, $tmp);
            $row = str_replace('{field}', $tmp, $row);

            if($fk_table == $param['supertable']) $row = "<!-- $row -->\n";;  // do not display select for key to supertable per default
          else:
            $row = str_replace('{field}', "{BFORMGRP|$ufield|size::4}{%list{$null}_$field->name}{/BFORMGRP}", $row);
          endif;

          $fkfields[] = [$field->name, $fk_table];
        else:
          if(isset($rowtpls['new']['int'])):
            $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['int']);
            $tmp = str_replace('{Fieldname}', $ufield, $tmp);
            $row = str_replace('{field}', $tmp, $row);
          else:
            $row = str_replace('{field}', "{BTEXT|$field->name|texttitle::$ufield}", $row);
          endif;
        endif;
      break;
      default:
        if(isset($rowtpls['new']['default'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['new']['default']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BTEXT|$field->name|texttitle::$ufield}", $row);
        endif;
      break;
      endswitch;

      $rows .= $row;
    endforeach;


    $tpl = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix}_new.tpl.tpl");
    if(!$with_date) $tpl = str_replace('{DATEINIT}', '', $tpl);
    $tpl = str_replace('{name}', $param['table'], $tpl);
    $tpl = str_replace('{Name}', ucfirst($param['table']), $tpl);
    if($this->prefix == 'user') $tpl = str_replace('{subtable}', $param['subtable'], $tpl);
    $tpl = str_replace('{rows}', $rows, $tpl);

    if($param['supertable']):
      $superfield = $foreignkeys->local_column($param['table'], $param['supertable']);
      $tpl = str_replace('{fkfields}', "{HIDDEN|$superfield|{%$superfield}}", $tpl);
    else:
      $tpl = str_replace('{fkfields}', '', $tpl);
    endif;

    $result = file_put_contents("tpl/{$this->prefix_}{$param['table']}_new.tpl", $tpl);
    if($result === false) $this->raise_error("Could not write tpl/{$this->prefix_}{$param['table']}_new.tpl");


    // Template for edit
    $rows = '';
    $rowtpl = file_get_contents('vendor/booosta/mkfiles/src/systpl/_edit_row.tpl');

    foreach($fields as $field):
      if($field->primarykey || $field->name == 'ser__obj') continue;
      if($param['supertable'] && $field->name == $param['supertable']) continue;

      $ufield = ucfirst($field->name);
      $row = str_replace('{Fieldname}', $ufield, $rowtpl);
      $row = str_replace('{fieldname}', $field->name, $row);

      switch($field->type):
      case 'text':
        if(isset($rowtpls['edit']['text'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['text']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BTEXTAREA|$field->name|10|texttitle::$ufield\n{*$field->name}}", $row);
        endif;
      break;
      case 'tinyint':
        if(isset($rowtpls['edit']['tinyint'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['tinyint']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BCHECKBOX|$field->name|{*$field->name}|texttitle::$ufield}", $row);
        endif;
      break;
      case 'enum':
        $options = str_replace("'", '', $field->param);
        $options = str_replace(',', "\n", $options);

        if(isset($rowtpls['edit']['enum'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['enum']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $tmp = str_replace('{options}', $options, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{SELECT|$field->name|{*$field->name}|texttitle::$ufield\n$options}", $row);
        endif;
      break;
      case 'date':
        if(isset($rowtpls['edit']['date'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['date']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BDATE|$field->name|{*$field->name}|texttitle::$ufield}", $row);
        endif;
      break;
      case 'int':
        if($foreignkeys->referenced_table($param['table'], $field->name)):
          if($field->null) $null = '0'; else $null = '';
          if(isset($rowtpls['edit']['foreignkey'])):
            $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['foreignkey']);
            $tmp = str_replace('{Fieldname}', $ufield, $tmp);
            $tmp = str_replace('{null}', $null, $tmp);
            $row = str_replace('{field}', $tmp, $row);
          else:
            $row = str_replace('{field}', "{BFORMGRP|$ufield|size::4}{%list{$null}_$field->name}{/BFORMGRP}", $row);
          endif;
        else:
          if(isset($rowtpls['edit']['int'])):
            $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['int']);
            $tmp = str_replace('{Fieldname}', $ufield, $tmp);
            $row = str_replace('{field}', $tmp, $row);
          else:
            $row = str_replace('{field}', "{BTEXT|$field->name|{*$field->name}|texttitle::$ufield}", $row);
          endif;
        endif;
      break;
      default:
        if(isset($rowtpls['edit']['default'])):
          $tmp = str_replace('{fieldname}', $field->name, $rowtpls['edit']['default']);
          $tmp = str_replace('{Fieldname}', $ufield, $tmp);
          $row = str_replace('{field}', $tmp, $row);
        else:
          $row = str_replace('{field}', "{BTEXT|$field->name|{*$field->name}|texttitle::$ufield}", $row);
        endif;
      break;
      endswitch;

      $rows .= $row;
    endforeach;

    if($param['subtable']):
      $tpl = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix}_edit_super.tpl.tpl");
      $tpl_st = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix}_subtables.tpl.tpl");
      $superfield = $foreignkeys->local_column($param['subtable'], $param['table']);
    else:
      $tpl = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix}_edit.tpl.tpl");
    endif;

    if(!$with_date) $tpl = str_replace('{DATEINIT}', '', $tpl);

    $tpl = str_replace('{idfield}', $idfield ? $idfield : 'id', $tpl);
    $tpl = str_replace('{name}', $param['table'], $tpl);
    $tpl = str_replace('{Name}', ucfirst($param['table']), $tpl);
    $tpl = str_replace('{rows}', $rows, $tpl);
    $tpl = str_replace('{superfield}', $superfield, $tpl);
    $tpl = str_replace('{subtable}', $param['subtable'], $tpl);
    $tpl = str_replace('{subscript}', $param['subtable'] ? "{$this->prefix_}{$param['subtable']}" : '', $tpl);
    $tpl = str_replace('{Subname}', ucfirst($param['subtable']), $tpl);
    $result = file_put_contents("tpl/{$this->prefix_}{$param['table']}_edit.tpl", $tpl);
    if($result === false) $this->raise_error("Could not write tpl/{$this->prefix_}{$param['table']}_edit.tpl");

    $tpl = str_replace('{idfield}', $idfield ? $idfield : 'id', $tpl_st);
    $tpl = str_replace('{name}', $param['table'], $tpl);
    $tpl = str_replace('{Name}', ucfirst($param['table']), $tpl);
    $tpl = str_replace('{rows}', $rows, $tpl);
    $tpl = str_replace('{superfield}', $superfield, $tpl);
    $tpl = str_replace('{subtable}', $param['subtable'], $tpl);
    $tpl = str_replace('{subscript}', $param['subtable'] ? "{$this->prefix_}{$param['subtable']}" : '', $tpl);
    $tpl = str_replace('{Subname}', ucfirst($param['subtable']), $tpl);
    $result = file_put_contents("tpl/{$this->prefix_}{$param['table']}_subtables.tpl", $tpl);
    if($result === false) $this->raise_error("Could not write tpl/{$this->prefix_}{$param['table']}_subtables.tpl");

    $checkboxes = implode(',', $checkbox_fields);

    $tpl = file_get_contents("vendor/booosta/mkfiles/src/systpl/{$this->prefix_}main.php.tpl");
    $tpl = str_replace('{name}', $param['table'], $tpl);
    $tpl = str_replace('{Name}', ucfirst($param['table']), $tpl);

    if($idfield) $tpl = str_replace('{idfield}', "protected \$idfield = '$idfield';", $tpl);
    else $tpl = str_replace('{idfield}', '', $tpl);

    if($this->prefix == 'user') $tpl = str_replace('{subtable}', $param['subtable'], $tpl);

    if($checkboxes) $tpl = str_replace('{checkbox_fields}', "protected \$checkbox_fields = '$checkboxes';", $tpl);
    else $tpl = str_replace('{checkbox_fields}', "#protected \$checkbox_fields = '';", $tpl);

    $ssname = '';
    if($param['subtable']):
      $ssname .= "\$app->set_subname('{$param['subtable']}');\n";
      $tpl = str_replace('{subscript}', "protected \$subscript = 'user_{$param['subtable']}';", $tpl);
    else:
      $tpl = str_replace('{subscript}', '', $tpl);
    endif;

    if($param['supertable']):
      $ssname .= "\$app->set_supername('{$param['supertable']}');\n";
      $urlhandler = "protected \$urlhandler_action_paramlist = ['new' => 'action/{$param['supertable']}'];";
    endif;

    if($this->prefix == 'user' && $param['supertable']) $ssname .= "\$app->set_superscript('user_{$param['supertable']}');\n";

    $tpl = str_replace('{super-subtable}', $ssname, $tpl);
    $tpl = str_replace('{sub_urlhandler}', $urlhandler, $tpl);

    $code = '';
    foreach($fkfields as $fkfield):
      if(is_array($fkfield)):
        $foreignfields = $this->DB->DB_fields($DATABASE, $fkfield[1]);
        foreach($foreignfields as $foreignfield):
          if($foreignfield->primarykey):
            $foreign_idfield = $foreignfield->name;
            continue;
          endif;

          if($foreignfield->name == 'name') $field_name_found = true;
          if($first_foreignfield == '') $first_foreignfield = $foreignfield->name;
        endforeach;

        if(!$field_name_found) $showfield = ", 'showfield' => '$first_foreignfield'"; else $showfield = '';
        if($foreign_idfield != 'id') $fkidfield = ", 'idfield' => '$foreign_idfield'";
        $code .= "'{$fkfield[0]}' => ['table' => '{$fkfield[1]}'$showfield$fkidfield],";
      else:
        $code .= "'$fkfield',";
      endif;
    endforeach;

    $foreign_keyfield = $foreignkeys->local_column($param['subtable'], $param['table']);
    if($param['subtable'] && $foreign_keyfield != $param['table'] && $foreign_keyfield != '')  // referencing column has different name than the referenced table name
      $tpl = str_replace('{sub_key}', "protected \$sub_key = '$foreign_keyfield';", $tpl);
    else
      $tpl = str_replace('{sub_key}', '', $tpl);

    #\booosta\debug("{$param['table']}, {$param['subtable']}");
    #\booosta\debug($foreignkeys->local_column($param['table'], $param['subtable']));
    #\booosta\debug($foreign_idfield);

    if($param['subtable']):
      $subtablefields =  $this->DB->DB_fields($DATABASE, $param['subtable']);
      foreach($subtablefields as $subtablefield)
        if($subtablefield->primarykey)
          $foreign_idfield = $subtablefield->name;

      if($foreign_idfield != 'id' && $foreign_idfield != '') 
        $tpl = str_replace('{sub_idfield}', "protected \$sub_idfield = '$foreign_idfield';", $tpl);
      else $tpl = str_replace('{sub_idfield}', '', $tpl);
    else:
      $tpl = str_replace('{sub_idfield}', '', $tpl);
      $tpl = str_replace('{sub_urlhandler}', '', $tpl);
    endif;

    if(sizeof($fkfields)) $tpl = str_replace('{fkfields}', "protected \$foreign_keys = [$code];", $tpl);
    else $tpl = str_replace('{fkfields}', '', $tpl);

    if(sizeof($nullfields)) $tpl = str_replace('{nullfields}', "protected \$null_fields = ['" . implode("','", $nullfields) . "'];", $tpl);
    else $tpl = str_replace('{nullfields}', '', $tpl);

    if($field_user_found) $tpl = str_replace('{use_userfield}', 'protected $use_userfield = true;', $tpl);
    else $tpl = str_replace('{use_userfield}', '', $tpl);

    $result = file_put_contents("{$this->prefix_}{$param['table']}.php", $tpl);
    if($result === false) $this->raise_error("Could not write {$this->prefix_}{$param['table']}.php");

    // add to menu if it has no supertable
    if($param['supertable'] == ''):
      if($this->prefix == 'user') $file = 'incl/menudefinitionfile_user.php'; else $file = 'incl/menudefinitionfile_admin.php';
      $code = file_get_contents($file);
      $tablename = ucfirst($param['table']);

      if(isset($rowtpls['menuitem'])):
        $linkcode = $rowtpls['menuitem'];
        $linkcode = str_replace('{tablename}', $tablename, $linkcode);
        $linkcode = str_replace('{scriptname}', "{$this->prefix_}{$param['table']}", $linkcode);
        $code = str_replace('###menuitems###', "$linkcode\n            ###menuitems###", $code);
      else:
        $code = str_replace('###menuitems###', "<li>{LINK|$tablename|{%base_dir}{$this->prefix_}{$param['table']}}</li>\n            ###menuitems###", $code);
      endif;

      $result = file_put_contents($file, $code);
      if($result === false) $this->raise_error("Could not write $file");
    endif;

    // add some privileges if they do not yet exist
    foreach(['view', 'create', 'edit', 'delete'] as $priv)
      if($this->DB->query_value("select count(*) from privilege where name='$priv {$param['table']}'") == 0)
        $this->DB->query("insert into privilege (name) values ('$priv {$param['table']}')");
  }
}
