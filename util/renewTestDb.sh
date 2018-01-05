#!/bin/bash
rm tests/test.db
mysqldump -utestdb -ptestdb testdb > dump_mysql.sql
./util/mysql2sqlite dump_mysql.sql | sqlite3 tests/test.db
rm dump_mysql.sql
