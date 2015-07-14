CREATE TABLE oceditorialstuffhistory (
  id serial NOT NULL,
  handler VARCHAR(50) DEFAULT NULL,
  object_id INTEGER DEFAULT 0,
  user_id INTEGER DEFAULT 0,
  created_time INTEGER DEFAULT 0,    
  type VARCHAR(50) DEFAULT NULL, 
  params_serialized TEXT,
  CONSTRAINT pk_oceditorialstuffhistory PRIMARY KEY (id)
);

CREATE INDEX oceditorialstuffhistory_handler ON oceditorialstuffhistory USING btree (handler);
CREATE INDEX oceditorialstuffhistory_user_id ON oceditorialstuffhistory USING btree (user_id);
CREATE INDEX oceditorialstuffhistory_object_id ON oceditorialstuffhistory USING btree (object_id);


CREATE TABLE oceditorialstuffnotificationrule (
  id serial NOT NULL,
  type VARCHAR(50) DEFAULT NULL,
  post_id integer DEFAULT 0 NOT NULL,
  use_digest integer DEFAULT 0,
  user_id integer DEFAULT 0 NOT NULL,
  CONSTRAINT pk_oceditorialstuffnotificationrule PRIMARY KEY (id)
);

CREATE INDEX oceditorialstuffnotificationrule_user_id ON oceditorialstuffnotificationrule USING btree (user_id);

