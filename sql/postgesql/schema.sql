CREATE TABLE oceditorialstuffhistory (
  handler VARCHAR(50) DEFAULT NULL,  
  object_id INTEGER DEFAULT 0,
  user_id INTEGER DEFAULT 0,
  created_time INTEGER DEFAULT 0,    
  type VARCHAR(50) DEFAULT NULL, 
  params_serialized TEXT
);

CREATE INDEX oceditorialstuffhistory_handler ON oceditorialstuffhistory USING btree (handler);
CREATE INDEX oceditorialstuffhistory_user_id ON oceditorialstuffhistory USING btree (user_id);
CREATE INDEX oceditorialstuffhistory_object_id ON oceditorialstuffhistory USING btree (object_id);