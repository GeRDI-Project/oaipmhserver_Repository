#!/bin/bash

# This file is part of the GeRDI software suite
# Author Tobias Weber <weber@lrz.de> 
# License: https://www.apache.org/licenses/LICENSE-2.0
# Absolutely no warranty given!
rm tests/test.db
mysqldump -utestdb -ptestdb testdb > dump_mysql.sql
./util/mysql2sqlite/mysql2sqlite dump_mysql.sql | sqlite3 tests/test.db
rm dump_mysql.sql
