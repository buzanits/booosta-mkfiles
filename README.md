# File creator for Booosta PHP Framework

This module provides a possibility to create the PHP scripts and necessary template files for the CRUD
functionality of PHP Booosta just by calling a script:

composer mkfiles -- address

for example creates address.php, tpl/address_default.tpl, tpl/address_new.tpl, tpl/address_edit.tpl and 
tpl/address_delete.tpl. Those files provide the basic CRUD (create, read, update, delete) functions for
the database table (in this expample _address_).

Booosta allows to develop PHP web applications quick. It is mainly designed for small web applications.
It does not provide a strict MVC distinction. Although the MVC concepts influence the framework. Templates,
data objects can be seen as the Vs and Ms of MVC.

Up to version 3 Booosta was available at Sourceforge: https://sourceforge.net/projects/booosta/ From version
4 on it resides on Github and is available from Packagist under booosta/booosta .
