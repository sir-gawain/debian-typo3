-- Add Compatibility operators
--
-- SQL below solves a particular case where in the search option in the list module (and possible other modules)
-- integers types are compared against text types. While this is perfectly 'valid' in MySQL, this is not valid in PostgreSQL.
--
-- An example of such SQL generated by TYPO3 is (see "uid" LIKE '%Old%'):
-- SELECT count(*) 			
-- FROM "tx_rvtcouponfeeds_programnames" 			
-- WHERE "pid" = 100 AND ("uid" LIKE '%Old%' OR "programname" LIKE '%Old%' OR "programurl" LIKE '%Old%')
-- 
-- The functions add compatibility operators for PostgreSQL to make sure comparison is possible and the SQL doesn't return an error.
--
-- $Id: postgresql-compatibility.sql 25943 2009-10-28 13:26:30Z xperseguers $
-- R. van Twisk <typo3@rvt.dds.nl>


CREATE OR REPLACE FUNCTION t3compat_operator_like(t text, i integer) RETURNS boolean AS
$BODY$
BEGIN
	RETURN t LIKE i;
END
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 1;

CREATE OR REPLACE FUNCTION t3compat_operator_like(i integer, t text) RETURNS boolean AS
$BODY$
BEGIN
	RETURN i::text LIKE t;
END
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 1;

CREATE OR REPLACE FUNCTION t3compat_operator_eq(t text, i integer) RETURNS boolean AS
$BODY$
BEGIN
	RETURN i::text=t;
END
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 1;

CREATE OR REPLACE FUNCTION t3compat_operator_eq(i integer, t text) RETURNS boolean AS
$BODY$
BEGIN
	RETURN i::text=t;
END
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 1;

-- Operator for LIKE
CREATE OPERATOR ~~ (PROCEDURE = t3compat_operator_like, LEFTARG = integer, RIGHTARG = text);
CREATE OPERATOR ~~ (PROCEDURE = t3compat_operator_like, LEFTARG = text, RIGHTARG = integer);
-- Operator for Equality
CREATE OPERATOR = (PROCEDURE = t3compat_operator_eq, LEFTARG = integer, RIGHTARG = text);
CREATE OPERATOR = (PROCEDURE = t3compat_operator_eq, LEFTARG = text, RIGHTARG = integer);


-- Remove Compatibility operators
--
--DROP OPERATOR ~~ (integer,text);
--DROP OPERATOR ~~ (text,integer);
--DROP OPERATOR = (integer,text);
--DROP OPERATOR = (text,integer);
--DROP FUNCTION t3compat_operator_like(integer, text);
--DROP FUNCTION t3compat_operator_like(text, integer);
--DROP FUNCTION t3compat_operator_eq(integer, text);
--DROP FUNCTION t3compat_operator_eq(text, integer);
