# How to quickly initialize db #



1) Export the db in the desired state, e.g., right after a clean installation. **MAKE SURE** you include the `DROP TABLE` statements.
     
2) Add this manually near the top of the SQL script (after the db is created):

```
SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `wp_vp_id`;
DROP TABLE IF EXISTS `wp_vp_references`;
DROP VIEW IF EXISTS `wp_vp_reference_details`;

```