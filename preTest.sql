USE testdb;
INSERT  INTO metadata_format(metadata_prefix,
                             metadata_schema,
                             metadata_namespace) 
        VALUES("oai_dc", 
               "http://www.openarchives.org/OAI/2.0/oai_dc.xsd", 
               "http://www.openarchives.org/OAI/2.0/oai_dc/");

INSERT  INTO repository(name, 
                        base_url,
                        compression,
                        highest_id,
                        deleted_record,
                        admin_email,
                        protocol_version,
                        granularity,
                        description,
                        earliest_timestamp)
        VALUES("AlpEnDAC",
               "www.alpendac.eu",
               "identity",
               "oai:alpendac.oai.research.lrz.de:2",
               "persistent",
               "hachinger@lrz.de",
               "2.0",
               "YYYY-MM-DDThh:mm:ssZ",
               "this is the AlpEnDAC repository",
               "2017-11-07 12:13:14");

INSERT  INTO item(id_ext,
                   state,
                   timestamp) 
       VALUES('35c80cc8-2007-448f-a337-c6869c329506-01', 
              'active', 
              '2017-12-11T09:20');

INSERT  INTO item(id_ext,
                   state,
                   timestamp) 
       VALUES('35c80cc8-2007-448f-a337-c6869c329506-02', 
              'active', 
              '2018-01-11T09:20');

INSERT  INTO item(id_ext,
                   state,
                   timestamp) 
       VALUES('35c80cc8-2007-448f-a337-c6869c329506-03', 
              'active', 
              '2017-08-11T09:20');

INSERT  INTO item(id_ext,
                   state,
                   timestamp) 
       VALUES('35c80cc8-2007-448f-a337-c6869c329506-04', 
              'active', 
              '2017-12-11T09:20');

INSERT  INTO record(metadata_format_id,
                     item_id,
                     xml,
                     state) 
        VALUES(1,
               1,
               '<oai_dc:dc 
         xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
         xmlns:dc="http://purl.org/dc/elements/1.1/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
         http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
        <dc:title>Using Structural Metadata to Localize Experience of 
                  Digital Content</dc:title> 
        <dc:creator>Dushay, Naomi</dc:creator>
        <dc:subject>Digital Libraries</dc:subject> 
        <dc:description>With the increasing technical sophistication of 
            both information consumers and providers, there is 
            increasing demand for more meaningful experiences of digital 
            information. We present a framework that separates digital 
            object experience, or rendering, from digital object storage 
            and manipulation, so the rendering can be tailored to 
            particular communities of users.
        </dc:description> 
        <dc:description>Comment: 23 pages including 2 appendices, 
            8 figures</dc:description> 
        <dc:date>2001-12-14</dc:date>
      </oai_dc:dc>', 
               'active');

INSERT  INTO record(metadata_format_id,
                     item_id,
                     xml,
                     state) 
        VALUES(1,
               2,
               '<oai_dc:dc 
         xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
         xmlns:dc="http://purl.org/dc/elements/1.1/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
         http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
        <dc:title>Using Structural Metadata to Localize Experience of 
                  Digital Content</dc:title> 
        <dc:creator>Dushay, Naomi</dc:creator>
        <dc:subject>Digital Libraries</dc:subject> 
        <dc:description>With the increasing technical sophistication of 
            both information consumers and providers, there is 
            increasing demand for more meaningful experiences of digital 
            information. We present a framework that separates digital 
            object experience, or rendering, from digital object storage 
            and manipulation, so the rendering can be tailored to 
            particular communities of users.
        </dc:description> 
        <dc:description>Comment: 23 pages including 2 appendices, 
            8 figures</dc:description> 
        <dc:date>2001-12-14</dc:date>
      </oai_dc:dc>',
               'active');

INSERT  INTO record(metadata_format_id,
                     item_id,
                     xml,
                     state) 
        VALUES(1,
               3,
               '<oai_dc:dc 
         xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
         xmlns:dc="http://purl.org/dc/elements/1.1/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
         http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
        <dc:title>Using Structural Metadata to Localize Experience of 
                  Digital Content</dc:title> 
        <dc:creator>Dushay, Naomi</dc:creator>
        <dc:subject>Digital Libraries</dc:subject> 
        <dc:description>With the increasing technical sophistication of 
            both information consumers and providers, there is 
            increasing demand for more meaningful experiences of digital 
            information. We present a framework that separates digital 
            object experience, or rendering, from digital object storage 
            and manipulation, so the rendering can be tailored to 
            particular communities of users.
        </dc:description> 
        <dc:description>Comment: 23 pages including 2 appendices, 
            8 figures</dc:description> 
        <dc:date>2001-12-14</dc:date>
      </oai_dc:dc>',
               'active');
