use viihuset_se;
-- vih2_banner
ALTER TABLE vih2_banner DROP FOREIGN KEY `vih2_banner_ibfk_1`;
ALTER TABLE vih2_banner
   ADD CONSTRAINT `vih2_banner_fk_brf`
   FOREIGN KEY (`brf`)
   REFERENCES `vih2_brf` (`name`)
   ON DELETE CASCADE;
-- vih2_module
ALTER TABLE vih2_module DROP FOREIGN KEY `vih2_module_ibfk_1`;
ALTER TABLE vih2_module
   ADD CONSTRAINT `vih2_module_fk_brf`
   FOREIGN KEY (`brf`)
   REFERENCES `vih2_brf` (`name`)
   ON DELETE CASCADE;
-- vih2_visitor
ALTER TABLE vih2_visitor DROP FOREIGN KEY `vih2_visitor_ibfk_1`;
ALTER TABLE vih2_visitor
   ADD CONSTRAINT `vih2_visitor_fk_brf`
   FOREIGN KEY (`brf`)
   REFERENCES `vih2_brf` (`name`)
   ON DELETE CASCADE;
-- vih2_user
-- first need to insert sysadmin brf for fk to work
INSERT INTO vih2_brf(name, activated, validity_period) VALUES ('sysadmin', true, NULL);
ALTER TABLE vih2_user
   ADD CONSTRAINT `vih2_user_fk_brf`
   FOREIGN KEY (`brf`)
   REFERENCES `vih2_brf` (`name`)
   ON DELETE CASCADE;
