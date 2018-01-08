--- This file is part of the GeRDI software suite
--- Author Tobias Weber <weber@lrz.de> 
--- License: https://www.apache.org/licenses/LICENSE-2.0
--- Absolutely no warranty given!
CREATE USER 'testdb'@'localhost' IDENTIFIED BY 'testdb';
GRANT ALL PRIVILEGES ON testdb.* TO 'testdb'@'localhost';
