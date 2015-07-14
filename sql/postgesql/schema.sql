DROP SEQUENCE IF EXISTS oceditorialstuffhistory_s;

CREATE SEQUENCE oceditorialstuffhistory_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

DROP TABLE IF EXISTS oceditorialstuffhistory;

CREATE TABLE oceditorialstuffhistory (
 id integer DEFAULT nextval('oceditorialstuffhistory_s'::text) NOT NULL,
  handler VARCHAR(50) DEFAULT NULL,
  object_id INTEGER DEFAULT 0,
  user_id INTEGER DEFAULT 0,
  created_time INTEGER DEFAULT 0,    
  type VARCHAR(50) DEFAULT NULL, 
 params_serialized TEXT
);

DROP INDEX IF EXISTS oceditorialstuffhistory_handler;
CREATE INDEX oceditorialstuffhistory_handler ON oceditorialstuffhistory USING btree (handler);

DROP INDEX IF EXISTS oceditorialstuffhistory_user_id;
CREATE INDEX oceditorialstuffhistory_user_id ON oceditorialstuffhistory USING btree (user_id);

DROP INDEX IF EXISTS oceditorialstuffhistory_object_id;
CREATE INDEX oceditorialstuffhistory_object_id ON oceditorialstuffhistory USING btree (object_id);
 
DROP SEQUENCE IF EXISTS oceditorialstuffnotificationrule_s;
CREATE SEQUENCE oceditorialstuffnotificationrule_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

DROP TABLE IF EXISTS oceditorialstuffnotificationrule;
CREATE TABLE oceditorialstuffnotificationrule (
   id integer DEFAULT nextval('oceditorialstuffnotificationrule_s'::text) NOT NULL,
  type VARCHAR(50) DEFAULT NULL,
  post_id integer DEFAULT 0 NOT NULL,
  use_digest integer DEFAULT 0,
  user_id integer DEFAULT 0 NOT NULL
);
 
DROP INDEX IF EXISTS oceditorialstuffnotificationrule_user_id;
CREATE INDEX oceditorialstuffnotificationrule_user_id ON oceditorialstuffnotificationrule USING btree (user_id);
 
ALTER TABLE ONLY oceditorialstuffnotificationrule
    ADD CONSTRAINT oceditorialstuffnotificationrule_pkey PRIMARY KEY (id);
