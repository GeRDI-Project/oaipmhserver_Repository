#!/bin/bash

# This file is part of the GeRDI software suite
# Author Stephan Hachinger <hachinger@lrz.de>
# License: https://www.apache.org/licenses/LICENSE-2.0
# No Warranty!

echo "beginning to make OAI-PMH db from AlpEnDAC-iRODS"
date

echo "getting WPIDs of data products..."
#grep da shit (only wpid format really)
icd /dacZone/home/irods-share-group/
ils | awk '{FS="/dacZone/home/irods-share-group/"} {print $2}' | \
  grep '^[a-z0-9]\{8,8\}-[a-z0-9]\{4,4\}-[a-z0-9]\{4,4\}-[a-z0-9]\{4,4\}-[a-z0-9]\{12,12\}$' > ~/oai-pmh/wpids.txt
#get timing
date

#prepare database
echo "preparing database tables if not existent..."
echo "CREATE TABLE IF NOT EXISTS Items (id INTEGER PRIMARY KEY, IDext TEXT not null, state TEXT not null, timestamp TEXT not null ); \
      CREATE TABLE IF NOT EXISTS MetadataFormats (id INTEGER PRIMARY KEY, metadataPrefix TEXT not null, schema TEXT not null, metadataNamespace TEXT not null ); \
      CREATE TABLE IF NOT EXISTS Repositories (repositoryName TEXT PRIMARY KEY, baseURL TEXT, protocolVersion NUMERIC not null, earliestDatestamp TEXT not null, \
                                               deletedRecord TEXT not null, granularity TEXT, adminEmail TEXT not null, compression TEXT, description TEXT not null, oaiServerAddress TEXT not null, highestID INTEGER not null ); \
      CREATE TABLE IF NOT EXISTS Sets ( setName TEXT PRIMARY KEY, IDint TEXT not null ); \
      CREATE TABLE IF NOT EXISTS ResumptionTokens ( token TEXT PRIMARY KEY, validUntil TEXT not null, IDint INTEGER not null, query TEXT not null ); \
      CREATE TABLE IF NOT EXISTS Records ( id INTEGER PRIMARY KEY, id_item INTEGER not null, metadataFormat INTEGER not null DEFAULT 1, xmlBlob TEXT not null, status TEXT not null );" \
      | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db


#default contents
echo "preparing AlpEnDAC Metadata Format and Repository entries if not existent..."
echo "INSERT INTO MetadataFormats SELECT NULL,'oai_dc','http://www.openarchives.org/OAI/2.0/oai_dc.xsd','http://www.openarchives.org/OAI/2.0/oai_dc/' \
                                  WHERE NOT EXISTS (SELECT * FROM MetadataFormats WHERE metadataPrefix='oai_dc' AND schema='http://www.openarchives.org/OAI/2.0/oai_dc.xsd' \
                                  AND metadataNamespace='http://www.openarchives.org/OAI/2.0/oai_dc/');" | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db

echo "INSERT INTO Repositories SELECT 'AlpEnDAC','www.alpendac.eu',1,'1900-01-01T00:00:00Z','persistent',NULL,'info@alpendac.eu',NULL, \
                              'The Alpine Environmental Data Analysis Centre (AlpEnDAC) is a data storage and analysis platform for high-altitude research facilities within the Virtual Alpine Observatory (VAO) collaboration and beyond.', 'alpendac.oai.research.lrz.de',355 \
                               WHERE NOT EXISTS (SELECT * FROM Repositories WHERE repositoryName='AlpEnDAC');" | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db


#counters just for "progess bar"
echo "looping over data products and updating if needed"
COUNTTOT=$(cat ~/oai-pmh/wpids.txt | wc -l | awk '{print $1}')
COUNTACT=0
#end counters

for wpid in $(cat ~/oai-pmh/wpids.txt) ; do 
  COUNTACT=$(( COUNTACT + 1 ))

  echo "----"
  echo "DATA PRODUCT ${COUNTACT} / ${COUNTTOT}: cheking need to update WPID "$wpid
  echo "----"

  echo "cd-ing in to irods dir"
  icd /dacZone/home/irods-share-group/${wpid}
  
  if ! echo 'SELECT * FROM Items WHERE IDext = "'${wpid}'";' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db | grep '' ; then 
    #make item with 1900 as update timestamp
    echo "data product not found ... add metadata record"
    echo 'INSERT INTO Items(IDext,state,timestamp) VALUES ("'${wpid}'","active","1500-01-01T00:00:00Z");' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db
  fi
  
  #now check if update is necessary
  echo 'MATCH (p:Product)-[r:has]->(q:Parameter) WHERE (p.wpid="'${wpid}'") RETURN q.pwpid;' | neo4j-shell | awk '{if (NR>6 && NF==3 && $2!="q.wpid") {if (length($2)==41) {print substr($2,2,39)}}}' > ~/oai-pmh/pwpids.txt
  UPDATED=0
  for pwpid in $(cat ~/oai-pmh/pwpids.txt) ; do
    #get timestamp of metadata record
    oaidate=$(date   -d $(echo 'SELECT timestamp FROM Items WHERE IDext = "'${wpid}'";' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db) +%s)
    imeta ls -d data_${pwpid: -2:2}.json metadataLastModified | awk '{if ($1=="value:") {print $2}}' > ~/oai-pmh/temp.txt
    irodsdate=$(date -d $(cat ~/oai-pmh/temp.txt) +%s)
    echo "irodsdate / oaidate in unix seconds: ${irodsdate} / ${oaidate}"
    if (( irodsdate > oaidate )) ; then
      UPDATED=1
      break
    fi
  done

  if (( UPDATED == 1 )) ; then
    echo "oai-pmh record for wpid ${wpid} must be updated"
    #build metadata XML
    xmlblob=$(cat /home/irods-share/oai-pmh/oai-export-by-wpid.xmlprefix)$'\n'
    #fixed stuff
    xmlblob=${xmlblob}'<dc:publisher>''AlpEnDAC Project, funded by the Bavarian State Ministry of the Environment and Consumer Protection''</dc:publisher>'$'\n'
    xmlblob=${xmlblob}'<dc:type>''Dataset''</dc:type>'$'\n'
    xmlblob=${xmlblob}'<dc:source>''Data ingested to www.alpendac.eu by the original authors''</dc:source>'$'\n'
    xmlblob=${xmlblob}'<dc:format>''application/json''</dc:format>'$'\n'
    xmlblob=${xmlblob}'<dc:identifier>AlpEnDAC/'${wpid}'</dc:identifier>'$'\n'
    xmlblob=${xmlblob}'<dc:language>''en-GB''</dc:language>'$'\n'

    echo "start xmlblob after inserting fixed attrs:"
    echo "$xmlblob"
     
    #author must be taken from file owner
    ils -A data_01.json | awk '{for (i=1; i<=NF; i++) {if (index($i,"#dacZone:own")>0 && $i!="irods-share#dacZone:own") {printf "%s ",$i}}}' | awk 'BEGIN{FS="#"} {print $1}' > ~/oai-pmh/temp.txt
    metadatavalue=$(cat ~/oai-pmh/temp.txt)
    echo "set owner: "$metadatavalue
    xmlblob=${xmlblob}'<dc:author>'"${metadatavalue}"'</dc:author>'$'\n'

    #contributor can be multiple, after "value: " it goes comma-separated
    imeta ls -d data_01.json authors | awk 'BEGIN{FS=":"} {if ($1=="value") {printf "%s",$2}}' | awk 'BEGIN{RS=","} {gsub(/^[ \t]+|[ \t]+$/,"",$0); printf "%s\n",$0}' > ~/oai-pmh/temp.txt
    cp ~/oai-pmh/temp.txt ~/oai-pmh/temp.tx2

    for (( i=1; i<=$(wc -l ~/oai-pmh/temp.txt | awk '{print $1}') ; i++ )) ; do 
      metadatavalue=$(awk -v i=$i '{if (NR==i && $1!="") {print}}' temp.txt)
      if ! [ "${metadatavalue}" == "" ] ; then xmlblob=${xmlblob}'<dc:contributor>'"${metadatavalue}"'</dc:contributor>'$'\n' ; fi
    done

    #metadata from iRODS metadata NORMAL
    unset irodsattr
    declare -A irodsattr
    irodsattr["title"]="shortTitle"
    irodsattr["subject"]="title"
    irodsattr["description"]="abstract"
    irodsattr["date"]="registrationDate"
    for i in "${!irodsattr[@]}" ; do
      #echo "process: $i with metadata query: imeta ls -d data_01.json ${irodsattr[$i]} | awk '{if ($1==value:) {print $2}}' > ~/oai-pmh/temp.txt"
      imeta ls -d data_01.json ${irodsattr[$i]} | awk '{if ($1=="value:") {print $2}}' > ~/oai-pmh/temp.txt
      metadatavalue=$(cat ~/oai-pmh/temp.txt)
      xmlblob=${xmlblob}'<dc:'"${i}"'>'"${metadatavalue}"'</dc:'"${i}"$'>\n'
    done

    xmlblob=${xmlblob}$(cat /home/irods-share/oai-pmh/oai-export-by-wpid.xmlsuffix)

    echo "final xmlblob:"
    echo "$xmlblob"

    #create or update record 
    iditem=$(echo 'SELECT id FROM Items WHERE IDext = "'${wpid}'";' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db)
    echo "updating record: "$iditem
    if ! echo 'SELECT * FROM Records WHERE id_item = '${iditem}';'  | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db | grep '' ; then  
      echo "INSERTING"
      echo 'INSERT INTO Records(id_item,metadataFormat,xmlBlob,status) VALUES ('${iditem}',(SELECT id FROM MetadataFormats WHERE metadataPrefix="oai_dc"),'"'"${xmlblob}"'"',"active");' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db
    else
      echo "UPDATING"
      echo 'UPDATE Records SET xmlBlob = '"'"${xmlblob}"'"' WHERE id_item = '${iditem}';' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db
    fi
    #update modification timestamp
    echo 'UPDATE Items SET timestamp = "'$(date -u +%Y-%m-%dT%H:%M:%SZ)'" WHERE IDext = "'${wpid}'";' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db
  fi
  icd /dacZone/home/irods-share-group/
done

#fix maxID

echo 'UPDATE Repositories SET highestID = (SELECT MAX(id) FROM Items) WHERE repositoryName="AlpEnDAC";' | sqlite3 /home/irods-share/oai-pmh/db/alpendac-irods-oaipmh.db



