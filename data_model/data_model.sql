--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.24
-- Dumped by pg_dump version 14.0

-- Started on 2021-11-08 16:17:03

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 484979)
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- TOC entry 4423 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- TOC entry 1508 (class 1255 OID 487322)
-- Name: fct_rmca_disable_foreign_keys(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_rmca_disable_foreign_keys() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
	returned  BOOLEAN;
	list_tables RECORD;
BEGIN
	FOR list_tables IN select * from pg_tables where schemaname='public'
	LOOP
		RAISE NOTICE 'table %', list_tables.tablename;

		EXECUTE 'ALTER TABLE  "'||list_tables.tablename||'"  DISABLE TRIGGER ALL ;';
	END LOOP;
	
	returned := TRUE;

	RETURN returned; 
END;
  $$;


ALTER FUNCTION public.fct_rmca_disable_foreign_keys() OWNER TO postgres;

--
-- TOC entry 1509 (class 1255 OID 487323)
-- Name: fct_rmca_enable_foreign_keys(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_rmca_enable_foreign_keys() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
	returned  BOOLEAN;
	list_tables RECORD;
BEGIN
	FOR list_tables IN select * from pg_tables where schemaname='public'
	LOOP
		RAISE NOTICE 'table %', list_tables.tablename;

		EXECUTE 'ALTER TABLE  "'||list_tables.tablename||'"  ENABLE TRIGGER ALL ;';
	END LOOP;
	
	returned := TRUE;

	RETURN returned; 
END;
  $$;


ALTER FUNCTION public.fct_rmca_enable_foreign_keys() OWNER TO postgres;

--
-- TOC entry 1510 (class 1255 OID 487324)
-- Name: fct_rmca_truncate_tables(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_rmca_truncate_tables() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
	returned  BOOLEAN;
	list_tables RECORD;
BEGIN
	FOR list_tables IN select * from pg_tables where schemaname='public'
	LOOP
		RAISE NOTICE 'table %', list_tables.tablename;

		EXECUTE 'TRUNCATE "'||list_tables.tablename||'" RESTART IDENTITY CASCADE ;';
	END LOOP;
	
	returned := TRUE;

	RETURN returned; 
END;
  $$;


ALTER FUNCTION public.fct_rmca_truncate_tables() OWNER TO postgres;

--
-- TOC entry 1520 (class 1255 OID 522260)
-- Name: fct_trg_rmca_control_keyword(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_trg_rmca_control_keyword() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	cpt_existing INT;
	path_existing VARCHAR; 
BEGIN

    IF TG_OP='INSERT' THEN
		SELECT COUNT(*) INTO cpt_existing FROM lkeywords WHERE LOWER(wordson) = LOWER(NEW.wordson);
		IF cpt_existing > 0 THEN
			SELECT path_word INTO path_existing FROM public.rmca_get_keywords_hierarchy()
					WHERE LOWER(word) =LOWER(NEW.wordson);
			RAISE EXCEPTION 'This keyword already exists, path is %', path_existing;
		END IF;
	END IF;	
	IF TG_OP='UPDATE' THEN
			    RAISE NOTICE 'update test';
				IF NEW.wordfather = OLD.wordfather THEN
					SELECT COUNT(*) INTO cpt_existing FROM lkeywords WHERE LOWER(wordson) = LOWER(NEW.wordson);
					IF cpt_existing > 0 THEN
						SELECT path_word INTO path_existing FROM public.rmca_get_keywords_hierarchy()
								WHERE LOWER(word) =LOWER(NEW.wordson);
						RAISE EXCEPTION 'This keyword already exists, path is %', path_existing;
					END IF;
				END IF;
				SELECT count(*) INTO cpt_existing
		 			from public.rmca_get_keywords_hierarchy()
					WHERE path_word ~* ('/'||NEW.wordson||'/') --old.wordson
					and word=NEW.wordfather
					;
			    RAISE NOTICE 'cpt loop % % ', cpt_existing, NEW.wordson;		
				IF cpt_existing > 0 AND NEW.wordson != NEW.wordfather THEN
					RAISE EXCEPTION 'Circular relation in keyword herarchy';
				END IF;
				--
			END IF;
	IF TG_OP='INSERT' THEN
		/*IF LOWER(NEW.wordson) = LOWER(NEW.wordfather) THEN
			RAISE EXCEPTION 'Circular relation in keyword herarchy';
		END IF;*/
		--raise notice 'test circ';
		SELECT COUNT(*) INTO cpt_existing FROM  
		(SELECT regexp_split_to_table(path_word,'/') as parent
		 from public.rmca_get_keywords_hierarchy()
			WHERE LOWER(word) = LOWER(NEW.wordfather) ) a
			WHERE LOWER(parent)=LOWER(NEW.wordson);
			IF cpt_existing > 0 AND NEW.wordson != NEW.wordfather THEN
				RAISE EXCEPTION 'Circular relation in keyword herarchy';
			ELSE
			
		END IF;
	END IF;
	
	IF TG_OP='DELETE' THEN
		SELECT count(*) INTO cpt_existing FROM dkeyword WHERE LOWER(keyword)=LOWER(OLD.wordson)
		and LOWER(old.wordfather)!=LOWER(OLD.wordson) 
		;
		IF cpt_existing > 0 THEN
				RAISE EXCEPTION 'Delete operation refused, this keyword is used in documents';
		END IF;
		SELECT count(*) INTO cpt_existing FROM lkeywords WHERE LOWER(wordfather)=LOWER(OLD.wordson)
		and LOWER(old.wordfather)!=LOWER(OLD.wordson) 
		;
		IF cpt_existing > 0 THEN
				RAISE EXCEPTION 'Delete operation refused, this keyword is used in hierarchies';
		END IF;
		RETURN OLD;
	END IF;
	
RETURN NEW;
END
$$;


ALTER FUNCTION public.fct_trg_rmca_control_keyword() OWNER TO postgres;

--
-- TOC entry 1518 (class 1255 OID 535932)
-- Name: fct_trg_rmca_control_upd_keyword(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_trg_rmca_control_upd_keyword() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE 
BEGIN

	IF TG_OP='UPDATE' THEN
		UPDATE lkeywords SET wordfather=NEW.wordson WHERE wordfather=OLD.wordson ;
		UPDATE dkeyword set keyword=NEW.wordson WHERE keyword=OLD.wordson ;
	END IF;
RETURN NEW;
END
$$;


ALTER FUNCTION public.fct_trg_rmca_control_upd_keyword() OWNER TO postgres;

--
-- TOC entry 1519 (class 1255 OID 593094)
-- Name: fct_trg_rmca_coordinates(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fct_trg_rmca_coordinates() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE 
	convert_coord boolean;
	epsg integer;
BEGIN
	
	IF TG_OP='UPDATE' Or TG_OP='INSERT' THEN
		convert_coord:=FALSE;
		IF NEW.wkt IS NOT NULL THEN
			IF LENGTH(NEW.wkt)>0 THEN
				convert_coord:=TRUE;
			END IF;
		END IF;
		IF convert_coord THEN
			epsg:=COALESCE(NEW.epsg::int,4326);
		
			NEW.geom=ST_SETSRID(ST_GEOMFROMTEXT(NEW.WKT),epsg);
		END IF;
		
	END IF;
RETURN NEW;
END
$$;


ALTER FUNCTION public.fct_trg_rmca_coordinates() OWNER TO postgres;

--
-- TOC entry 1515 (class 1255 OID 514529)
-- Name: fct_trk_log_table(); Type: FUNCTION; Schema: public; Owner: darwin2
--

CREATE FUNCTION public.fct_trk_log_table() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  user_id integer;
  track_level integer;
  track_fields integer;
  trk_id bigint;
  tbl_row RECORD;
  new_val varchar;
  old_val varchar;
BEGIN
  BEGIN
  SELECT coalesce(current_setting('session.track_level'),'10')::integer INTO track_level;
  EXCEPTION
	  WHEN OTHERS THEN 
	BEGIN 
		SET session.track_level TO 10;
	END;
   END;
  IF track_level = 0 THEN --NO Tracking
    RETURN NEW;
  ELSIF track_level = 1 THEN -- Track Only Main tables
    IF TG_TABLE_NAME::text NOT IN ('specimens', 'taxonomy', 'chronostratigraphy', 'lithostratigraphy',
      'mineralogy', 'lithology', 'people') THEN
      RETURN NEW;
    END IF;
  END IF;
 BEGIN
  SELECT coalesce(current_setting('session.geo_darwin_user'),'1')::integer INTO user_id;
  --EXCEPTION
	--  WHEN OTHERS THEN 
	--BEGIN 
--		SET session.geo_darwin_user TO 1;
	--END;
  END ;
  IF user_id = 0 OR  user_id = -1 THEN
    RETURN NEW;
  END IF;

  IF TG_OP = 'INSERT' THEN
    INSERT INTO t_data_log (referenced_table, record_id, user_ref, action, modification_date_time, new_value)
        VALUES (TG_TABLE_NAME::text, NEW.pk, user_id, 'insert', now(), row_to_json(NEW)) RETURNING pk into trk_id;
 	DELETE FROM tv_materialized_view_stamp;
	insert into tv_materialized_view_stamp (to_refresh, last_refresh) VALUES(true, NOW());
 ELSEIF TG_OP = 'UPDATE' THEN

    IF ROW(NEW.*) IS DISTINCT FROM ROW(OLD.*) THEN
    INSERT INTO t_data_log (referenced_table, record_id, user_ref, action, modification_date_time, new_value, old_value)
        VALUES (TG_TABLE_NAME::text, NEW.pk, user_id, 'update', now(), row_to_json(NEW), row_to_json(OLD)) RETURNING pk into trk_id;
    	DELETE FROM tv_materialized_view_stamp;
		insert into tv_materialized_view_stamp (to_refresh, last_refresh) VALUES(true, NOW());
	ELSE
      RAISE info 'unnecessary update on table "%" and id "%"', TG_TABLE_NAME::text, NEW.pk;
    END IF;

  ELSEIF TG_OP = 'DELETE' THEN
  
    INSERT INTO t_data_log (referenced_table, record_id, user_ref, action, modification_date_time, old_value)
      VALUES (TG_TABLE_NAME::text, OLD.pk, user_id, 'delete', now(), row_to_json(OLD));
	DELETE FROM tv_materialized_view_stamp;
	insert into tv_materialized_view_stamp (to_refresh, last_refresh) VALUES(true, NOW());
  END IF;

  RETURN NULL;
END;
$$;


ALTER FUNCTION public.fct_trk_log_table() OWNER TO darwin2;

--
-- TOC entry 1511 (class 1255 OID 509331)
-- Name: pk_collid(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pk_collid() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
     BEGIN
         NEW.pk := NEW.idcollection||NEW.idsample;
         RETURN NEW;
     END;
 $$;


ALTER FUNCTION public.pk_collid() OWNER TO postgres;

--
-- TOC entry 1512 (class 1255 OID 511773)
-- Name: rmca_align_seq(); Type: FUNCTION; Schema: public; Owner: darwin2
--

CREATE FUNCTION public.rmca_align_seq() RETURNS integer
    LANGUAGE plpgsql
    AS $$
    Declare returned int;
    rec_seq record;
    count_tmp bigint;
    max_tmp bigint;
   cur_seq  CURSOR FOR SELECT REPLACE(REPLACE(column_default,'nextval(''',''),'''::regclass)','') as seq_name, table_name, column_name from information_schema.columns where column_default like 'nextval%' and table_schema='public' order by table_name;

  metadata_tmp integer;
    BEGIN
       set search_path='public';
    returned:=-1;
  
      OPEN cur_seq;

   LOOP
    -- fetch row into the film
      FETCH cur_seq INTO rec_seq;
    -- exit when no more row to fetch
      EXIT WHEN NOT FOUND;
                  EXECUTE 'SELECT COUNT(*) FROM public.'||rec_seq.table_name||';' INTO count_tmp;
                  EXECUTE '(SELECT MAX('||rec_seq.column_name||')+1 FROM public.'||rec_seq.table_name||');' INTO  max_tmp;
                RAISE notice E'seq: % \t table:% \ t count (row): % \t column bound to sequence % \t max key value % ', rec_seq.seq_name,rec_seq.table_name, count_tmp, rec_seq.column_name,  max_tmp ;
		EXECUTE 'SELECT setval(''public.'||rec_seq.seq_name||''', (SELECT MAX('||rec_seq.column_name||')+1 FROM public.'||rec_seq.table_name||') , false);';

   END LOOP;
                returned:=0;
      return returned;

      
    END;
   $$;


ALTER FUNCTION public.rmca_align_seq() OWNER TO darwin2;

--
-- TOC entry 1513 (class 1255 OID 511775)
-- Name: rmca_check_seq(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.rmca_check_seq() RETURNS integer
    LANGUAGE plpgsql
    AS $$
    Declare returned int;
    rec_seq record;
    count_tmp bigint;
    max_tmp bigint;
   cur_seq  CURSOR FOR SELECT REPLACE(REPLACE(column_default,'nextval(''',''),'''::regclass)','') as seq_name, table_name, column_name from information_schema.columns where column_default like 'nextval%' and table_schema='public' order by table_name;

  metadata_tmp integer;
    BEGIN
       set search_path='public';
    returned:=-1;
  
      OPEN cur_seq;

   LOOP
    -- fetch row into the film
      FETCH cur_seq INTO rec_seq;
    -- exit when no more row to fetch
      EXIT WHEN NOT FOUND;
                  EXECUTE 'SELECT COUNT(*) FROM public.'||rec_seq.table_name||';' INTO count_tmp;
                  EXECUTE '(SELECT MAX('||rec_seq.column_name||')+1 FROM public.'||rec_seq.table_name||');' INTO  max_tmp;
                RAISE notice E'seq: % \t table:% \ t count (row): % \t column bound to sequence % \t max key value % ', rec_seq.seq_name,rec_seq.table_name, count_tmp, rec_seq.column_name,  max_tmp ;
		--EXECUTE 'SELECT setval(''public.'||rec_seq.seq_name||''', (SELECT MAX('||rec_seq.column_name||')+1 FROM public.'||rec_seq.table_name||') , false);';

   END LOOP;
                returned:=0;
      return returned;

      
    END;
   $$;


ALTER FUNCTION public.rmca_check_seq() OWNER TO postgres;

--
-- TOC entry 1514 (class 1255 OID 512404)
-- Name: rmca_get_keywords_hierarchy(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.rmca_get_keywords_hierarchy() RETURNS TABLE(pk integer, word character varying, wordfather character varying, parent_pk integer, path_pk character varying, path_word character varying, level integer)
    LANGUAGE plpgsql
    AS $$
DECLARE

DECLARE
row_keyword RECORD;
flag_go boolean;
cpt_ori integer;
cpt1 integer;
cpt2 integer;
tmp_level integer;
	
BEGIN 
		tmp_level=1;
		SELECT COUNT(*) INTO cpt_ori FROM lkeywords;
		DROP TABLE IF EXISTS tmp_keyword;
		CREATE TEMP TABLE IF NOT EXISTS  tmp_keyword  
		( pk integer, word varchar, wordfather varchar, parent_pk integer, path_pk varchar, path_word varchar, level integer) ON COMMIT DROP;
		INSERT INTO tmp_keyword (pk, word,path_pk, path_word, level)  (WITH a as (
				select * from lkeywords
				)
				SELECT (ROW_NUMBER() OVER ()) + cpt_ori, sub.wordfather,'/'||((ROW_NUMBER() OVER ()) + cpt_ori)::varchar||'/', 
																	   '/'||sub.wordfather||'/', tmp_level  from (SELECT distinct		
				b.wordfather from lkeywords b LEFT JOIN a on
				b.wordfather=a.wordson where a.pk is null) sub											   
											   );
		INSERT INTO tmp_keyword (pk, word,path_pk, path_word, level) 
			SELECT distinct lkeywords.pk, lkeywords.wordson, '/'||lkeywords.pk::varchar||'/', '/'||lkeywords.wordson||'/', tmp_level FROM  lkeywords WHERE lkeywords.wordson=lkeywords.wordfather;
		flag_go:=true;
		while flag_go
		LOOP
		    
			SELECT COUNT(distinct tmp_keyword.word) INTO cpt1 FROM tmp_keyword;
			
			WITH b AS (SELECT * FROM tmp_keyword WHERE tmp_keyword.level=tmp_level )
				INSERT INTO tmp_keyword (pk ,word, wordfather , parent_pk, path_pk,  path_word,  level)
					SELECT distinct lkeywords.pk,lkeywords.wordson, lkeywords.wordfather,
					lkeywords.pk,
					b.path_pk||lkeywords.pk::varchar||'/',
					b.path_word||lkeywords.wordson||'/',
					tmp_level+1  FROM lkeywords INNER JOIN b
					ON	lkeywords.wordfather=b.word WHERE lkeywords.wordson!=lkeywords.wordfather ;
					
			tmp_level:=tmp_level+1;
			SELECT COUNT(distinct tmp_keyword.word) INTO cpt2 FROM tmp_keyword;
			IF cpt1=cpt2  THEN
				flag_go:=FALSE;
			END IF;
		END LOOP;
		
	
	RETURN QUERY SELECT * FROM tmp_keyword;
END;
$$;


ALTER FUNCTION public.rmca_get_keywords_hierarchy() OWNER TO postgres;

--
-- TOC entry 1516 (class 1255 OID 514399)
-- Name: rmca_refresh_materialized_view(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.rmca_refresh_materialized_view() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
returned boolean;
begin 
returned:= true;
	refresh materialized view mv_all_contributions_to_object_agg_merge;
	refresh materialized view mv_rmca_main_objects_description;
	refresh materialized view mv_rmca_merge_all_objects_vertical_expand;
	
	delete  from tv_keyword_hierarchy_to_object;
	INSERT INTO tv_keyword_hierarchy_to_object ( main_pk, idcollection, id, keyword, keywordlevel, pk, path_word) 
		SELECT  main_pk, idcollection, id, keyword, keywordlevel, pk, path_word 
		FROM v_keyword_hierarchy_to_object;
	refresh materialized view mv_keyword_hierarchy_to_object_list_parent;
return returned;
EXCEPTION  
  WHEN OTHERS THEN 
  BEGIN 
    raise notice 'refresh view exception % %', SQLERRM, SQLSTATE;
    RETURN false;
  END;
END;
$$;


ALTER FUNCTION public.rmca_refresh_materialized_view() OWNER TO postgres;

--
-- TOC entry 1517 (class 1255 OID 514652)
-- Name: rmca_refresh_materialized_view_auto(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.rmca_refresh_materialized_view_auto() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
declare
returned boolean;
to_refresh_flag boolean;
begin 
returned:= false;


SELECT to_refresh INTO to_refresh_flag FROM tv_materialized_view_stamp LIMIT 1;
IF to_refresh_flag is true THEN

	SELECT rmca_refresh_materialized_view.rmca_refresh_materialized_view into returned FROM rmca_refresh_materialized_view();
	UPDATE tv_materialized_view_stamp SET to_refresh=false;
END IF;
return returned;
EXCEPTION  

  WHEN OTHERS THEN 
  BEGIN 
    raise notice 'refresh view exception % %', SQLERRM, SQLSTATE;
    RETURN false;
  END;
END;
$$;


ALTER FUNCTION public.rmca_refresh_materialized_view_auto() OWNER TO postgres;

SET default_tablespace = '';

--
-- TOC entry 352 (class 1259 OID 511782)
-- Name: alldata; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.alldata (
    idcontributor integer,
    people character varying,
    institut character varying,
    idcontribution integer,
    datetype character varying,
    contribution_date timestamp without time zone,
    pk integer,
    sample_idcollection character varying,
    idsample integer,
    fieldnum character varying,
    museumnum character varying,
    museumlocation character varying,
    boxnumber character varying,
    sampledescription text,
    weight integer,
    quantity character varying,
    size character varying,
    dimentioncode smallint,
    quality integer,
    slimplate boolean,
    chemicalanalysis boolean,
    type text,
    holotype boolean,
    paratype boolean,
    radioactivity smallint,
    loaninformation character varying,
    securitylevel integer,
    varioussampleinfo character varying,
    mname character varying,
    mformula character varying,
    fmparent character varying,
    mparent character varying,
    fmname character varying,
    weightsample text,
    observationhm character varying,
    mineral2 character varying,
    minnum character varying,
    weighttot text,
    mweight text,
    mesure1 double precision,
    lat double precision,
    long double precision,
    loc_date timestamp without time zone,
    place character varying,
    geodescription text,
    positiondescription character varying,
    descript character varying,
    statum_idcollection character varying,
    idpt integer,
    idstratum integer,
    descriptionstratum character varying,
    bottomstratum double precision,
    topstratum double precision,
    lithostratum character varying,
    title character varying,
    doc_idcollection character varying,
    iddoc integer,
    medium character varying,
    docinfo character varying,
    doccartotype character varying
);


ALTER TABLE public.alldata OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 486347)
-- Name: codecollection; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.codecollection (
    codecollection character varying NOT NULL,
    collection character varying,
    typeobjets character varying,
    zoneutilisation character varying,
    idresponsable integer DEFAULT 0,
    pk integer NOT NULL
);


ALTER TABLE public.codecollection OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 486345)
-- Name: codecollection_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.codecollection_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.codecollection_pk_seq OWNER TO postgres;

--
-- TOC entry 4424 (class 0 OID 0)
-- Dependencies: 239
-- Name: codecollection_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.codecollection_pk_seq OWNED BY public.codecollection.pk;


--
-- TOC entry 382 (class 1259 OID 535906)
-- Name: cpt_existing; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cpt_existing (
    count bigint
);


ALTER TABLE public.cpt_existing OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 486357)
-- Name: dcontribution; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dcontribution (
    idcontribution integer NOT NULL,
    datetype character varying NOT NULL,
    date timestamp without time zone,
    year integer,
    pk integer NOT NULL,
    date_format smallint,
    name character varying
);


ALTER TABLE public.dcontribution OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 486355)
-- Name: dcontribution_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dcontribution_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dcontribution_pk_seq OWNER TO postgres;

--
-- TOC entry 4425 (class 0 OID 0)
-- Dependencies: 241
-- Name: dcontribution_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dcontribution_pk_seq OWNED BY public.dcontribution.pk;


--
-- TOC entry 383 (class 1259 OID 592675)
-- Name: dcontributor_idcontributor_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dcontributor_idcontributor_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dcontributor_idcontributor_seq OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 486367)
-- Name: dcontributor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dcontributor (
    idcontributor integer DEFAULT nextval('public.dcontributor_idcontributor_seq'::regclass) NOT NULL,
    people character varying,
    peoplefonction character varying,
    peopletitre character varying,
    peoplestatut character varying,
    institut character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dcontributor OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 486365)
-- Name: dcontributor_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dcontributor_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dcontributor_pk_seq OWNER TO postgres;

--
-- TOC entry 4426 (class 0 OID 0)
-- Dependencies: 243
-- Name: dcontributor_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dcontributor_pk_seq OWNED BY public.dcontributor.pk;


--
-- TOC entry 246 (class 1259 OID 486377)
-- Name: ddocaerphoto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocaerphoto (
    idcollection character varying,
    iddoc integer,
    pid character varying,
    fid character varying,
    pk integer NOT NULL
);


ALTER TABLE public.ddocaerphoto OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 486375)
-- Name: ddocaerphoto_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocaerphoto_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocaerphoto_pk_seq OWNER TO postgres;

--
-- TOC entry 4427 (class 0 OID 0)
-- Dependencies: 245
-- Name: ddocaerphoto_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocaerphoto_pk_seq OWNED BY public.ddocaerphoto.pk;


--
-- TOC entry 248 (class 1259 OID 486387)
-- Name: ddocarchive; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocarchive (
    idcollection character varying NOT NULL,
    iddoc integer NOT NULL,
    extend integer DEFAULT 0,
    geology boolean,
    geochemistry boolean,
    geophysics boolean,
    exploration boolean,
    production boolean,
    reserves boolean,
    exploitation boolean,
    processing boolean,
    management boolean,
    report boolean,
    drillingcores boolean,
    maps boolean,
    paleontology boolean,
    sedimentology boolean,
    economy boolean,
    sample text,
    sgeology boolean,
    smineralogy boolean,
    spaleontology boolean,
    sconcentre boolean,
    yearlow integer,
    yearhigh integer,
    pk integer NOT NULL
);


ALTER TABLE public.ddocarchive OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 486385)
-- Name: ddocarchive_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocarchive_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocarchive_pk_seq OWNER TO postgres;

--
-- TOC entry 4428 (class 0 OID 0)
-- Dependencies: 247
-- Name: ddocarchive_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocarchive_pk_seq OWNED BY public.ddocarchive.pk;


--
-- TOC entry 250 (class 1259 OID 486397)
-- Name: ddocfilm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocfilm (
    idcollection character varying,
    iddoc integer,
    film character varying NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.ddocfilm OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 486395)
-- Name: ddocfilm_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocfilm_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocfilm_pk_seq OWNER TO postgres;

--
-- TOC entry 4429 (class 0 OID 0)
-- Dependencies: 249
-- Name: ddocfilm_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocfilm_pk_seq OWNED BY public.ddocfilm.pk;


--
-- TOC entry 252 (class 1259 OID 486407)
-- Name: ddoclinks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddoclinks (
    idcollection character varying,
    iddoc integer,
    idcollection2 character varying NOT NULL,
    iddoc2 integer NOT NULL,
    noticeid character varying,
    pk integer NOT NULL
);


ALTER TABLE public.ddoclinks OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 486405)
-- Name: ddoclinks_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddoclinks_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddoclinks_pk_seq OWNER TO postgres;

--
-- TOC entry 4430 (class 0 OID 0)
-- Dependencies: 251
-- Name: ddoclinks_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddoclinks_pk_seq OWNED BY public.ddoclinks.pk;


--
-- TOC entry 254 (class 1259 OID 486416)
-- Name: ddocmap; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocmap (
    idcollection character varying,
    iddoc integer,
    projection character varying,
    sheetnumber character varying,
    oncartesius boolean,
    pk integer NOT NULL
);


ALTER TABLE public.ddocmap OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 486414)
-- Name: ddocmap_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocmap_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocmap_pk_seq OWNER TO postgres;

--
-- TOC entry 4431 (class 0 OID 0)
-- Dependencies: 253
-- Name: ddocmap_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocmap_pk_seq OWNED BY public.ddocmap.pk;


--
-- TOC entry 256 (class 1259 OID 486427)
-- Name: ddocsatellite; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocsatellite (
    idcollection character varying DEFAULT '0'::character varying NOT NULL,
    iddoc integer NOT NULL,
    sattype character varying,
    satnum integer DEFAULT 0,
    orbit character varying,
    pathtrack character varying,
    rowframe character varying,
    date timestamp without time zone,
    "time" timestamp without time zone,
    sensor character varying,
    moderadar character varying,
    split character varying,
    ascdesc character varying,
    originale character varying,
    original boolean,
    backupnum character varying,
    backupno double precision,
    backupform character varying,
    modified boolean,
    orthorectified boolean,
    format character varying,
    pk integer NOT NULL
);


ALTER TABLE public.ddocsatellite OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 486425)
-- Name: ddocsatellite_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocsatellite_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocsatellite_pk_seq OWNER TO postgres;

--
-- TOC entry 4432 (class 0 OID 0)
-- Dependencies: 255
-- Name: ddocsatellite_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocsatellite_pk_seq OWNED BY public.ddocsatellite.pk;


--
-- TOC entry 258 (class 1259 OID 486438)
-- Name: ddocscale; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocscale (
    idcollection character varying,
    iddoc integer,
    scale integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.ddocscale OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 486436)
-- Name: ddocscale_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocscale_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocscale_pk_seq OWNER TO postgres;

--
-- TOC entry 4433 (class 0 OID 0)
-- Dependencies: 257
-- Name: ddocscale_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocscale_pk_seq OWNED BY public.ddocscale.pk;


--
-- TOC entry 260 (class 1259 OID 486447)
-- Name: ddoctitle; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddoctitle (
    idcollection character varying NOT NULL,
    iddoc integer NOT NULL,
    titlelevel smallint DEFAULT 1 NOT NULL,
    title character varying,
    pk integer NOT NULL
);


ALTER TABLE public.ddoctitle OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 486445)
-- Name: ddoctitle_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddoctitle_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddoctitle_pk_seq OWNER TO postgres;

--
-- TOC entry 4434 (class 0 OID 0)
-- Dependencies: 259
-- Name: ddoctitle_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddoctitle_pk_seq OWNED BY public.ddoctitle.pk;


--
-- TOC entry 365 (class 1259 OID 512551)
-- Name: seq_template_data_shared_pk; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_template_data_shared_pk
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_template_data_shared_pk OWNER TO postgres;

--
-- TOC entry 363 (class 1259 OID 512529)
-- Name: template_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.template_data (
    pk integer DEFAULT nextval('public.seq_template_data_shared_pk'::regclass) NOT NULL
);


ALTER TABLE public.template_data OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 486457)
-- Name: ddocument; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ddocument (
    idcollection character varying NOT NULL,
    iddoc integer NOT NULL,
    idpt integer DEFAULT 0,
    numarchive character varying,
    caption text,
    centralnum character varying,
    medium character varying,
    location character varying,
    numericallocation character varying,
    filename character varying,
    docinfo character varying,
    edition character varying,
    pubplace character varying,
    doccartotype character varying,
    pk integer DEFAULT nextval('public.seq_template_data_shared_pk'::regclass)
)
INHERITS (public.template_data);


ALTER TABLE public.ddocument OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 486455)
-- Name: ddocument_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ddocument_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ddocument_pk_seq OWNER TO postgres;

--
-- TOC entry 4435 (class 0 OID 0)
-- Dependencies: 261
-- Name: ddocument_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ddocument_pk_seq OWNED BY public.ddocument.pk;


--
-- TOC entry 264 (class 1259 OID 486467)
-- Name: dgestion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dgestion (
    idgestion integer DEFAULT 0 NOT NULL,
    idencodeur smallint DEFAULT 0,
    date timestamp without time zone DEFAULT now(),
    action character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dgestion OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 486465)
-- Name: dgestion_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dgestion_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dgestion_pk_seq OWNER TO postgres;

--
-- TOC entry 4436 (class 0 OID 0)
-- Dependencies: 263
-- Name: dgestion_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dgestion_pk_seq OWNED BY public.dgestion.pk;


--
-- TOC entry 266 (class 1259 OID 486479)
-- Name: dgestionnaire; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dgestionnaire (
    idencodeur smallint NOT NULL,
    people character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dgestionnaire OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 486477)
-- Name: dgestionnaire_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dgestionnaire_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dgestionnaire_pk_seq OWNER TO postgres;

--
-- TOC entry 4437 (class 0 OID 0)
-- Dependencies: 265
-- Name: dgestionnaire_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dgestionnaire_pk_seq OWNED BY public.dgestionnaire.pk;


--
-- TOC entry 268 (class 1259 OID 486489)
-- Name: dinstitute; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dinstitute (
    idinstitution integer DEFAULT 0 NOT NULL,
    acronyme character varying,
    fullname character varying,
    adresse character varying,
    ville character varying,
    contact character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dinstitute OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 486487)
-- Name: dinstitute_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dinstitute_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dinstitute_pk_seq OWNER TO postgres;

--
-- TOC entry 4438 (class 0 OID 0)
-- Dependencies: 267
-- Name: dinstitute_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dinstitute_pk_seq OWNED BY public.dinstitute.pk;


--
-- TOC entry 270 (class 1259 OID 486499)
-- Name: dkeyword; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dkeyword (
    idcollection character varying NOT NULL,
    id integer NOT NULL,
    keyword character varying NOT NULL,
    keywordlevel smallint,
    pk integer NOT NULL
);


ALTER TABLE public.dkeyword OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 486497)
-- Name: dkeyword_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dkeyword_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dkeyword_pk_seq OWNER TO postgres;

--
-- TOC entry 4439 (class 0 OID 0)
-- Dependencies: 269
-- Name: dkeyword_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dkeyword_pk_seq OWNED BY public.dkeyword.pk;


--
-- TOC entry 272 (class 1259 OID 486508)
-- Name: dlinkcontdoc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkcontdoc (
    idcontribution integer DEFAULT 0 NOT NULL,
    idcollection character varying NOT NULL,
    id integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkcontdoc OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 486506)
-- Name: dlinkcontdoc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkcontdoc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkcontdoc_pk_seq OWNER TO postgres;

--
-- TOC entry 4440 (class 0 OID 0)
-- Dependencies: 271
-- Name: dlinkcontdoc_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkcontdoc_pk_seq OWNED BY public.dlinkcontdoc.pk;


--
-- TOC entry 274 (class 1259 OID 486518)
-- Name: dlinkcontloc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkcontloc (
    idcontribution integer NOT NULL,
    idcollection character varying,
    id integer,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkcontloc OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 486516)
-- Name: dlinkcontloc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkcontloc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkcontloc_pk_seq OWNER TO postgres;

--
-- TOC entry 4441 (class 0 OID 0)
-- Dependencies: 273
-- Name: dlinkcontloc_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkcontloc_pk_seq OWNED BY public.dlinkcontloc.pk;


--
-- TOC entry 276 (class 1259 OID 486528)
-- Name: dlinkcontribute; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkcontribute (
    idcontribution integer DEFAULT 0 NOT NULL,
    idcontributor integer DEFAULT 0 NOT NULL,
    contributorrole character varying NOT NULL,
    contributororder smallint DEFAULT 0,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkcontribute OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 486526)
-- Name: dlinkcontribute_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkcontribute_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkcontribute_pk_seq OWNER TO postgres;

--
-- TOC entry 4442 (class 0 OID 0)
-- Dependencies: 275
-- Name: dlinkcontribute_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkcontribute_pk_seq OWNED BY public.dlinkcontribute.pk;


--
-- TOC entry 278 (class 1259 OID 486540)
-- Name: dlinkcontsam; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkcontsam (
    idcontribution integer NOT NULL,
    idcollection character varying NOT NULL,
    id integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkcontsam OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 486538)
-- Name: dlinkcontsam_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkcontsam_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkcontsam_pk_seq OWNER TO postgres;

--
-- TOC entry 4443 (class 0 OID 0)
-- Dependencies: 277
-- Name: dlinkcontsam_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkcontsam_pk_seq OWNED BY public.dlinkcontsam.pk;


--
-- TOC entry 280 (class 1259 OID 486549)
-- Name: dlinkdocloc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkdocloc (
    idcollecloc character varying,
    idpt integer,
    idcollecdoc character varying,
    iddoc integer,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkdocloc OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 486547)
-- Name: dlinkdocloc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkdocloc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkdocloc_pk_seq OWNER TO postgres;

--
-- TOC entry 4444 (class 0 OID 0)
-- Dependencies: 279
-- Name: dlinkdocloc_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkdocloc_pk_seq OWNED BY public.dlinkdocloc.pk;


--
-- TOC entry 282 (class 1259 OID 486559)
-- Name: dlinkdocsam; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkdocsam (
    idcollectiondoc character varying NOT NULL,
    iddoc integer NOT NULL,
    idcollectionsample character varying NOT NULL,
    idsample integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkdocsam OWNER TO postgres;

--
-- TOC entry 281 (class 1259 OID 486557)
-- Name: dlinkdocsam_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkdocsam_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkdocsam_pk_seq OWNER TO postgres;

--
-- TOC entry 4445 (class 0 OID 0)
-- Dependencies: 281
-- Name: dlinkdocsam_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkdocsam_pk_seq OWNED BY public.dlinkdocsam.pk;


--
-- TOC entry 284 (class 1259 OID 486568)
-- Name: dlinkgestdoc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkgestdoc (
    idcollecdoc character varying,
    iddoc integer,
    idgestion integer,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkgestdoc OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 486566)
-- Name: dlinkgestdoc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkgestdoc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkgestdoc_pk_seq OWNER TO postgres;

--
-- TOC entry 4446 (class 0 OID 0)
-- Dependencies: 283
-- Name: dlinkgestdoc_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkgestdoc_pk_seq OWNED BY public.dlinkgestdoc.pk;


--
-- TOC entry 286 (class 1259 OID 486577)
-- Name: dlinkgestloc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkgestloc (
    idcollection character varying,
    idpt integer,
    idgestion integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkgestloc OWNER TO postgres;

--
-- TOC entry 285 (class 1259 OID 486575)
-- Name: dlinkgestloc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkgestloc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkgestloc_pk_seq OWNER TO postgres;

--
-- TOC entry 4447 (class 0 OID 0)
-- Dependencies: 285
-- Name: dlinkgestloc_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkgestloc_pk_seq OWNED BY public.dlinkgestloc.pk;


--
-- TOC entry 288 (class 1259 OID 486586)
-- Name: dlinkgestsam; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinkgestsam (
    idcollection character varying NOT NULL,
    idsam integer NOT NULL,
    idgestion integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinkgestsam OWNER TO postgres;

--
-- TOC entry 287 (class 1259 OID 486584)
-- Name: dlinkgestsam_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinkgestsam_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinkgestsam_pk_seq OWNER TO postgres;

--
-- TOC entry 4448 (class 0 OID 0)
-- Dependencies: 287
-- Name: dlinkgestsam_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinkgestsam_pk_seq OWNED BY public.dlinkgestsam.pk;


--
-- TOC entry 290 (class 1259 OID 486595)
-- Name: dlinklocsam; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlinklocsam (
    idcollectionloc character varying NOT NULL,
    idpt integer NOT NULL,
    idstratum integer NOT NULL,
    idcollecsample character varying NOT NULL,
    idsample integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlinklocsam OWNER TO postgres;

--
-- TOC entry 289 (class 1259 OID 486593)
-- Name: dlinklocsam_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlinklocsam_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlinklocsam_pk_seq OWNER TO postgres;

--
-- TOC entry 4449 (class 0 OID 0)
-- Dependencies: 289
-- Name: dlinklocsam_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlinklocsam_pk_seq OWNED BY public.dlinklocsam.pk;


--
-- TOC entry 292 (class 1259 OID 486604)
-- Name: dloccarto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dloccarto (
    idcollection character varying DEFAULT '0'::character varying NOT NULL,
    idpt integer NOT NULL,
    cartoref character varying NOT NULL,
    cartoname character varying,
    cartoinfo character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dloccarto OWNER TO postgres;

--
-- TOC entry 291 (class 1259 OID 486602)
-- Name: dloccarto_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dloccarto_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dloccarto_pk_seq OWNER TO postgres;

--
-- TOC entry 4450 (class 0 OID 0)
-- Dependencies: 291
-- Name: dloccarto_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dloccarto_pk_seq OWNED BY public.dloccarto.pk;


--
-- TOC entry 294 (class 1259 OID 486614)
-- Name: dloccenter; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dloccenter (
    idcollection character varying NOT NULL,
    idpt integer NOT NULL,
    coord_lat numeric,
    coord_long numeric,
    altitude integer,
    date timestamp without time zone,
    fieldnum character varying,
    place character varying,
    geodescription text,
    positiondescription character varying,
    variousinfo character varying,
    docref character varying,
    idprecision integer,
    pk integer DEFAULT nextval('public.seq_template_data_shared_pk'::regclass),
    country text,
    epsg character varying DEFAULT 4326,
    wkt character varying,
    coordinate_format character varying,
    geom public.geometry,
    latitude_degrees integer,
    latitude_minutes numeric(10,8),
    latitude_seconds numeric(10,8),
    latitude_direction character varying,
    longitude_degrees integer,
    longitude_minutes numeric(10,8),
    longitude_seconds numeric(10,8),
    longitude_direction character varying,
    original_latitude character varying,
    original_longitude character varying
)
INHERITS (public.template_data);


ALTER TABLE public.dloccenter OWNER TO postgres;

--
-- TOC entry 293 (class 1259 OID 486612)
-- Name: dloccenter_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dloccenter_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dloccenter_pk_seq OWNER TO postgres;

--
-- TOC entry 4451 (class 0 OID 0)
-- Dependencies: 293
-- Name: dloccenter_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dloccenter_pk_seq OWNED BY public.dloccenter.pk;


--
-- TOC entry 296 (class 1259 OID 486626)
-- Name: dlocdrilling; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocdrilling (
    idcollection character varying,
    idpt integer,
    drilling character varying,
    diameter numeric(18,2),
    unitdiameter character varying,
    waterflow double precision,
    restingwater boolean,
    depthwatertable numeric(18,2),
    infowater character varying,
    infodrilling character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dlocdrilling OWNER TO postgres;

--
-- TOC entry 295 (class 1259 OID 486624)
-- Name: dlocdrilling_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocdrilling_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocdrilling_pk_seq OWNER TO postgres;

--
-- TOC entry 4452 (class 0 OID 0)
-- Dependencies: 295
-- Name: dlocdrilling_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlocdrilling_pk_seq OWNED BY public.dlocdrilling.pk;


--
-- TOC entry 298 (class 1259 OID 486640)
-- Name: dlocdrillingtype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocdrillingtype (
    idcollection character varying DEFAULT '0'::character varying NOT NULL,
    idpt integer NOT NULL,
    drillingtype character varying NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlocdrillingtype OWNER TO postgres;

--
-- TOC entry 297 (class 1259 OID 486638)
-- Name: dlocdrillingtype_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocdrillingtype_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocdrillingtype_pk_seq OWNER TO postgres;

--
-- TOC entry 4453 (class 0 OID 0)
-- Dependencies: 297
-- Name: dlocdrillingtype_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlocdrillingtype_pk_seq OWNED BY public.dlocdrillingtype.pk;


--
-- TOC entry 300 (class 1259 OID 486650)
-- Name: dlochydro; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlochydro (
    idcollection character varying DEFAULT '0'::character varying NOT NULL,
    idpt integer NOT NULL,
    hydroinfo character varying NOT NULL,
    hydroname character varying NOT NULL,
    tributaryof character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dlochydro OWNER TO postgres;

--
-- TOC entry 299 (class 1259 OID 486648)
-- Name: dlochydro_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlochydro_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlochydro_pk_seq OWNER TO postgres;

--
-- TOC entry 4454 (class 0 OID 0)
-- Dependencies: 299
-- Name: dlochydro_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlochydro_pk_seq OWNED BY public.dlochydro.pk;


--
-- TOC entry 302 (class 1259 OID 486660)
-- Name: dloclitho; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dloclitho (
    idcollection character varying NOT NULL,
    idpt integer NOT NULL,
    idstratum integer NOT NULL,
    lithostratum character varying,
    topstratum double precision,
    bottomstratum double precision,
    alternance boolean,
    descriptionstratum character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dloclitho OWNER TO postgres;

--
-- TOC entry 301 (class 1259 OID 486658)
-- Name: dloclitho_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dloclitho_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dloclitho_pk_seq OWNER TO postgres;

--
-- TOC entry 4455 (class 0 OID 0)
-- Dependencies: 301
-- Name: dloclitho_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dloclitho_pk_seq OWNED BY public.dloclitho.pk;


--
-- TOC entry 304 (class 1259 OID 486669)
-- Name: dlocpolygon; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocpolygon (
    idcollection character varying,
    idpt integer,
    polyarea character varying,
    polyname character varying NOT NULL,
    polydescription text,
    idpoly integer,
    pk integer NOT NULL
);


ALTER TABLE public.dlocpolygon OWNER TO postgres;

--
-- TOC entry 303 (class 1259 OID 486667)
-- Name: dlocpolygon_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocpolygon_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocpolygon_pk_seq OWNER TO postgres;

--
-- TOC entry 4456 (class 0 OID 0)
-- Dependencies: 303
-- Name: dlocpolygon_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlocpolygon_pk_seq OWNED BY public.dlocpolygon.pk;


--
-- TOC entry 306 (class 1259 OID 486680)
-- Name: dlocquadril; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocquadril (
    idcollection character varying,
    idpt integer,
    x1 double precision,
    y1 double precision,
    x2 double precision,
    y2 double precision,
    x3 double precision,
    y3 double precision,
    x4 double precision,
    y4 double precision,
    pk integer NOT NULL
);


ALTER TABLE public.dlocquadril OWNER TO postgres;

--
-- TOC entry 305 (class 1259 OID 486678)
-- Name: dlocquadril_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocquadril_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocquadril_pk_seq OWNER TO postgres;

--
-- TOC entry 4457 (class 0 OID 0)
-- Dependencies: 305
-- Name: dlocquadril_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlocquadril_pk_seq OWNED BY public.dlocquadril.pk;


--
-- TOC entry 308 (class 1259 OID 486689)
-- Name: dlocstratumdesc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocstratumdesc (
    idcollection character varying,
    idpt integer,
    idstratum integer,
    descript character varying NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.dlocstratumdesc OWNER TO postgres;

--
-- TOC entry 307 (class 1259 OID 486687)
-- Name: dlocstratumdesc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocstratumdesc_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocstratumdesc_pk_seq OWNER TO postgres;

--
-- TOC entry 310 (class 1259 OID 486699)
-- Name: dlocstructure; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dlocstructure (
    idcollection character varying NOT NULL,
    idpt integer NOT NULL,
    idstratum integer NOT NULL,
    nummesure integer NOT NULL,
    objectmesure character varying,
    dip integer,
    dipdirection integer,
    orientation integer,
    striation boolean,
    sens character varying,
    structureinfo character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dlocstructure OWNER TO postgres;

--
-- TOC entry 309 (class 1259 OID 486697)
-- Name: dlocstructure_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dlocstructure_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dlocstructure_pk_seq OWNER TO postgres;

--
-- TOC entry 4458 (class 0 OID 0)
-- Dependencies: 309
-- Name: dlocstructure_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dlocstructure_pk_seq OWNED BY public.dlocstructure.pk;


--
-- TOC entry 312 (class 1259 OID 486708)
-- Name: docplanvol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.docplanvol (
    fid character varying NOT NULL,
    nombloc character varying,
    bid character varying,
    bande character varying,
    comment character varying,
    pk integer NOT NULL
);


ALTER TABLE public.docplanvol OWNER TO postgres;

--
-- TOC entry 311 (class 1259 OID 486706)
-- Name: docplanvol_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.docplanvol_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.docplanvol_pk_seq OWNER TO postgres;

--
-- TOC entry 4459 (class 0 OID 0)
-- Dependencies: 311
-- Name: docplanvol_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.docplanvol_pk_seq OWNED BY public.docplanvol.pk;


--
-- TOC entry 314 (class 1259 OID 486717)
-- Name: dsamarays; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamarays (
    idcollection character varying,
    idsample integer,
    alpharay character varying,
    betaray character varying,
    gammaray character varying,
    xray character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dsamarays OWNER TO postgres;

--
-- TOC entry 313 (class 1259 OID 486715)
-- Name: dsamarays_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamarays_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamarays_pk_seq OWNER TO postgres;

--
-- TOC entry 4460 (class 0 OID 0)
-- Dependencies: 313
-- Name: dsamarays_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamarays_pk_seq OWNED BY public.dsamarays.pk;


--
-- TOC entry 316 (class 1259 OID 486726)
-- Name: dsamgranulo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamgranulo (
    idcollection character varying NOT NULL,
    idsample integer NOT NULL,
    weighttot double precision,
    weightsand double precision DEFAULT 0,
    w_above_2000 double precision,
    w_2000 double precision,
    w_1400 double precision,
    w_1000 double precision,
    w_710 double precision,
    w_500 double precision,
    w_355 double precision,
    w_250 double precision,
    w_180 double precision,
    w_125 double precision,
    w_90 double precision,
    w_63 double precision,
    description character varying,
    pc double precision,
    pccum double precision,
    date timestamp without time zone,
    pk integer NOT NULL
);


ALTER TABLE public.dsamgranulo OWNER TO postgres;

--
-- TOC entry 315 (class 1259 OID 486724)
-- Name: dsamgranulo_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamgranulo_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamgranulo_pk_seq OWNER TO postgres;

--
-- TOC entry 4461 (class 0 OID 0)
-- Dependencies: 315
-- Name: dsamgranulo_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamgranulo_pk_seq OWNED BY public.dsamgranulo.pk;


--
-- TOC entry 318 (class 1259 OID 486736)
-- Name: dsamheavymin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamheavymin (
    idcollection character varying NOT NULL,
    idsample integer NOT NULL,
    weighthm double precision,
    weightsample double precision,
    observationhm character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dsamheavymin OWNER TO postgres;

--
-- TOC entry 320 (class 1259 OID 486745)
-- Name: dsamheavymin2; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamheavymin2 (
    idcollection character varying NOT NULL,
    idsample integer NOT NULL,
    mineral character varying NOT NULL,
    minnum smallint,
    observationhm character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dsamheavymin2 OWNER TO postgres;

--
-- TOC entry 319 (class 1259 OID 486743)
-- Name: dsamheavymin2_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamheavymin2_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamheavymin2_pk_seq OWNER TO postgres;

--
-- TOC entry 4462 (class 0 OID 0)
-- Dependencies: 319
-- Name: dsamheavymin2_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamheavymin2_pk_seq OWNED BY public.dsamheavymin2.pk;


--
-- TOC entry 317 (class 1259 OID 486734)
-- Name: dsamheavymin_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamheavymin_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamheavymin_pk_seq OWNER TO postgres;

--
-- TOC entry 4463 (class 0 OID 0)
-- Dependencies: 317
-- Name: dsamheavymin_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamheavymin_pk_seq OWNED BY public.dsamheavymin.pk;


--
-- TOC entry 355 (class 1259 OID 511871)
-- Name: dsammagsusc_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsammagsusc_pk_seq
    START WITH 29749
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsammagsusc_pk_seq OWNER TO postgres;

--
-- TOC entry 321 (class 1259 OID 486754)
-- Name: dsammagsusc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsammagsusc (
    idcollection character varying,
    idsample integer,
    weight double precision,
    exponent integer,
    mesure1 double precision,
    mesure2 double precision,
    mesure3 double precision,
    mesure4 double precision,
    mesure5 double precision,
    mesure6 double precision,
    pk integer DEFAULT nextval('public.dsammagsusc_pk_seq'::regclass) NOT NULL
);


ALTER TABLE public.dsammagsusc OWNER TO postgres;

--
-- TOC entry 323 (class 1259 OID 486764)
-- Name: dsamminerals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamminerals (
    idcollection character varying NOT NULL,
    idsample integer NOT NULL,
    idmineral integer NOT NULL,
    grade smallint DEFAULT 0,
    pk integer NOT NULL
);


ALTER TABLE public.dsamminerals OWNER TO postgres;

--
-- TOC entry 322 (class 1259 OID 486762)
-- Name: dsamminerals_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamminerals_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamminerals_pk_seq OWNER TO postgres;

--
-- TOC entry 4464 (class 0 OID 0)
-- Dependencies: 322
-- Name: dsamminerals_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamminerals_pk_seq OWNED BY public.dsamminerals.pk;


--
-- TOC entry 324 (class 1259 OID 486774)
-- Name: dsample; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsample (
    idcollection character varying NOT NULL,
    idsample integer NOT NULL,
    fieldnum character varying,
    museumnum character varying,
    museumlocation character varying,
    boxnumber character varying,
    sampledescription text,
    weight integer,
    quantity character varying,
    size character varying,
    dimentioncode smallint,
    quality character varying,
    slimplate boolean,
    chemicalanalysis boolean,
    holotype boolean,
    paratype boolean,
    radioactivity smallint,
    loaninformation character varying,
    securitylevel integer,
    varioussampleinfo character varying,
    pk integer DEFAULT nextval('public.seq_template_data_shared_pk'::regclass)
)
INHERITS (public.template_data);


ALTER TABLE public.dsample OWNER TO postgres;

--
-- TOC entry 341 (class 1259 OID 509363)
-- Name: dsample_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsample_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsample_pk_seq OWNER TO postgres;

--
-- TOC entry 326 (class 1259 OID 486783)
-- Name: dsamslimplate; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dsamslimplate (
    idcollection character varying,
    idsample integer,
    numplate character varying NOT NULL,
    platedescription text,
    platevariousinfo character varying,
    pk integer NOT NULL
);


ALTER TABLE public.dsamslimplate OWNER TO postgres;

--
-- TOC entry 325 (class 1259 OID 486781)
-- Name: dsamslimplate_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dsamslimplate_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dsamslimplate_pk_seq OWNER TO postgres;

--
-- TOC entry 4465 (class 0 OID 0)
-- Dependencies: 325
-- Name: dsamslimplate_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dsamslimplate_pk_seq OWNED BY public.dsamslimplate.pk;


--
-- TOC entry 351 (class 1259 OID 511770)
-- Name: duser_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.duser_id_seq
    START WITH 5
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.duser_id_seq OWNER TO postgres;

--
-- TOC entry 343 (class 1259 OID 511584)
-- Name: duser; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.duser (
    id integer DEFAULT nextval('public.duser_id_seq'::regclass) NOT NULL,
    username character varying NOT NULL,
    username_canonical character varying NOT NULL,
    first_name character varying NOT NULL,
    last_name character varying NOT NULL,
    email character varying,
    email_canonical character varying,
    enabled boolean NOT NULL,
    salt character varying,
    password character varying NOT NULL,
    last_login timestamp without time zone,
    confirmation_token character varying,
    password_requested_at timestamp without time zone,
    roles character varying,
    groups character varying[]
);


ALTER TABLE public.duser OWNER TO postgres;

--
-- TOC entry 342 (class 1259 OID 511461)
-- Name: duser_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.duser_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.duser_pk_seq OWNER TO postgres;

--
-- TOC entry 344 (class 1259 OID 511607)
-- Name: fos_group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fos_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fos_group_id_seq OWNER TO postgres;

--
-- TOC entry 345 (class 1259 OID 511609)
-- Name: fos_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fos_group (
    id integer DEFAULT nextval('public.fos_group_id_seq'::regclass) NOT NULL,
    name character varying NOT NULL,
    roles character varying[]
);


ALTER TABLE public.fos_group OWNER TO postgres;

--
-- TOC entry 346 (class 1259 OID 511618)
-- Name: fos_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fos_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fos_role_id_seq OWNER TO postgres;

--
-- TOC entry 347 (class 1259 OID 511642)
-- Name: fos_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fos_role (
    id integer DEFAULT nextval('public.fos_role_id_seq'::regclass) NOT NULL,
    name character varying NOT NULL,
    parent character varying[],
    children character varying[],
    users character varying
);


ALTER TABLE public.fos_role OWNER TO postgres;

--
-- TOC entry 349 (class 1259 OID 511732)
-- Name: fos_user_collections_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fos_user_collections_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fos_user_collections_pk_seq OWNER TO postgres;

--
-- TOC entry 350 (class 1259 OID 511734)
-- Name: fos_user_collections; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fos_user_collections (
    user_id integer NOT NULL,
    role character varying NOT NULL,
    collection_id integer NOT NULL,
    pk integer DEFAULT nextval('public.fos_user_collections_pk_seq'::regclass) NOT NULL
);


ALTER TABLE public.fos_user_collections OWNER TO postgres;

--
-- TOC entry 348 (class 1259 OID 511663)
-- Name: fos_user_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fos_user_role (
    user_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE public.fos_user_role OWNER TO postgres;

--
-- TOC entry 364 (class 1259 OID 512534)
-- Name: generic_keyword; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.generic_keyword (
    keyword character varying NOT NULL,
    keywordlevel smallint NOT NULL,
    pk integer NOT NULL,
    fk_shared_pk integer
);


ALTER TABLE public.generic_keyword OWNER TO postgres;

--
-- TOC entry 328 (class 1259 OID 486792)
-- Name: larea; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.larea (
    polyarea character varying NOT NULL,
    parent character varying,
    pk integer NOT NULL
);


ALTER TABLE public.larea OWNER TO postgres;

--
-- TOC entry 327 (class 1259 OID 486790)
-- Name: larea_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.larea_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.larea_pk_seq OWNER TO postgres;

--
-- TOC entry 4466 (class 0 OID 0)
-- Dependencies: 327
-- Name: larea_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.larea_pk_seq OWNED BY public.larea.pk;


--
-- TOC entry 330 (class 1259 OID 486801)
-- Name: lkeywords; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lkeywords (
    wordson character varying NOT NULL,
    wordfather character varying NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.lkeywords OWNER TO postgres;

--
-- TOC entry 381 (class 1259 OID 528502)
-- Name: lkeywords_bck20210722; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lkeywords_bck20210722 (
    wordson character varying,
    wordfather character varying,
    pk integer
);


ALTER TABLE public.lkeywords_bck20210722 OWNER TO postgres;

--
-- TOC entry 329 (class 1259 OID 486799)
-- Name: lkeywords_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lkeywords_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lkeywords_pk_seq OWNER TO postgres;

--
-- TOC entry 4467 (class 0 OID 0)
-- Dependencies: 329
-- Name: lkeywords_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lkeywords_pk_seq OWNED BY public.lkeywords.pk;


--
-- TOC entry 332 (class 1259 OID 486810)
-- Name: lmapreftype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lmapreftype (
    reftype character varying NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.lmapreftype OWNER TO postgres;

--
-- TOC entry 331 (class 1259 OID 486808)
-- Name: lmapreftype_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lmapreftype_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lmapreftype_pk_seq OWNER TO postgres;

--
-- TOC entry 4468 (class 0 OID 0)
-- Dependencies: 331
-- Name: lmapreftype_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lmapreftype_pk_seq OWNED BY public.lmapreftype.pk;


--
-- TOC entry 334 (class 1259 OID 486819)
-- Name: lmedium; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lmedium (
    medium character varying NOT NULL,
    domaincode character varying,
    definition character varying,
    pk integer NOT NULL
);


ALTER TABLE public.lmedium OWNER TO postgres;

--
-- TOC entry 333 (class 1259 OID 486817)
-- Name: lmedium_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lmedium_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lmedium_pk_seq OWNER TO postgres;

--
-- TOC entry 4469 (class 0 OID 0)
-- Dependencies: 333
-- Name: lmedium_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lmedium_pk_seq OWNED BY public.lmedium.pk;


--
-- TOC entry 336 (class 1259 OID 486828)
-- Name: lminerals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lminerals (
    idmineral integer NOT NULL,
    rank character varying,
    fmname character varying,
    mname character varying,
    mformula character varying,
    fmparent character varying,
    mparent character varying,
    pk integer NOT NULL,
    id integer
);


ALTER TABLE public.lminerals OWNER TO postgres;

--
-- TOC entry 335 (class 1259 OID 486826)
-- Name: lminerals_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lminerals_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lminerals_pk_seq OWNER TO postgres;

--
-- TOC entry 4470 (class 0 OID 0)
-- Dependencies: 335
-- Name: lminerals_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lminerals_pk_seq OWNED BY public.lminerals.pk;


--
-- TOC entry 338 (class 1259 OID 486837)
-- Name: lprecision; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lprecision (
    "precision" character varying,
    idprecision integer NOT NULL,
    pk integer NOT NULL
);


ALTER TABLE public.lprecision OWNER TO postgres;

--
-- TOC entry 337 (class 1259 OID 486835)
-- Name: lprecision_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lprecision_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lprecision_pk_seq OWNER TO postgres;

--
-- TOC entry 4471 (class 0 OID 0)
-- Dependencies: 337
-- Name: lprecision_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lprecision_pk_seq OWNED BY public.lprecision.pk;


--
-- TOC entry 340 (class 1259 OID 486846)
-- Name: ltitlelevel; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ltitlelevel (
    titlelevel smallint DEFAULT 1 NOT NULL,
    titlecaption character varying,
    pk integer NOT NULL
);


ALTER TABLE public.ltitlelevel OWNER TO postgres;

--
-- TOC entry 339 (class 1259 OID 486844)
-- Name: ltitlelevel_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ltitlelevel_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ltitlelevel_pk_seq OWNER TO postgres;

--
-- TOC entry 4472 (class 0 OID 0)
-- Dependencies: 339
-- Name: ltitlelevel_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ltitlelevel_pk_seq OWNED BY public.ltitlelevel.pk;


--
-- TOC entry 360 (class 1259 OID 512476)
-- Name: v_all_contributions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_all_contributions AS
 SELECT dcontribution.idcontribution,
    dcontribution.datetype,
    dcontribution.date,
    dcontribution.year,
    dlinkcontribute.contributorrole,
    dlinkcontribute.contributororder,
    dcontributor.people,
    dcontributor.peoplefonction,
    dcontributor.peopletitre,
    dcontributor.peoplestatut,
    dcontributor.institut,
    dlinkcontdoc.id AS iddoc,
    dlinkcontdoc.idcollection AS idcollectiondoc,
    dlinkcontsam.id AS idsample,
    dlinkcontsam.idcollection AS idcollectionsample,
    dlinkcontloc.id AS idloccenter,
    dlinkcontloc.idcollection AS idcollectionloc,
    dcontributor.idcontributor
   FROM (((((public.dcontribution
     JOIN public.dlinkcontribute ON ((dcontribution.idcontribution = dlinkcontribute.idcontribution)))
     JOIN public.dcontributor ON ((dlinkcontribute.idcontributor = dcontributor.idcontributor)))
     FULL JOIN public.dlinkcontdoc ON ((dcontribution.idcontribution = dlinkcontdoc.idcontribution)))
     FULL JOIN public.dlinkcontsam ON ((dcontribution.idcontribution = dlinkcontsam.idcontribution)))
     FULL JOIN public.dlinkcontloc ON ((dcontribution.idcontribution = dlinkcontloc.idcontribution)));


ALTER TABLE public.v_all_contributions OWNER TO postgres;

--
-- TOC entry 367 (class 1259 OID 512613)
-- Name: v_all_contributions_to_object; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_all_contributions_to_object AS
 SELECT DISTINCT c.pk,
    'document'::text AS relation_type,
    a.idcontribution,
    a.datetype,
    a.date,
    a.year,
    a.contributorrole,
    a.contributororder,
    a.people,
    a.peoplefonction,
    a.peopletitre,
    a.peoplestatut,
    a.institut,
    a.idcontributor
   FROM ((public.v_all_contributions a
     LEFT JOIN public.dlinkcontdoc b ON ((a.idcontribution = b.idcontribution)))
     LEFT JOIN public.ddocument c ON ((((b.idcollection)::text = (c.idcollection)::text) AND (b.id = c.iddoc))))
UNION
 SELECT DISTINCT c.pk,
    'sample'::text AS relation_type,
    a.idcontribution,
    a.datetype,
    a.date,
    a.year,
    a.contributorrole,
    a.contributororder,
    a.people,
    a.peoplefonction,
    a.peopletitre,
    a.peoplestatut,
    a.institut,
    a.idcontributor
   FROM ((public.v_all_contributions a
     JOIN public.dlinkcontsam b ON ((a.idcontribution = b.idcontribution)))
     JOIN public.dsample c ON ((((b.idcollection)::text = (c.idcollection)::text) AND (b.id = c.idsample))))
UNION
 SELECT DISTINCT c.pk,
    'point'::text AS relation_type,
    a.idcontribution,
    a.datetype,
    a.date,
    a.year,
    a.contributorrole,
    a.contributororder,
    a.people,
    a.peoplefonction,
    a.peopletitre,
    a.peoplestatut,
    a.institut,
    a.idcontributor
   FROM ((public.v_all_contributions a
     JOIN public.dlinkcontloc b ON ((a.idcontribution = b.idcontribution)))
     JOIN public.dloccenter c ON ((((b.idcollection)::text = (c.idcollection)::text) AND (b.id = c.idpt))));


ALTER TABLE public.v_all_contributions_to_object OWNER TO postgres;

--
-- TOC entry 368 (class 1259 OID 512618)
-- Name: v_all_contributions_to_object_agg; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_all_contributions_to_object_agg AS
 SELECT v_all_contributions_to_object.pk,
    v_all_contributions_to_object.relation_type,
    v_all_contributions_to_object.idcontribution,
    v_all_contributions_to_object.datetype,
    v_all_contributions_to_object.date,
    v_all_contributions_to_object.year,
    v_all_contributions_to_object.contributorrole,
    array_agg(v_all_contributions_to_object.people ORDER BY v_all_contributions_to_object.contributororder) AS people_list,
    array_agg(v_all_contributions_to_object.institut ORDER BY v_all_contributions_to_object.contributororder) AS institut_list
   FROM public.v_all_contributions_to_object
  GROUP BY v_all_contributions_to_object.pk, v_all_contributions_to_object.relation_type, v_all_contributions_to_object.idcontribution, v_all_contributions_to_object.datetype, v_all_contributions_to_object.date, v_all_contributions_to_object.year, v_all_contributions_to_object.contributorrole;


ALTER TABLE public.v_all_contributions_to_object_agg OWNER TO postgres;

--
-- TOC entry 369 (class 1259 OID 512622)
-- Name: v_all_contributions_to_object_agg_merge; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_all_contributions_to_object_agg_merge AS
 WITH a AS (
         SELECT v_all_contributions_to_object_agg.pk,
            v_all_contributions_to_object_agg.relation_type,
            v_all_contributions_to_object_agg.idcontribution,
            v_all_contributions_to_object_agg.datetype,
            v_all_contributions_to_object_agg.date,
            v_all_contributions_to_object_agg.year,
            v_all_contributions_to_object_agg.contributorrole,
            array_to_string(v_all_contributions_to_object_agg.people_list, '; '::text) AS people_list,
            array_to_string(v_all_contributions_to_object_agg.institut_list, '; '::text) AS institut_list
           FROM public.v_all_contributions_to_object_agg
        )
 SELECT DISTINCT a.pk,
    a.relation_type,
    (((a.datetype)::text || '; '::text) || ((a.year)::character varying)::text) AS dates,
    string_agg(((initcap((a.contributorrole)::text) || ': '::text) || a.people_list), ' . '::text ORDER BY a.contributorrole) AS contributors,
    string_agg(((initcap((a.contributorrole)::text) || ': '::text) || a.institut_list), ' . '::text ORDER BY a.contributorrole) AS institutions
   FROM a
  GROUP BY a.pk, a.relation_type, a.idcontribution, a.datetype, a.date, a.year;


ALTER TABLE public.v_all_contributions_to_object_agg_merge OWNER TO postgres;

--
-- TOC entry 375 (class 1259 OID 514365)
-- Name: mv_all_contributions_to_object_agg_merge; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_all_contributions_to_object_agg_merge AS
 SELECT v_all_contributions_to_object_agg_merge.pk,
    v_all_contributions_to_object_agg_merge.relation_type,
    v_all_contributions_to_object_agg_merge.dates,
    v_all_contributions_to_object_agg_merge.contributors,
    v_all_contributions_to_object_agg_merge.institutions
   FROM public.v_all_contributions_to_object_agg_merge
  WITH NO DATA;


ALTER TABLE public.mv_all_contributions_to_object_agg_merge OWNER TO postgres;

--
-- TOC entry 374 (class 1259 OID 514326)
-- Name: tv_keyword_hierarchy_to_object; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tv_keyword_hierarchy_to_object (
    main_pk integer,
    idcollection character varying,
    id integer,
    keyword character varying,
    keywordlevel smallint,
    pk integer,
    path_word character varying,
    level integer
);


ALTER TABLE public.tv_keyword_hierarchy_to_object OWNER TO postgres;

--
-- TOC entry 379 (class 1259 OID 514892)
-- Name: mv_keyword_hierarchy_to_object_list_parent; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_keyword_hierarchy_to_object_list_parent AS
 SELECT DISTINCT unnest(regexp_split_to_array((tv_keyword_hierarchy_to_object.path_word)::text, '/'::text)) AS word
   FROM public.tv_keyword_hierarchy_to_object
  WITH NO DATA;


ALTER TABLE public.mv_keyword_hierarchy_to_object_list_parent OWNER TO postgres;

--
-- TOC entry 384 (class 1259 OID 593284)
-- Name: mv_merge_keywords; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_merge_keywords AS
 SELECT DISTINCT unnest(regexp_split_to_array((tv_keyword_hierarchy_to_object.path_word)::text, '/'::text)) AS word
   FROM public.tv_keyword_hierarchy_to_object
UNION
 SELECT DISTINCT lower((dloclitho.lithostratum)::text) AS word
   FROM public.dloclitho
UNION
 SELECT DISTINCT lower((dlocdrillingtype.drillingtype)::text) AS word
   FROM public.dlocdrillingtype
  WITH NO DATA;


ALTER TABLE public.mv_merge_keywords OWNER TO postgres;

--
-- TOC entry 366 (class 1259 OID 512599)
-- Name: v_rmca_main_objects_description; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_main_objects_description AS
SELECT
    NULL::integer AS pk,
    NULL::text AS main_type,
    NULL::character varying AS col_1_name,
    NULL::character varying AS col_1_value,
    NULL::character varying AS col_2_name,
    NULL::character varying AS col_2_value,
    NULL::character varying AS col_3_name,
    NULL::character varying AS col_3_value,
    NULL::character varying AS col_4_name,
    NULL::text AS col_4_value;


ALTER TABLE public.v_rmca_main_objects_description OWNER TO postgres;

--
-- TOC entry 373 (class 1259 OID 514270)
-- Name: mv_rmca_main_objects_description; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_rmca_main_objects_description AS
 SELECT v_rmca_main_objects_description.pk,
    v_rmca_main_objects_description.main_type,
    v_rmca_main_objects_description.col_1_name,
    v_rmca_main_objects_description.col_1_value,
    v_rmca_main_objects_description.col_2_name,
    v_rmca_main_objects_description.col_2_value,
    v_rmca_main_objects_description.col_3_name,
    v_rmca_main_objects_description.col_3_value,
    v_rmca_main_objects_description.col_4_name,
    v_rmca_main_objects_description.col_4_value
   FROM public.v_rmca_main_objects_description
  WITH NO DATA;


ALTER TABLE public.mv_rmca_main_objects_description OWNER TO postgres;

--
-- TOC entry 358 (class 1259 OID 512453)
-- Name: v_rmca_document_2_sample; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_document_2_sample AS
 SELECT ddocument.pk AS fk_document,
    ddocument.idcollection,
    ddocument.iddoc,
    dsample.pk AS fk_sample,
    dsample.idcollection AS idcollectionsample,
    dsample.idsample
   FROM ((public.ddocument
     JOIN public.dlinkdocsam ON ((((ddocument.idcollection)::text = (dlinkdocsam.idcollectiondoc)::text) AND (ddocument.iddoc = dlinkdocsam.iddoc))))
     LEFT JOIN public.dsample ON ((((dlinkdocsam.idcollectionsample)::text = (dsample.idcollection)::text) AND (dlinkdocsam.idsample = dsample.idsample))));


ALTER TABLE public.v_rmca_document_2_sample OWNER TO postgres;

--
-- TOC entry 357 (class 1259 OID 512438)
-- Name: v_rmca_loc_2_doc_loc_2_sample; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_loc_2_doc_loc_2_sample AS
 SELECT dloccenter.pk AS fk_locality,
    dloccenter.idcollection AS idcollectionloc,
    dloccenter.idpt,
    ddocument.pk AS fk_document,
    ddocument.idcollection,
    ddocument.iddoc,
    dloclitho.pk AS fk_litho,
    dloclitho.idstratum,
    dsample.pk AS fk_sample,
    dsample.idcollection AS idcollectionsample,
    dsample.idsample,
    dloccenter.coord_lat,
    dloccenter.coord_long
   FROM (((((public.dloccenter
     FULL JOIN public.dlinkdocloc ON (((dloccenter.idpt = dlinkdocloc.idpt) AND ((dloccenter.idcollection)::text = (dlinkdocloc.idcollecloc)::text))))
     FULL JOIN public.ddocument ON ((((dlinkdocloc.idcollecdoc)::text = (ddocument.idcollection)::text) AND (dlinkdocloc.iddoc = ddocument.iddoc))))
     FULL JOIN public.dloclitho ON ((((dloccenter.idcollection)::text = (dloclitho.idcollection)::text) AND (dloccenter.idpt = dloclitho.idpt))))
     FULL JOIN public.dlinklocsam ON (((dloclitho.idpt = dlinklocsam.idpt) AND (dloclitho.idstratum = dlinklocsam.idstratum) AND ((dloclitho.idcollection)::text = (dlinklocsam.idcollectionloc)::text))))
     FULL JOIN public.dsample ON ((((dlinklocsam.idcollecsample)::text = (dsample.idcollection)::text) AND (dlinklocsam.idsample = dsample.idsample))));


ALTER TABLE public.v_rmca_loc_2_doc_loc_2_sample OWNER TO postgres;

--
-- TOC entry 359 (class 1259 OID 512458)
-- Name: v_rmca_merge_all_objects; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_merge_all_objects AS
 WITH a AS (
         SELECT v_rmca_loc_2_doc_loc_2_sample.fk_locality,
            v_rmca_loc_2_doc_loc_2_sample.idcollectionloc,
            v_rmca_loc_2_doc_loc_2_sample.idpt,
            v_rmca_loc_2_doc_loc_2_sample.fk_document,
            v_rmca_loc_2_doc_loc_2_sample.idcollection,
            v_rmca_loc_2_doc_loc_2_sample.iddoc,
            v_rmca_loc_2_doc_loc_2_sample.fk_litho,
            v_rmca_loc_2_doc_loc_2_sample.idstratum,
            v_rmca_loc_2_doc_loc_2_sample.fk_sample,
            v_rmca_loc_2_doc_loc_2_sample.idcollectionsample,
            v_rmca_loc_2_doc_loc_2_sample.idsample,
            v_rmca_loc_2_doc_loc_2_sample.coord_lat,
            v_rmca_loc_2_doc_loc_2_sample.coord_long
           FROM public.v_rmca_loc_2_doc_loc_2_sample
        ), b AS (
         SELECT v_rmca_document_2_sample.fk_document,
            v_rmca_document_2_sample.idcollection,
            v_rmca_document_2_sample.iddoc,
            v_rmca_document_2_sample.fk_sample,
            v_rmca_document_2_sample.idcollectionsample,
            v_rmca_document_2_sample.idsample,
            NULL::numeric AS coord_lat,
            NULL::numeric AS coord_long
           FROM public.v_rmca_document_2_sample
          WHERE ((NOT (v_rmca_document_2_sample.fk_document IN ( SELECT a.fk_document
                   FROM a))) OR (NOT (v_rmca_document_2_sample.fk_sample IN ( SELECT a.fk_sample
                   FROM a))) OR ((NOT (v_rmca_document_2_sample.fk_document IN ( SELECT a.fk_document
                   FROM a))) AND (NOT (v_rmca_document_2_sample.fk_sample IN ( SELECT a.fk_sample
                   FROM a)))))
        )
 SELECT a.fk_locality,
    a.idcollectionloc,
    a.idpt,
    a.fk_document,
    a.idcollection,
    a.iddoc,
    a.fk_litho,
    a.idstratum,
    a.fk_sample,
    a.idcollectionsample,
    a.idsample,
    a.coord_lat,
    a.coord_long
   FROM a
UNION
 SELECT NULL::integer AS fk_locality,
    NULL::character varying AS idcollectionloc,
    NULL::integer AS idpt,
    b.fk_document,
    b.idcollection,
    b.iddoc,
    NULL::integer AS fk_litho,
    NULL::integer AS idstratum,
    b.fk_sample,
    b.idcollectionsample,
    b.idsample,
    b.coord_lat,
    b.coord_long
   FROM b;


ALTER TABLE public.v_rmca_merge_all_objects OWNER TO postgres;

--
-- TOC entry 362 (class 1259 OID 512521)
-- Name: v_rmca_merge_all_objects_vertical_expand; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_merge_all_objects_vertical_expand AS
 WITH a AS (
         SELECT v_rmca_merge_all_objects.fk_locality,
            v_rmca_merge_all_objects.idcollectionloc,
            v_rmca_merge_all_objects.idpt,
            v_rmca_merge_all_objects.fk_document,
            v_rmca_merge_all_objects.idcollection,
            v_rmca_merge_all_objects.iddoc,
            v_rmca_merge_all_objects.fk_litho,
            v_rmca_merge_all_objects.idstratum,
            v_rmca_merge_all_objects.fk_sample,
            v_rmca_merge_all_objects.idcollectionsample,
            v_rmca_merge_all_objects.idsample,
            v_rmca_merge_all_objects.coord_long,
            v_rmca_merge_all_objects.coord_lat
           FROM public.v_rmca_merge_all_objects
        )
 SELECT DISTINCT a.fk_locality AS main_pk,
    'locality'::text AS type,
    a.fk_document,
    a.fk_sample,
    NULL::integer AS fk_localitie,
    a.coord_long,
    a.coord_lat,
    a.idpt AS idobject,
    a.idcollectionloc AS idcollection
   FROM a
  WHERE (a.fk_locality IS NOT NULL)
UNION
 SELECT DISTINCT a.fk_document AS main_pk,
    'document'::text AS type,
    NULL::integer AS fk_document,
    a.fk_sample,
    a.fk_locality AS fk_localitie,
    a.coord_long,
    a.coord_lat,
    a.iddoc AS idobject,
    a.idcollection
   FROM a
  WHERE (a.fk_document IS NOT NULL)
UNION
 SELECT DISTINCT a.fk_sample AS main_pk,
    'sample'::text AS type,
    a.fk_document,
    NULL::integer AS fk_sample,
    a.fk_locality AS fk_localitie,
    a.coord_long,
    a.coord_lat,
    a.idsample AS idobject,
    a.idcollectionsample AS idcollection
   FROM a
  WHERE (a.fk_sample IS NOT NULL);


ALTER TABLE public.v_rmca_merge_all_objects_vertical_expand OWNER TO postgres;

--
-- TOC entry 372 (class 1259 OID 514263)
-- Name: mv_rmca_merge_all_objects_vertical_expand; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_rmca_merge_all_objects_vertical_expand AS
 SELECT v_rmca_merge_all_objects_vertical_expand.main_pk,
    v_rmca_merge_all_objects_vertical_expand.type,
    v_rmca_merge_all_objects_vertical_expand.fk_document,
    v_rmca_merge_all_objects_vertical_expand.fk_sample,
    v_rmca_merge_all_objects_vertical_expand.fk_localitie,
    v_rmca_merge_all_objects_vertical_expand.coord_long,
    v_rmca_merge_all_objects_vertical_expand.coord_lat,
    v_rmca_merge_all_objects_vertical_expand.idobject,
    v_rmca_merge_all_objects_vertical_expand.idcollection
   FROM public.v_rmca_merge_all_objects_vertical_expand
  WITH NO DATA;


ALTER TABLE public.mv_rmca_merge_all_objects_vertical_expand OWNER TO postgres;

--
-- TOC entry 378 (class 1259 OID 514535)
-- Name: t_data_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.t_data_log (
    pk bigint NOT NULL,
    referenced_table character varying,
    record_id integer,
    user_ref integer,
    action character varying,
    old_value json,
    new_value json,
    modification_date_time timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.t_data_log OWNER TO postgres;

--
-- TOC entry 377 (class 1259 OID 514533)
-- Name: t_data_log_pk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.t_data_log_pk_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.t_data_log_pk_seq OWNER TO postgres;

--
-- TOC entry 4473 (class 0 OID 0)
-- Dependencies: 377
-- Name: t_data_log_pk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.t_data_log_pk_seq OWNED BY public.t_data_log.pk;


--
-- TOC entry 376 (class 1259 OID 514492)
-- Name: tv_materialized_view_stamp; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tv_materialized_view_stamp (
    to_refresh boolean,
    last_refresh timestamp without time zone
);


ALTER TABLE public.tv_materialized_view_stamp OWNER TO postgres;

--
-- TOC entry 356 (class 1259 OID 511889)
-- Name: v_doctitle_agg; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_doctitle_agg AS
 SELECT row_number() OVER () AS pk,
    ddoctitle.idcollection,
    ddoctitle.iddoc,
    string_agg((ddoctitle.title)::text, '; '::text ORDER BY ddoctitle.titlelevel) AS titleagg
   FROM public.ddoctitle
  GROUP BY ddoctitle.idcollection, ddoctitle.iddoc;


ALTER TABLE public.v_doctitle_agg OWNER TO postgres;

--
-- TOC entry 361 (class 1259 OID 512516)
-- Name: v_rmca_merge_all_objects_vertical; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_rmca_merge_all_objects_vertical AS
 WITH a AS (
         SELECT v_rmca_merge_all_objects.fk_locality,
            v_rmca_merge_all_objects.idcollectionloc,
            v_rmca_merge_all_objects.idpt,
            v_rmca_merge_all_objects.fk_document,
            v_rmca_merge_all_objects.idcollection,
            v_rmca_merge_all_objects.iddoc,
            v_rmca_merge_all_objects.fk_litho,
            v_rmca_merge_all_objects.idstratum,
            v_rmca_merge_all_objects.fk_sample,
            v_rmca_merge_all_objects.idcollectionsample,
            v_rmca_merge_all_objects.idsample,
            v_rmca_merge_all_objects.coord_long,
            v_rmca_merge_all_objects.coord_lat
           FROM public.v_rmca_merge_all_objects
        )
 SELECT DISTINCT a.fk_locality AS main_pk,
    'locality'::text AS type,
    array_agg(DISTINCT a.fk_document) AS fks_documents,
    array_agg(DISTINCT a.fk_sample) AS fks_samples,
    NULL::integer[] AS fks_localities,
    a.coord_long,
    a.coord_lat,
    a.idpt AS idobject,
    a.idcollectionloc AS idcollection
   FROM a
  WHERE (a.fk_locality IS NOT NULL)
  GROUP BY a.fk_locality, a.coord_long, a.coord_lat, a.idpt, a.idcollectionloc
UNION
 SELECT DISTINCT a.fk_document AS main_pk,
    'document'::text AS type,
    NULL::integer[] AS fks_documents,
    array_agg(DISTINCT a.fk_sample) AS fks_samples,
    array_agg(DISTINCT a.fk_locality) AS fks_localities,
    a.coord_long,
    a.coord_lat,
    a.iddoc AS idobject,
    a.idcollection
   FROM a
  WHERE (a.fk_document IS NOT NULL)
  GROUP BY a.fk_document, a.coord_long, a.coord_lat, a.iddoc, a.idcollection
UNION
 SELECT DISTINCT a.fk_sample AS main_pk,
    'sample'::text AS type,
    array_agg(DISTINCT a.fk_document) AS fks_documents,
    NULL::integer[] AS fks_samples,
    array_agg(DISTINCT a.fk_locality) AS fks_localities,
    a.coord_long,
    a.coord_lat,
    a.idsample AS idobject,
    a.idcollectionsample AS idcollection
   FROM a
  WHERE (a.fk_sample IS NOT NULL)
  GROUP BY a.fk_sample, a.coord_long, a.coord_lat, a.idsample, a.idcollectionsample;


ALTER TABLE public.v_rmca_merge_all_objects_vertical OWNER TO postgres;

--
-- TOC entry 370 (class 1259 OID 512764)
-- Name: v_keyword_hierarchy_to_object; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_keyword_hierarchy_to_object AS
 SELECT DISTINCT c.main_pk,
    a.idcollection,
    a.id,
    a.keyword,
    a.keywordlevel,
    a.pk,
    b.path_word,
    b.level
   FROM ((public.dkeyword a
     JOIN public.rmca_get_keywords_hierarchy() b(pk, word, wordfather, parent_pk, path_pk, path_word, level) ON (((a.keyword)::text = (b.word)::text)))
     JOIN public.v_rmca_merge_all_objects_vertical c ON (((a.id = c.idobject) AND ((a.idcollection)::text = (c.idcollection)::text))))
UNION
 SELECT DISTINCT dloccenter.pk AS main_pk,
    dloclitho.idcollection,
    dloclitho.idpt AS id,
    lower((dloclitho.lithostratum)::text) AS keyword,
    (1)::smallint AS keywordlevel,
    NULL::smallint AS pk,
    (('/'::text || (dloclitho.lithostratum)::text) || '/'::text) AS path_word,
    1 AS level
   FROM (public.dloclitho
     JOIN public.dloccenter ON ((((dloclitho.idcollection)::text = (dloccenter.idcollection)::text) AND (dloclitho.idpt = dloccenter.idpt))))
UNION
 SELECT DISTINCT dloccenter.pk AS main_pk,
    dlocdrillingtype.idcollection,
    dlocdrillingtype.idpt AS id,
    lower((dlocdrillingtype.drillingtype)::text) AS keyword,
    (1)::smallint AS keywordlevel,
    NULL::smallint AS pk,
    (('/'::text || (dlocdrillingtype.drillingtype)::text) || '/'::text) AS path_word,
    1 AS level
   FROM (public.dlocdrillingtype
     JOIN public.dloccenter ON ((((dlocdrillingtype.idcollection)::text = (dloccenter.idcollection)::text) AND (dlocdrillingtype.idpt = dloccenter.idpt))));


ALTER TABLE public.v_keyword_hierarchy_to_object OWNER TO postgres;

--
-- TOC entry 371 (class 1259 OID 512805)
-- Name: v_keyword_hierarchy_to_object_list_parent; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_keyword_hierarchy_to_object_list_parent AS
 SELECT DISTINCT unnest(regexp_split_to_array((v_keyword_hierarchy_to_object.path_word)::text, '/'::text)) AS word
   FROM public.v_keyword_hierarchy_to_object
UNION
 SELECT DISTINCT dloclitho.lithostratum AS word
   FROM public.dloclitho;


ALTER TABLE public.v_keyword_hierarchy_to_object_list_parent OWNER TO postgres;

--
-- TOC entry 380 (class 1259 OID 522194)
-- Name: v_lkeywords_merge; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_lkeywords_merge AS
 WITH a AS (
         SELECT DISTINCT lkeywords.wordson
           FROM public.lkeywords
        ), b AS (
         SELECT DISTINCT lkeywords.wordfather
           FROM (public.lkeywords
             FULL JOIN a ON (((lkeywords.wordfather)::text = (a.wordson)::text)))
          WHERE (a.wordson IS NULL)
        )
 SELECT DISTINCT c.word
   FROM ( SELECT a.wordson AS word
           FROM a
        UNION
         SELECT b.wordfather
           FROM b) c
  ORDER BY c.word;


ALTER TABLE public.v_lkeywords_merge OWNER TO postgres;

--
-- TOC entry 354 (class 1259 OID 511864)
-- Name: v_search_all_data; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_search_all_data AS
 SELECT dcr.idcontributor,
    dcr.people,
    dcr.institut,
    dc.idcontribution,
    dc.datetype,
    dc.date AS contribution_date,
    ds.idsample,
    ds.idcollection AS sample_id_collection,
    ds.sampledescription,
    ds.holotype,
    ds.paratype,
    lm.fmname,
    lm.fmparent,
    lm.mname,
    lm.mparent,
    dlocc.coord_lat,
    dlocc.coord_long,
    dlocc.date AS loc_date,
    dlocc.place,
    dlocc.geodescription,
    dlocc.positiondescription,
    dlocsd.descript,
    dlocsd.idcollection AS statum_id_collection,
    dlocsd.idpt,
    dlocsd.idstratum,
    dlocl.descriptionstratum,
    dlocl.bottomstratum,
    dlocl.topstratum,
    dlocl.lithostratum,
    ddocut.title,
    ddocu.idcollection,
    ddocu.iddoc,
    ddocu.medium,
    ddocu.docinfo,
    ddocu.doccartotype
   FROM ((((((((((((((((public.dcontribution dc
     LEFT JOIN public.dlinkcontribute dlc ON ((dc.idcontribution = dlc.idcontribution)))
     LEFT JOIN public.dcontributor dcr ON ((dcr.idcontributor = dlc.idcontributor)))
     LEFT JOIN public.dlinkcontsam dlcs ON ((dc.idcontribution = dlcs.idcontribution)))
     LEFT JOIN public.dsample ds ON ((((ds.idcollection)::text = (dlcs.idcollection)::text) AND (ds.idsample = dlcs.id))))
     LEFT JOIN public.dsamminerals dsm ON ((((dsm.idcollection)::text = (ds.idcollection)::text) AND (dsm.idsample = ds.idsample))))
     LEFT JOIN public.lminerals lm ON ((dsm.idmineral = lm.idmineral)))
     LEFT JOIN public.dlinkcontloc dlcloc ON ((dlcloc.idcontribution = dc.idcontribution)))
     LEFT JOIN public.dloccenter dlocc ON ((((dlcloc.idcollection)::text = (dlocc.idcollection)::text) AND (dlcloc.id = dlocc.idpt))))
     LEFT JOIN public.dloclitho dlocl ON ((((dlocl.idcollection)::text = (dlocc.idcollection)::text) AND (dlocl.idpt = dlocc.idpt))))
     LEFT JOIN public.dlinklocsam dllocs ON ((((dlocl.idcollection)::text = (dllocs.idcollectionloc)::text) AND (dlocl.idpt = dllocs.idpt) AND (dlocl.idstratum = dllocs.idstratum) AND ((ds.idcollection)::text = (dllocs.idcollecsample)::text) AND (ds.idsample = dllocs.idsample))))
     LEFT JOIN public.dlocstratumdesc dlocsd ON ((((dlocl.idcollection)::text = (dlocsd.idcollection)::text) AND (dlocl.idpt = dlocsd.idpt) AND (dlocl.idstratum = dlocsd.idstratum))))
     LEFT JOIN public.dlinkcontdoc dlcd ON ((dc.idcontribution = dlcd.idcontribution)))
     LEFT JOIN public.ddocument ddocu ON ((((dlcd.idcollection)::text = (ddocu.idcollection)::text) AND (dlcd.id = ddocu.iddoc))))
     LEFT JOIN public.ddoctitle ddocut ON ((((ddocu.idcollection)::text = (ddocut.idcollection)::text) AND (ddocu.iddoc = ddocut.iddoc))))
     LEFT JOIN public.dlinkdocloc dldocloc ON ((((dldocloc.idcollecdoc)::text = (ddocu.idcollection)::text) AND (dldocloc.iddoc = ddocu.iddoc) AND ((dldocloc.idcollecloc)::text = (dlocc.idcollection)::text) AND (dldocloc.idpt = dlocc.idpt))))
     LEFT JOIN public.dlinkdocsam dldocs ON ((((dldocs.idcollectiondoc)::text = (ddocu.idcollection)::text) AND (dldocs.iddoc = ddocu.iddoc) AND ((dldocs.idcollectionsample)::text = (ds.idcollection)::text) AND (dldocs.idsample = ds.idsample))));


ALTER TABLE public.v_search_all_data OWNER TO postgres;

--
-- TOC entry 353 (class 1259 OID 511859)
-- Name: v_search_alldata; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_search_alldata AS
 SELECT dcr.idcontributor,
    dcr.people,
    dcr.institut,
    dc.idcontribution,
    dc.datetype,
    dc.date AS contribution_date,
    ds.pk,
    ds.idcollection AS sample_idcollection,
    ds.idsample,
    ds.fieldnum,
    ds.museumnum,
    ds.museumlocation,
    ds.boxnumber,
    ds.sampledescription,
    ds.weight,
    ds.quantity,
    ds.size,
    ds.dimentioncode,
    (ds.quality)::integer AS quality,
    ds.slimplate,
    ds.chemicalanalysis,
    (
        CASE
            WHEN (ds.holotype = true) THEN 'H'::text
            ELSE ''::text
        END ||
        CASE
            WHEN (ds.paratype = true) THEN 'P'::text
            ELSE ''::text
        END) AS type,
    ds.holotype,
    ds.paratype,
    ds.radioactivity,
    ds.loaninformation,
    ds.securitylevel,
    ds.varioussampleinfo,
    lm.mname,
    lm.mformula,
    lm.fmparent,
    lm.mparent,
    lm.fmname,
    to_char(shm1.weightsample, '999.99'::text) AS weightsample,
    shm1.observationhm,
    shm2.mineral AS mineral2,
    (shm2.minnum)::character varying AS minnum,
    to_char(sg.weighttot, '999.99'::text) AS weighttot,
    to_char(sms.weight, '999.99'::text) AS mweight,
    (to_char(sms.mesure1, '9999.999'::text))::double precision AS mesure1,
    dlocc.coord_lat,
    dlocc.coord_long,
    dlocc.date AS loc_date,
    dlocc.place,
    dlocc.geodescription,
    dlocc.positiondescription,
    dlocsd.descript,
    dlocsd.idcollection AS statum_idcollection,
    dlocsd.idpt,
    dlocsd.idstratum,
    dlocl.descriptionstratum,
    dlocl.bottomstratum,
    dlocl.topstratum,
    dlocl.lithostratum,
    ddocut.title,
    ddocu.idcollection AS doc_idcollection,
    ddocu.iddoc,
    ddocu.medium,
    ddocu.docinfo,
    ddocu.doccartotype
   FROM ((((((((((((((((((((public.dcontribution dc
     LEFT JOIN public.dlinkcontribute dlc ON ((dc.idcontribution = dlc.idcontribution)))
     LEFT JOIN public.dcontributor dcr ON ((dcr.idcontributor = dlc.idcontributor)))
     LEFT JOIN public.dlinkcontsam dlcs ON ((dc.idcontribution = dlcs.idcontribution)))
     LEFT JOIN public.dsample ds ON ((((ds.idcollection)::text = (dlcs.idcollection)::text) AND (ds.idsample = dlcs.id))))
     LEFT JOIN public.dsamminerals dsm ON ((((dsm.idcollection)::text = (ds.idcollection)::text) AND (dsm.idsample = ds.idsample))))
     LEFT JOIN public.lminerals lm ON ((dsm.idmineral = lm.idmineral)))
     LEFT JOIN public.dsamheavymin shm1 ON ((((shm1.idcollection)::text = (ds.idcollection)::text) AND (shm1.idsample = ds.idsample))))
     LEFT JOIN public.dsamheavymin2 shm2 ON ((((shm2.idcollection)::text = (ds.idcollection)::text) AND (shm2.idsample = ds.idsample))))
     LEFT JOIN public.dsamgranulo sg ON ((((sg.idcollection)::text = (ds.idcollection)::text) AND (sg.idsample = ds.idsample))))
     LEFT JOIN public.dsammagsusc sms ON ((((sms.idcollection)::text = (ds.idcollection)::text) AND (sms.idsample = ds.idsample))))
     LEFT JOIN public.dlinkcontloc dlcloc ON ((dlcloc.idcontribution = dc.idcontribution)))
     LEFT JOIN public.dloccenter dlocc ON ((((dlcloc.idcollection)::text = (dlocc.idcollection)::text) AND (dlcloc.id = dlocc.idpt))))
     LEFT JOIN public.dloclitho dlocl ON ((((dlocl.idcollection)::text = (dlocc.idcollection)::text) AND (dlocl.idpt = dlocc.idpt))))
     LEFT JOIN public.dlinklocsam dllocs ON ((((dlocl.idcollection)::text = (dllocs.idcollectionloc)::text) AND (dlocl.idpt = dllocs.idpt) AND (dlocl.idstratum = dllocs.idstratum) AND ((ds.idcollection)::text = (dllocs.idcollecsample)::text) AND (ds.idsample = dllocs.idsample))))
     LEFT JOIN public.dlocstratumdesc dlocsd ON ((((dlocl.idcollection)::text = (dlocsd.idcollection)::text) AND (dlocl.idpt = dlocsd.idpt) AND (dlocl.idstratum = dlocsd.idstratum))))
     LEFT JOIN public.dlinkcontdoc dlcd ON ((dc.idcontribution = dlcd.idcontribution)))
     LEFT JOIN public.ddocument ddocu ON ((((dlcd.idcollection)::text = (ddocu.idcollection)::text) AND (dlcd.id = ddocu.iddoc))))
     LEFT JOIN public.ddoctitle ddocut ON ((((ddocu.idcollection)::text = (ddocut.idcollection)::text) AND (ddocu.iddoc = ddocut.iddoc))))
     LEFT JOIN public.dlinkdocloc dldocloc ON ((((dldocloc.idcollecdoc)::text = (ddocu.idcollection)::text) AND (dldocloc.iddoc = ddocu.iddoc) AND ((dldocloc.idcollecloc)::text = (dlocc.idcollection)::text) AND (dldocloc.idpt = dlocc.idpt))))
     LEFT JOIN public.dlinkdocsam dldocs ON ((((dldocs.idcollectiondoc)::text = (ddocu.idcollection)::text) AND (dldocs.iddoc = ddocu.iddoc) AND ((dldocs.idcollectionsample)::text = (ds.idcollection)::text) AND (dldocs.idsample = ds.idsample))));


ALTER TABLE public.v_search_alldata OWNER TO postgres;

--
-- TOC entry 3909 (class 2604 OID 486351)
-- Name: codecollection pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.codecollection ALTER COLUMN pk SET DEFAULT nextval('public.codecollection_pk_seq'::regclass);


--
-- TOC entry 3910 (class 2604 OID 486371)
-- Name: dcontributor pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dcontributor ALTER COLUMN pk SET DEFAULT nextval('public.dcontributor_pk_seq'::regclass);


--
-- TOC entry 3913 (class 2604 OID 486391)
-- Name: ddocarchive pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocarchive ALTER COLUMN pk SET DEFAULT nextval('public.ddocarchive_pk_seq'::regclass);


--
-- TOC entry 3916 (class 2604 OID 486432)
-- Name: ddocsatellite pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite ALTER COLUMN pk SET DEFAULT nextval('public.ddocsatellite_pk_seq'::regclass);


--
-- TOC entry 3918 (class 2604 OID 486451)
-- Name: ddoctitle pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoctitle ALTER COLUMN pk SET DEFAULT nextval('public.ddoctitle_pk_seq'::regclass);


--
-- TOC entry 3924 (class 2604 OID 486473)
-- Name: dgestion pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestion ALTER COLUMN pk SET DEFAULT nextval('public.dgestion_pk_seq'::regclass);


--
-- TOC entry 3926 (class 2604 OID 486493)
-- Name: dinstitute pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dinstitute ALTER COLUMN pk SET DEFAULT nextval('public.dinstitute_pk_seq'::regclass);


--
-- TOC entry 3928 (class 2604 OID 486512)
-- Name: dlinkcontdoc pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontdoc ALTER COLUMN pk SET DEFAULT nextval('public.dlinkcontdoc_pk_seq'::regclass);


--
-- TOC entry 3929 (class 2604 OID 511884)
-- Name: dlinkcontloc pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontloc ALTER COLUMN pk SET DEFAULT nextval('public.dlinkcontloc_pk_seq'::regclass);


--
-- TOC entry 3933 (class 2604 OID 486534)
-- Name: dlinkcontribute pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontribute ALTER COLUMN pk SET DEFAULT nextval('public.dlinkcontribute_pk_seq'::regclass);


--
-- TOC entry 3934 (class 2604 OID 486543)
-- Name: dlinkcontsam pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontsam ALTER COLUMN pk SET DEFAULT nextval('public.dlinkcontsam_pk_seq'::regclass);


--
-- TOC entry 3935 (class 2604 OID 486562)
-- Name: dlinkdocsam pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocsam ALTER COLUMN pk SET DEFAULT nextval('public.dlinkdocsam_pk_seq'::regclass);


--
-- TOC entry 3936 (class 2604 OID 486589)
-- Name: dlinkgestsam pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestsam ALTER COLUMN pk SET DEFAULT nextval('public.dlinkgestsam_pk_seq'::regclass);


--
-- TOC entry 3937 (class 2604 OID 486598)
-- Name: dlinklocsam pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinklocsam ALTER COLUMN pk SET DEFAULT nextval('public.dlinklocsam_pk_seq'::regclass);


--
-- TOC entry 3939 (class 2604 OID 486608)
-- Name: dloccarto pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccarto ALTER COLUMN pk SET DEFAULT nextval('public.dloccarto_pk_seq'::regclass);


--
-- TOC entry 3943 (class 2604 OID 486644)
-- Name: dlocdrillingtype pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrillingtype ALTER COLUMN pk SET DEFAULT nextval('public.dlocdrillingtype_pk_seq'::regclass);


--
-- TOC entry 3945 (class 2604 OID 486654)
-- Name: dlochydro pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlochydro ALTER COLUMN pk SET DEFAULT nextval('public.dlochydro_pk_seq'::regclass);


--
-- TOC entry 3946 (class 2604 OID 486663)
-- Name: dloclitho pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloclitho ALTER COLUMN pk SET DEFAULT nextval('public.dloclitho_pk_seq'::regclass);


--
-- TOC entry 3947 (class 2604 OID 486702)
-- Name: dlocstructure pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstructure ALTER COLUMN pk SET DEFAULT nextval('public.dlocstructure_pk_seq'::regclass);


--
-- TOC entry 3948 (class 2604 OID 486711)
-- Name: docplanvol pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.docplanvol ALTER COLUMN pk SET DEFAULT nextval('public.docplanvol_pk_seq'::regclass);


--
-- TOC entry 3950 (class 2604 OID 486730)
-- Name: dsamgranulo pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamgranulo ALTER COLUMN pk SET DEFAULT nextval('public.dsamgranulo_pk_seq'::regclass);


--
-- TOC entry 3951 (class 2604 OID 486739)
-- Name: dsamheavymin pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin ALTER COLUMN pk SET DEFAULT nextval('public.dsamheavymin_pk_seq'::regclass);


--
-- TOC entry 3952 (class 2604 OID 486748)
-- Name: dsamheavymin2 pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin2 ALTER COLUMN pk SET DEFAULT nextval('public.dsamheavymin2_pk_seq'::regclass);


--
-- TOC entry 3955 (class 2604 OID 486768)
-- Name: dsamminerals pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamminerals ALTER COLUMN pk SET DEFAULT nextval('public.dsamminerals_pk_seq'::regclass);


--
-- TOC entry 3957 (class 2604 OID 486804)
-- Name: lkeywords pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lkeywords ALTER COLUMN pk SET DEFAULT nextval('public.lkeywords_pk_seq'::regclass);


--
-- TOC entry 3958 (class 2604 OID 486813)
-- Name: lmapreftype pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lmapreftype ALTER COLUMN pk SET DEFAULT nextval('public.lmapreftype_pk_seq'::regclass);


--
-- TOC entry 3959 (class 2604 OID 486831)
-- Name: lminerals pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lminerals ALTER COLUMN pk SET DEFAULT nextval('public.lminerals_pk_seq'::regclass);


--
-- TOC entry 3960 (class 2604 OID 486840)
-- Name: lprecision pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lprecision ALTER COLUMN pk SET DEFAULT nextval('public.lprecision_pk_seq'::regclass);


--
-- TOC entry 3962 (class 2604 OID 486850)
-- Name: ltitlelevel pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ltitlelevel ALTER COLUMN pk SET DEFAULT nextval('public.ltitlelevel_pk_seq'::regclass);


--
-- TOC entry 3968 (class 2604 OID 514538)
-- Name: t_data_log pk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_data_log ALTER COLUMN pk SET DEFAULT nextval('public.t_data_log_pk_seq'::regclass);


--
-- TOC entry 4008 (class 2606 OID 486857)
-- Name: ddocsatellite DDocSatellite_IDDoc_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite
    ADD CONSTRAINT "DDocSatellite_IDDoc_key" UNIQUE (iddoc);


--
-- TOC entry 4129 (class 2606 OID 486859)
-- Name: dsamarays DSamARays_IDCollection_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamarays
    ADD CONSTRAINT "DSamARays_IDCollection_key" UNIQUE (idcollection);


--
-- TOC entry 3973 (class 2606 OID 486861)
-- Name: codecollection codecollection_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.codecollection
    ADD CONSTRAINT codecollection_pkey PRIMARY KEY (pk);


--
-- TOC entry 3975 (class 2606 OID 486863)
-- Name: codecollection codecollection_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.codecollection
    ADD CONSTRAINT codecollection_unique UNIQUE (codecollection);


--
-- TOC entry 4216 (class 2606 OID 514544)
-- Name: t_data_log data_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_data_log
    ADD CONSTRAINT data_log_pkey PRIMARY KEY (pk);


--
-- TOC entry 3977 (class 2606 OID 592802)
-- Name: dcontribution dcontribution_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dcontribution
    ADD CONSTRAINT dcontribution_pkey PRIMARY KEY (pk);


--
-- TOC entry 3979 (class 2606 OID 486867)
-- Name: dcontribution dcontribution_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dcontribution
    ADD CONSTRAINT dcontribution_unique UNIQUE (idcontribution);


--
-- TOC entry 3981 (class 2606 OID 486869)
-- Name: dcontributor dcontributor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dcontributor
    ADD CONSTRAINT dcontributor_pkey PRIMARY KEY (pk);


--
-- TOC entry 3983 (class 2606 OID 486871)
-- Name: dcontributor dcontributor_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dcontributor
    ADD CONSTRAINT dcontributor_unique UNIQUE (idcontributor);


--
-- TOC entry 3986 (class 2606 OID 486873)
-- Name: ddocaerphoto ddocaerphoto_pid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocaerphoto
    ADD CONSTRAINT ddocaerphoto_pid_unique UNIQUE (pid);


--
-- TOC entry 3988 (class 2606 OID 486875)
-- Name: ddocaerphoto ddocaerphoto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocaerphoto
    ADD CONSTRAINT ddocaerphoto_pkey PRIMARY KEY (pk);


--
-- TOC entry 3990 (class 2606 OID 486877)
-- Name: ddocaerphoto ddocaerphoto_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocaerphoto
    ADD CONSTRAINT ddocaerphoto_unique UNIQUE (idcollection, iddoc);


--
-- TOC entry 3992 (class 2606 OID 486879)
-- Name: ddocarchive ddocarchive_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocarchive
    ADD CONSTRAINT ddocarchive_pkey PRIMARY KEY (pk);


--
-- TOC entry 3996 (class 2606 OID 486881)
-- Name: ddocfilm ddocfilm_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocfilm
    ADD CONSTRAINT ddocfilm_pkey PRIMARY KEY (pk);


--
-- TOC entry 3998 (class 2606 OID 486883)
-- Name: ddocfilm ddocfilm_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocfilm
    ADD CONSTRAINT ddocfilm_unique UNIQUE (idcollection, iddoc, film);


--
-- TOC entry 4000 (class 2606 OID 486885)
-- Name: ddoclinks ddoclinks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoclinks
    ADD CONSTRAINT ddoclinks_pkey PRIMARY KEY (pk);


--
-- TOC entry 4002 (class 2606 OID 486887)
-- Name: ddoclinks ddoclinks_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoclinks
    ADD CONSTRAINT ddoclinks_unique UNIQUE (idcollection, iddoc, idcollection2, iddoc2);


--
-- TOC entry 4004 (class 2606 OID 486889)
-- Name: ddocmap ddocmap_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocmap
    ADD CONSTRAINT ddocmap_pkey PRIMARY KEY (pk);


--
-- TOC entry 4006 (class 2606 OID 486891)
-- Name: ddocmap ddocmap_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocmap
    ADD CONSTRAINT ddocmap_unique UNIQUE (iddoc, idcollection);


--
-- TOC entry 4010 (class 2606 OID 486893)
-- Name: ddocsatellite ddocsatellite_iddoc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite
    ADD CONSTRAINT ddocsatellite_iddoc_unique UNIQUE (iddoc);


--
-- TOC entry 4012 (class 2606 OID 486895)
-- Name: ddocsatellite ddocsatellite_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite
    ADD CONSTRAINT ddocsatellite_pkey PRIMARY KEY (pk);


--
-- TOC entry 4014 (class 2606 OID 486897)
-- Name: ddocsatellite ddocsatellite_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite
    ADD CONSTRAINT ddocsatellite_unique UNIQUE (idcollection, iddoc);


--
-- TOC entry 4016 (class 2606 OID 486899)
-- Name: ddocscale ddocscale_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocscale
    ADD CONSTRAINT ddocscale_pkey PRIMARY KEY (pk);


--
-- TOC entry 4018 (class 2606 OID 486901)
-- Name: ddocscale ddocscale_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocscale
    ADD CONSTRAINT ddocscale_unique UNIQUE (idcollection, iddoc, scale);


--
-- TOC entry 4020 (class 2606 OID 486903)
-- Name: ddoctitle ddoctitle_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoctitle
    ADD CONSTRAINT ddoctitle_pkey PRIMARY KEY (pk);


--
-- TOC entry 4022 (class 2606 OID 486905)
-- Name: ddoctitle ddoctitle_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoctitle
    ADD CONSTRAINT ddoctitle_unique UNIQUE (idcollection, iddoc, titlelevel);


--
-- TOC entry 4024 (class 2606 OID 512568)
-- Name: ddocument ddocument_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocument
    ADD CONSTRAINT ddocument_pkey PRIMARY KEY (pk);


--
-- TOC entry 4026 (class 2606 OID 486909)
-- Name: ddocument ddocument_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocument
    ADD CONSTRAINT ddocument_unique UNIQUE (idcollection, iddoc);


--
-- TOC entry 4028 (class 2606 OID 486913)
-- Name: dgestion dgestion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestion
    ADD CONSTRAINT dgestion_pkey PRIMARY KEY (pk);


--
-- TOC entry 4030 (class 2606 OID 486915)
-- Name: dgestion dgestion_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestion
    ADD CONSTRAINT dgestion_unique UNIQUE (idgestion);


--
-- TOC entry 4032 (class 2606 OID 486917)
-- Name: dgestionnaire dgestionnaire_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestionnaire
    ADD CONSTRAINT dgestionnaire_pkey PRIMARY KEY (pk);


--
-- TOC entry 4034 (class 2606 OID 486919)
-- Name: dgestionnaire dgestionnaire_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestionnaire
    ADD CONSTRAINT dgestionnaire_unique UNIQUE (idencodeur);


--
-- TOC entry 4036 (class 2606 OID 486921)
-- Name: dinstitute dinstitute_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dinstitute
    ADD CONSTRAINT dinstitute_pkey PRIMARY KEY (pk);


--
-- TOC entry 4038 (class 2606 OID 486923)
-- Name: dinstitute dinstitute_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dinstitute
    ADD CONSTRAINT dinstitute_unique UNIQUE (idinstitution);


--
-- TOC entry 4040 (class 2606 OID 486925)
-- Name: dkeyword dkeyword_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dkeyword
    ADD CONSTRAINT dkeyword_pkey PRIMARY KEY (pk);


--
-- TOC entry 4042 (class 2606 OID 486927)
-- Name: dkeyword dkeyword_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dkeyword
    ADD CONSTRAINT dkeyword_unique UNIQUE (id, idcollection, keyword);


--
-- TOC entry 4044 (class 2606 OID 486929)
-- Name: dlinkcontdoc dlinkcontdoc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontdoc
    ADD CONSTRAINT dlinkcontdoc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4046 (class 2606 OID 486931)
-- Name: dlinkcontdoc dlinkcontdoc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontdoc
    ADD CONSTRAINT dlinkcontdoc_unique UNIQUE (idcontribution, idcollection, id);


--
-- TOC entry 4048 (class 2606 OID 486933)
-- Name: dlinkcontloc dlinkcontloc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontloc
    ADD CONSTRAINT dlinkcontloc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4050 (class 2606 OID 486935)
-- Name: dlinkcontloc dlinkcontloc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontloc
    ADD CONSTRAINT dlinkcontloc_unique UNIQUE (idcontribution, idcollection, id);


--
-- TOC entry 4052 (class 2606 OID 486937)
-- Name: dlinkcontribute dlinkcontribute_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontribute
    ADD CONSTRAINT dlinkcontribute_pkey PRIMARY KEY (pk);


--
-- TOC entry 4054 (class 2606 OID 592673)
-- Name: dlinkcontribute dlinkcontribute_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontribute
    ADD CONSTRAINT dlinkcontribute_unique UNIQUE (idcontribution, contributorrole, idcontributor);


--
-- TOC entry 4057 (class 2606 OID 486941)
-- Name: dlinkcontsam dlinkcontsam_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontsam
    ADD CONSTRAINT dlinkcontsam_pkey PRIMARY KEY (pk);


--
-- TOC entry 4059 (class 2606 OID 486943)
-- Name: dlinkcontsam dlinkcontsam_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontsam
    ADD CONSTRAINT dlinkcontsam_unique UNIQUE (idcontribution, idcollection, id);


--
-- TOC entry 4061 (class 2606 OID 486945)
-- Name: dlinkdocloc dlinkdocloc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocloc
    ADD CONSTRAINT dlinkdocloc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4063 (class 2606 OID 486947)
-- Name: dlinkdocloc dlinkdocloc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocloc
    ADD CONSTRAINT dlinkdocloc_unique UNIQUE (idcollecloc, idpt, idcollecdoc, iddoc);


--
-- TOC entry 4065 (class 2606 OID 486949)
-- Name: dlinkdocsam dlinkdocsam_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocsam
    ADD CONSTRAINT dlinkdocsam_pkey PRIMARY KEY (pk);


--
-- TOC entry 4067 (class 2606 OID 486951)
-- Name: dlinkdocsam dlinkdocsam_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocsam
    ADD CONSTRAINT dlinkdocsam_unique UNIQUE (iddoc, idcollectiondoc, idsample, idcollectionsample);


--
-- TOC entry 4069 (class 2606 OID 486953)
-- Name: dlinkgestdoc dlinkgestdoc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestdoc
    ADD CONSTRAINT dlinkgestdoc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4071 (class 2606 OID 486955)
-- Name: dlinkgestdoc dlinkgestdoc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestdoc
    ADD CONSTRAINT dlinkgestdoc_unique UNIQUE (idgestion, idcollecdoc, iddoc);


--
-- TOC entry 4073 (class 2606 OID 486957)
-- Name: dlinkgestloc dlinkgestloc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestloc
    ADD CONSTRAINT dlinkgestloc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4075 (class 2606 OID 486959)
-- Name: dlinkgestloc dlinkgestloc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestloc
    ADD CONSTRAINT dlinkgestloc_unique UNIQUE (idgestion, idcollection, idpt);


--
-- TOC entry 4077 (class 2606 OID 486961)
-- Name: dlinkgestsam dlinkgestsam_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestsam
    ADD CONSTRAINT dlinkgestsam_pkey PRIMARY KEY (pk);


--
-- TOC entry 4079 (class 2606 OID 486963)
-- Name: dlinkgestsam dlinkgestsam_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestsam
    ADD CONSTRAINT dlinkgestsam_unique UNIQUE (idgestion, idcollection, idsam);


--
-- TOC entry 4081 (class 2606 OID 486965)
-- Name: dlinklocsam dlinklocsam_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinklocsam
    ADD CONSTRAINT dlinklocsam_pkey PRIMARY KEY (pk);


--
-- TOC entry 4083 (class 2606 OID 486967)
-- Name: dlinklocsam dlinklocsam_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinklocsam
    ADD CONSTRAINT dlinklocsam_unique UNIQUE (idcollectionloc, idpt, idstratum, idcollecsample, idsample);


--
-- TOC entry 4085 (class 2606 OID 486969)
-- Name: dloccarto dloccarto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccarto
    ADD CONSTRAINT dloccarto_pkey PRIMARY KEY (pk);


--
-- TOC entry 4087 (class 2606 OID 486971)
-- Name: dloccarto dloccarto_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccarto
    ADD CONSTRAINT dloccarto_unique UNIQUE (idcollection, idpt, cartoref);


--
-- TOC entry 4089 (class 2606 OID 512578)
-- Name: dloccenter dloccenter_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccenter
    ADD CONSTRAINT dloccenter_pkey PRIMARY KEY (pk);


--
-- TOC entry 4091 (class 2606 OID 486975)
-- Name: dloccenter dloccenter_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccenter
    ADD CONSTRAINT dloccenter_unique UNIQUE (idcollection, idpt);


--
-- TOC entry 4093 (class 2606 OID 486977)
-- Name: dlocdrilling dlocdrilling_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrilling
    ADD CONSTRAINT dlocdrilling_pkey PRIMARY KEY (pk);


--
-- TOC entry 4095 (class 2606 OID 486979)
-- Name: dlocdrilling dlocdrilling_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrilling
    ADD CONSTRAINT dlocdrilling_unique UNIQUE (idcollection, idpt);


--
-- TOC entry 4097 (class 2606 OID 486981)
-- Name: dlocdrillingtype dlocdrillingtype_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrillingtype
    ADD CONSTRAINT dlocdrillingtype_pkey PRIMARY KEY (pk);


--
-- TOC entry 4099 (class 2606 OID 486983)
-- Name: dlocdrillingtype dlocdrillingtype_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrillingtype
    ADD CONSTRAINT dlocdrillingtype_unique UNIQUE (idcollection, idpt, drillingtype);


--
-- TOC entry 4101 (class 2606 OID 486985)
-- Name: dlochydro dlochydro_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlochydro
    ADD CONSTRAINT dlochydro_pkey PRIMARY KEY (pk);


--
-- TOC entry 4103 (class 2606 OID 486987)
-- Name: dlochydro dlochydro_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlochydro
    ADD CONSTRAINT dlochydro_unique UNIQUE (idcollection, idpt, hydroinfo, hydroname);


--
-- TOC entry 4105 (class 2606 OID 486989)
-- Name: dloclitho dloclitho_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloclitho
    ADD CONSTRAINT dloclitho_pkey PRIMARY KEY (pk);


--
-- TOC entry 4107 (class 2606 OID 486991)
-- Name: dloclitho dloclitho_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloclitho
    ADD CONSTRAINT dloclitho_unique UNIQUE (idcollection, idpt, idstratum);


--
-- TOC entry 4109 (class 2606 OID 486993)
-- Name: dlocpolygon dlocpolygon_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocpolygon
    ADD CONSTRAINT dlocpolygon_pkey PRIMARY KEY (pk);


--
-- TOC entry 4111 (class 2606 OID 486995)
-- Name: dlocpolygon dlocpolygon_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocpolygon
    ADD CONSTRAINT dlocpolygon_unique UNIQUE (idcollection, idpt, polyarea, polyname);


--
-- TOC entry 4113 (class 2606 OID 486997)
-- Name: dlocquadril dlocquadril_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocquadril
    ADD CONSTRAINT dlocquadril_pkey PRIMARY KEY (pk);


--
-- TOC entry 4115 (class 2606 OID 486999)
-- Name: dlocquadril dlocquadril_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocquadril
    ADD CONSTRAINT dlocquadril_unique UNIQUE (idcollection, idpt);


--
-- TOC entry 4117 (class 2606 OID 487001)
-- Name: dlocstratumdesc dlocstatumdesc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstratumdesc
    ADD CONSTRAINT dlocstatumdesc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4119 (class 2606 OID 487003)
-- Name: dlocstratumdesc dlocstatumdesc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstratumdesc
    ADD CONSTRAINT dlocstatumdesc_unique UNIQUE (idcollection, idpt, idstratum, descript);


--
-- TOC entry 4121 (class 2606 OID 487005)
-- Name: dlocstructure dlocstructure_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstructure
    ADD CONSTRAINT dlocstructure_pkey PRIMARY KEY (pk);


--
-- TOC entry 4123 (class 2606 OID 487007)
-- Name: dlocstructure dlocstructure_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstructure
    ADD CONSTRAINT dlocstructure_unique UNIQUE (idcollection, idpt, idstratum, nummesure);


--
-- TOC entry 4125 (class 2606 OID 487009)
-- Name: docplanvol docplanvol_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.docplanvol
    ADD CONSTRAINT docplanvol_pkey PRIMARY KEY (pk);


--
-- TOC entry 4127 (class 2606 OID 487011)
-- Name: docplanvol docplanvol_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.docplanvol
    ADD CONSTRAINT docplanvol_unique UNIQUE (fid);


--
-- TOC entry 4131 (class 2606 OID 487015)
-- Name: dsamarays dsamarays_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamarays
    ADD CONSTRAINT dsamarays_pkey PRIMARY KEY (pk);


--
-- TOC entry 4133 (class 2606 OID 487017)
-- Name: dsamarays dsamarays_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamarays
    ADD CONSTRAINT dsamarays_unique UNIQUE (idsample, idcollection);


--
-- TOC entry 4135 (class 2606 OID 487019)
-- Name: dsamgranulo dsamgranulo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamgranulo
    ADD CONSTRAINT dsamgranulo_pkey PRIMARY KEY (pk);


--
-- TOC entry 4137 (class 2606 OID 487021)
-- Name: dsamgranulo dsamgranulo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamgranulo
    ADD CONSTRAINT dsamgranulo_unique UNIQUE (idcollection, idsample);


--
-- TOC entry 4143 (class 2606 OID 487023)
-- Name: dsamheavymin2 dsamheavymin2_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin2
    ADD CONSTRAINT dsamheavymin2_pkey PRIMARY KEY (pk);


--
-- TOC entry 4145 (class 2606 OID 487025)
-- Name: dsamheavymin2 dsamheavymin2_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin2
    ADD CONSTRAINT dsamheavymin2_unique UNIQUE (idcollection, idsample, mineral);


--
-- TOC entry 4139 (class 2606 OID 487027)
-- Name: dsamheavymin dsamheavymin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin
    ADD CONSTRAINT dsamheavymin_pkey PRIMARY KEY (pk);


--
-- TOC entry 4141 (class 2606 OID 487029)
-- Name: dsamheavymin dsamheavymin_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin
    ADD CONSTRAINT dsamheavymin_unique UNIQUE (idcollection, idsample);


--
-- TOC entry 4147 (class 2606 OID 511883)
-- Name: dsammagsusc dsammagsusc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsammagsusc
    ADD CONSTRAINT dsammagsusc_pkey PRIMARY KEY (pk);


--
-- TOC entry 4149 (class 2606 OID 487033)
-- Name: dsammagsusc dsammagsusc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsammagsusc
    ADD CONSTRAINT dsammagsusc_unique UNIQUE (idcollection, idsample);


--
-- TOC entry 4151 (class 2606 OID 487035)
-- Name: dsamminerals dsamminerals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamminerals
    ADD CONSTRAINT dsamminerals_pkey PRIMARY KEY (pk);


--
-- TOC entry 4153 (class 2606 OID 487037)
-- Name: dsamminerals dsamminerals_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamminerals
    ADD CONSTRAINT dsamminerals_unique UNIQUE (idcollection, idsample, idmineral);


--
-- TOC entry 4155 (class 2606 OID 512574)
-- Name: dsample dsample_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsample
    ADD CONSTRAINT dsample_pkey PRIMARY KEY (pk);


--
-- TOC entry 4157 (class 2606 OID 487041)
-- Name: dsample dsample_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsample
    ADD CONSTRAINT dsample_unique UNIQUE (idcollection, idsample);


--
-- TOC entry 4159 (class 2606 OID 487043)
-- Name: dsamslimplate dsamslimplate_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamslimplate
    ADD CONSTRAINT dsamslimplate_pkey PRIMARY KEY (pk);


--
-- TOC entry 4161 (class 2606 OID 487045)
-- Name: dsamslimplate dsamslimplate_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamslimplate
    ADD CONSTRAINT dsamslimplate_unique UNIQUE (idcollection, idsample, numplate);


--
-- TOC entry 4195 (class 2606 OID 511768)
-- Name: duser duser_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.duser
    ADD CONSTRAINT duser_pkey PRIMARY KEY (id);


--
-- TOC entry 4197 (class 2606 OID 511595)
-- Name: duser duser_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.duser
    ADD CONSTRAINT duser_unique UNIQUE (id);


--
-- TOC entry 4179 (class 2606 OID 509382)
-- Name: lminerals fmnameunique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lminerals
    ADD CONSTRAINT fmnameunique UNIQUE (fmname);


--
-- TOC entry 4199 (class 2606 OID 511617)
-- Name: fos_group fos_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_group
    ADD CONSTRAINT fos_group_pkey PRIMARY KEY (id);


--
-- TOC entry 4201 (class 2606 OID 511650)
-- Name: fos_role fos_role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_role
    ADD CONSTRAINT fos_role_pkey PRIMARY KEY (id);


--
-- TOC entry 4203 (class 2606 OID 511652)
-- Name: fos_role fos_role_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_role
    ADD CONSTRAINT fos_role_unique UNIQUE (name);


--
-- TOC entry 4207 (class 2606 OID 511742)
-- Name: fos_user_collections fos_user_collections_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_collections
    ADD CONSTRAINT fos_user_collections_pkey PRIMARY KEY (pk);


--
-- TOC entry 4209 (class 2606 OID 511744)
-- Name: fos_user_collections fos_user_collections_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_collections
    ADD CONSTRAINT fos_user_collections_unique UNIQUE (user_id, collection_id);


--
-- TOC entry 4205 (class 2606 OID 511667)
-- Name: fos_user_role fos_user_role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_role
    ADD CONSTRAINT fos_user_role_pkey PRIMARY KEY (user_id, role_id);


--
-- TOC entry 4214 (class 2606 OID 512541)
-- Name: generic_keyword generic_keyword_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.generic_keyword
    ADD CONSTRAINT generic_keyword_pkey PRIMARY KEY (pk);


--
-- TOC entry 4163 (class 2606 OID 487047)
-- Name: larea larea_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.larea
    ADD CONSTRAINT larea_pkey PRIMARY KEY (pk);


--
-- TOC entry 4165 (class 2606 OID 487049)
-- Name: larea larea_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.larea
    ADD CONSTRAINT larea_unique UNIQUE (polyarea);


--
-- TOC entry 4167 (class 2606 OID 487051)
-- Name: lkeywords lkeywords_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lkeywords
    ADD CONSTRAINT lkeywords_pkey PRIMARY KEY (pk);


--
-- TOC entry 4171 (class 2606 OID 487053)
-- Name: lmapreftype lmapreftype_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lmapreftype
    ADD CONSTRAINT lmapreftype_pkey PRIMARY KEY (pk);


--
-- TOC entry 4173 (class 2606 OID 487055)
-- Name: lmapreftype lmapreftype_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lmapreftype
    ADD CONSTRAINT lmapreftype_unique UNIQUE (reftype);


--
-- TOC entry 4175 (class 2606 OID 511870)
-- Name: lmedium lmedium_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lmedium
    ADD CONSTRAINT lmedium_pkey PRIMARY KEY (medium);


--
-- TOC entry 4177 (class 2606 OID 487059)
-- Name: lmedium lmedium_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lmedium
    ADD CONSTRAINT lmedium_unique UNIQUE (medium);


--
-- TOC entry 4181 (class 2606 OID 487061)
-- Name: lminerals lminerals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lminerals
    ADD CONSTRAINT lminerals_pkey PRIMARY KEY (pk);


--
-- TOC entry 4183 (class 2606 OID 487063)
-- Name: lminerals lminerals_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lminerals
    ADD CONSTRAINT lminerals_unique UNIQUE (idmineral);


--
-- TOC entry 4187 (class 2606 OID 511830)
-- Name: lprecision lprecision_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lprecision
    ADD CONSTRAINT lprecision_pkey PRIMARY KEY (idprecision);


--
-- TOC entry 4189 (class 2606 OID 511832)
-- Name: lprecision lprecision_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lprecision
    ADD CONSTRAINT lprecision_unique UNIQUE (idprecision);


--
-- TOC entry 4191 (class 2606 OID 487069)
-- Name: ltitlelevel ltitlelevel_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ltitlelevel
    ADD CONSTRAINT ltitlelevel_pkey PRIMARY KEY (pk);


--
-- TOC entry 4193 (class 2606 OID 487071)
-- Name: ltitlelevel ltitlelevel_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ltitlelevel
    ADD CONSTRAINT ltitlelevel_unique UNIQUE (titlelevel);


--
-- TOC entry 4185 (class 2606 OID 509380)
-- Name: lminerals mnameunique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lminerals
    ADD CONSTRAINT mnameunique UNIQUE (mname);


--
-- TOC entry 3994 (class 2606 OID 487073)
-- Name: ddocarchive pk_ddoc_archives; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocarchive
    ADD CONSTRAINT pk_ddoc_archives UNIQUE (idcollection, iddoc);


--
-- TOC entry 4211 (class 2606 OID 512592)
-- Name: template_data pk_template_data; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.template_data
    ADD CONSTRAINT pk_template_data PRIMARY KEY (pk);


--
-- TOC entry 4169 (class 2606 OID 487075)
-- Name: lkeywords unique_keywords; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lkeywords
    ADD CONSTRAINT unique_keywords UNIQUE (wordson, wordfather);


--
-- TOC entry 4212 (class 1259 OID 512598)
-- Name: fki_fk_generic_keyword_to_template; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fki_fk_generic_keyword_to_template ON public.generic_keyword USING btree (fk_shared_pk);


--
-- TOC entry 4055 (class 1259 OID 592791)
-- Name: idcontribution; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idcontribution ON public.dlinkcontribute USING btree (idcontribution);


--
-- TOC entry 3984 (class 1259 OID 592679)
-- Name: idx_unique_contributor; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX idx_unique_contributor ON public.dcontributor USING btree ((COALESCE(people, ''::character varying)), (COALESCE(peoplefonction, ''::character varying)), (COALESCE(peopletitre, ''::character varying)), (COALESCE(peoplestatut, ''::character varying)), (COALESCE(institut, ''::character varying)));


--
-- TOC entry 4405 (class 2618 OID 512602)
-- Name: v_rmca_main_objects_description _RETURN; Type: RULE; Schema: public; Owner: postgres
--

CREATE OR REPLACE VIEW public.v_rmca_main_objects_description AS
 SELECT DISTINCT dsample.pk,
    'sample'::text AS main_type,
    'fieldnum'::character varying AS col_1_name,
    dsample.fieldnum AS col_1_value,
    'museumnum'::character varying AS col_2_name,
    dsample.museumnum AS col_2_value,
    'museumlocation'::character varying AS col_3_name,
    dsample.museumlocation AS col_3_value,
    'minerals'::character varying AS col_4_name,
    string_agg((lminerals.fmname)::text, '; '::text ORDER BY (lminerals.fmname)::text) AS col_4_value
   FROM ((public.dsample
     LEFT JOIN public.dsamminerals ON ((((dsample.idcollection)::text = (dsamminerals.idcollection)::text) AND (dsample.idsample = dsamminerals.idsample))))
     LEFT JOIN public.lminerals ON ((dsamminerals.idmineral = lminerals.idmineral)))
  GROUP BY dsample.pk
UNION
 SELECT DISTINCT dloccenter.pk,
    'georef'::text AS main_type,
    'country'::character varying AS col_1_name,
    dloccenter.country AS col_1_value,
    'place'::character varying AS col_2_name,
    dloccenter.place AS col_2_value,
    'lithology'::character varying AS col_3_name,
    string_agg(DISTINCT (dloclitho.lithostratum)::text, '; '::text ORDER BY (dloclitho.lithostratum)::text) AS col_3_value,
    'drilling'::character varying AS col_4_name,
    string_agg(DISTINCT (dlocdrilling.drilling)::text, '; '::text ORDER BY (dlocdrilling.drilling)::text) AS col_4_value
   FROM ((public.dloccenter
     LEFT JOIN public.dloclitho ON ((((dloccenter.idcollection)::text = (dloclitho.idcollection)::text) AND (dloccenter.idpt = dloclitho.idpt))))
     LEFT JOIN public.dlocdrilling ON ((((dloccenter.idcollection)::text = (dlocdrilling.idcollection)::text) AND (dloccenter.idpt = dlocdrilling.idpt))))
  GROUP BY dloccenter.pk
UNION
 SELECT DISTINCT ddocument.pk,
        CASE
            WHEN (ddocmap.pk IS NOT NULL) THEN 'document - map'::text
            WHEN (ddocaerphoto.pk IS NOT NULL) THEN 'document - aerial photo'::text
            WHEN (ddocsatellite.pk IS NOT NULL) THEN 'document - satellite image'::text
            WHEN (ddocfilm.pk IS NOT NULL) THEN 'document - film'::text
            ELSE 'document'::text
        END AS main_type,
    'numarchive'::text AS col_1_name,
    ddocument.numarchive AS col_1_value,
    'title'::text AS col_2_name,
    v_doctitle_agg.titleagg AS col_2_value,
    'scale'::text AS col_3_name,
    string_agg(DISTINCT ((ddocscale.scale)::character varying)::text, '; '::text ORDER BY ((ddocscale.scale)::character varying)::text) AS col_3_value,
    NULL::character varying AS col_4_name,
    NULL::character varying AS col_4_value
   FROM ((((((public.ddocument
     LEFT JOIN public.v_doctitle_agg ON ((((ddocument.idcollection)::text = (v_doctitle_agg.idcollection)::text) AND (ddocument.iddoc = v_doctitle_agg.iddoc))))
     LEFT JOIN public.ddocscale ON ((((ddocument.idcollection)::text = (ddocscale.idcollection)::text) AND (ddocument.iddoc = ddocscale.iddoc))))
     LEFT JOIN public.ddocmap ON ((((ddocument.idcollection)::text = (ddocmap.idcollection)::text) AND (ddocument.iddoc = ddocmap.iddoc))))
     LEFT JOIN public.ddocsatellite ON ((((ddocument.idcollection)::text = (ddocsatellite.idcollection)::text) AND (ddocument.iddoc = ddocsatellite.iddoc))))
     LEFT JOIN public.ddocaerphoto ON ((((ddocument.idcollection)::text = (ddocaerphoto.idcollection)::text) AND (ddocument.iddoc = ddocaerphoto.iddoc))))
     LEFT JOIN public.ddocfilm ON ((((ddocument.idcollection)::text = (ddocfilm.idcollection)::text) AND (ddocument.iddoc = ddocfilm.iddoc))))
  GROUP BY ddocument.pk, v_doctitle_agg.titleagg, ddocmap.pk, ddocaerphoto.pk, ddocsatellite.pk, ddocfilm.pk;


--
-- TOC entry 4273 (class 2620 OID 536540)
-- Name: lkeywords trg_rmca_control_keyword; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_rmca_control_keyword BEFORE INSERT OR DELETE OR UPDATE ON public.lkeywords FOR EACH ROW WHEN ((pg_trigger_depth() = 0)) EXECUTE PROCEDURE public.fct_trg_rmca_control_keyword();


--
-- TOC entry 4274 (class 2620 OID 536541)
-- Name: lkeywords trg_rmca_control_keyword_upd; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_rmca_control_keyword_upd AFTER UPDATE ON public.lkeywords FOR EACH ROW WHEN ((pg_trigger_depth() = 0)) EXECUTE PROCEDURE public.fct_trg_rmca_control_upd_keyword();


--
-- TOC entry 4270 (class 2620 OID 593095)
-- Name: dloccenter trg_rmca_coordinates; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_rmca_coordinates BEFORE INSERT OR UPDATE ON public.dloccenter FOR EACH ROW EXECUTE PROCEDURE public.fct_trg_rmca_coordinates();


--
-- TOC entry 4271 (class 2620 OID 514554)
-- Name: dloccenter trg_trk_log_table_dloccenter; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_trk_log_table_dloccenter AFTER INSERT OR DELETE OR UPDATE ON public.dloccenter FOR EACH ROW EXECUTE PROCEDURE public.fct_trk_log_table();


--
-- TOC entry 4269 (class 2620 OID 514552)
-- Name: ddocument trg_trk_log_table_document; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_trk_log_table_document AFTER INSERT OR DELETE OR UPDATE ON public.ddocument FOR EACH ROW EXECUTE PROCEDURE public.fct_trk_log_table();


--
-- TOC entry 4272 (class 2620 OID 514553)
-- Name: dsample trg_trk_log_table_dsample; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_trk_log_table_dsample AFTER INSERT OR DELETE OR UPDATE ON public.dsample FOR EACH ROW EXECUTE PROCEDURE public.fct_trk_log_table();


--
-- TOC entry 4217 (class 2606 OID 487076)
-- Name: ddocaerphoto DDocAerPhoto_FID_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocaerphoto
    ADD CONSTRAINT "DDocAerPhoto_FID_fk" FOREIGN KEY (fid) REFERENCES public.docplanvol(fid);


--
-- TOC entry 4218 (class 2606 OID 487081)
-- Name: ddocaerphoto DDocAerPhoto_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocaerphoto
    ADD CONSTRAINT "DDocAerPhoto_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4219 (class 2606 OID 487086)
-- Name: ddocfilm DDocFilm_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocfilm
    ADD CONSTRAINT "DDocFilm_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4220 (class 2606 OID 487091)
-- Name: ddoclinks DDocLinks_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoclinks
    ADD CONSTRAINT "DDocLinks_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4221 (class 2606 OID 487096)
-- Name: ddocmap DDocMap_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocmap
    ADD CONSTRAINT "DDocMap_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4222 (class 2606 OID 487101)
-- Name: ddocsatellite DDocSatellite_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocsatellite
    ADD CONSTRAINT "DDocSatellite_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4223 (class 2606 OID 487106)
-- Name: ddocscale DDocScale_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocscale
    ADD CONSTRAINT "DDocScale_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4224 (class 2606 OID 487111)
-- Name: ddoctitle DDocTitle_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddoctitle
    ADD CONSTRAINT "DDocTitle_IDCollection_fk" FOREIGN KEY (idcollection, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4225 (class 2606 OID 487116)
-- Name: ddocument DDocument_Medium_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ddocument
    ADD CONSTRAINT "DDocument_Medium_fk" FOREIGN KEY (medium) REFERENCES public.lmedium(medium);


--
-- TOC entry 4226 (class 2606 OID 487121)
-- Name: dgestion DGestion_IDEncodeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgestion
    ADD CONSTRAINT "DGestion_IDEncodeur_fk" FOREIGN KEY (idencodeur) REFERENCES public.dgestionnaire(idencodeur);


--
-- TOC entry 4227 (class 2606 OID 487126)
-- Name: dkeyword DKeyword_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dkeyword
    ADD CONSTRAINT "DKeyword_IDCollection_fk" FOREIGN KEY (idcollection, id) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4228 (class 2606 OID 487131)
-- Name: dlinkcontdoc DLinkContDoc_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontdoc
    ADD CONSTRAINT "DLinkContDoc_IDCollection_fk" FOREIGN KEY (idcollection, id) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4229 (class 2606 OID 487136)
-- Name: dlinkcontdoc DLinkContDoc_IDContribution_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontdoc
    ADD CONSTRAINT "DLinkContDoc_IDContribution_fk" FOREIGN KEY (idcontribution) REFERENCES public.dcontribution(idcontribution);


--
-- TOC entry 4230 (class 2606 OID 487141)
-- Name: dlinkcontloc DLinkContLoc_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontloc
    ADD CONSTRAINT "DLinkContLoc_IDCollection_fk" FOREIGN KEY (idcollection, id) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4233 (class 2606 OID 487146)
-- Name: dlinkcontsam DLinkContSam_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontsam
    ADD CONSTRAINT "DLinkContSam_IDCollection_fk" FOREIGN KEY (idcollection, id) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4234 (class 2606 OID 487151)
-- Name: dlinkcontsam DLinkContSam_IDContribution_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontsam
    ADD CONSTRAINT "DLinkContSam_IDContribution_fk" FOREIGN KEY (idcontribution) REFERENCES public.dcontribution(idcontribution);


--
-- TOC entry 4231 (class 2606 OID 592808)
-- Name: dlinkcontribute DLinkContribute_IDContribution_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontribute
    ADD CONSTRAINT "DLinkContribute_IDContribution_fk" FOREIGN KEY (idcontribution) REFERENCES public.dcontribution(idcontribution);


--
-- TOC entry 4232 (class 2606 OID 487161)
-- Name: dlinkcontribute DLinkContribute_IDContributor_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkcontribute
    ADD CONSTRAINT "DLinkContribute_IDContributor_fk" FOREIGN KEY (idcontributor) REFERENCES public.dcontributor(idcontributor);


--
-- TOC entry 4235 (class 2606 OID 487166)
-- Name: dlinkdocloc DLinkDocLoc_IDCollecDoc_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocloc
    ADD CONSTRAINT "DLinkDocLoc_IDCollecDoc_fk" FOREIGN KEY (idcollecdoc, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4236 (class 2606 OID 487171)
-- Name: dlinkdocloc DLinkDocLoc_IDCollecLoc_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocloc
    ADD CONSTRAINT "DLinkDocLoc_IDCollecLoc_fk" FOREIGN KEY (idcollecloc, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4237 (class 2606 OID 487176)
-- Name: dlinkdocsam DLinkDocSam_IDCollectionDoc_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocsam
    ADD CONSTRAINT "DLinkDocSam_IDCollectionDoc_fk" FOREIGN KEY (idcollectiondoc, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4238 (class 2606 OID 487181)
-- Name: dlinkdocsam DLinkDocSam_IDCollectionSample_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkdocsam
    ADD CONSTRAINT "DLinkDocSam_IDCollectionSample_fk" FOREIGN KEY (idcollectionsample, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4239 (class 2606 OID 487186)
-- Name: dlinkgestdoc DLinkGestDoc_IDCollecDoc_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestdoc
    ADD CONSTRAINT "DLinkGestDoc_IDCollecDoc_fk" FOREIGN KEY (idcollecdoc, iddoc) REFERENCES public.ddocument(idcollection, iddoc);


--
-- TOC entry 4240 (class 2606 OID 487191)
-- Name: dlinkgestdoc DLinkGestDoc_IDGestion_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestdoc
    ADD CONSTRAINT "DLinkGestDoc_IDGestion_fk" FOREIGN KEY (idgestion) REFERENCES public.dgestion(idgestion);


--
-- TOC entry 4241 (class 2606 OID 487196)
-- Name: dlinkgestloc DLinkGestLoc_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestloc
    ADD CONSTRAINT "DLinkGestLoc_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4242 (class 2606 OID 487201)
-- Name: dlinkgestsam DLinkGestSam_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestsam
    ADD CONSTRAINT "DLinkGestSam_IDCollection_fk" FOREIGN KEY (idcollection, idsam) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4243 (class 2606 OID 487206)
-- Name: dlinkgestsam DLinkGestSam_IDGestion_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinkgestsam
    ADD CONSTRAINT "DLinkGestSam_IDGestion_fk" FOREIGN KEY (idgestion) REFERENCES public.dgestion(idgestion);


--
-- TOC entry 4244 (class 2606 OID 487211)
-- Name: dlinklocsam DLinkLocSam_IDCollecSample_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinklocsam
    ADD CONSTRAINT "DLinkLocSam_IDCollecSample_fk" FOREIGN KEY (idcollecsample, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4245 (class 2606 OID 487216)
-- Name: dlinklocsam DLinkLocSam_IDCollectionLoc_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlinklocsam
    ADD CONSTRAINT "DLinkLocSam_IDCollectionLoc_fk" FOREIGN KEY (idcollectionloc, idpt, idstratum) REFERENCES public.dloclitho(idcollection, idpt, idstratum);


--
-- TOC entry 4246 (class 2606 OID 487221)
-- Name: dloccarto DLocCarto_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccarto
    ADD CONSTRAINT "DLocCarto_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4247 (class 2606 OID 511833)
-- Name: dloccenter DLocCenter_IDPrecision_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloccenter
    ADD CONSTRAINT "DLocCenter_IDPrecision_fk" FOREIGN KEY (idprecision) REFERENCES public.lprecision(idprecision);


--
-- TOC entry 4249 (class 2606 OID 487231)
-- Name: dlocdrillingtype DLocDrillingType_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrillingtype
    ADD CONSTRAINT "DLocDrillingType_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4248 (class 2606 OID 487236)
-- Name: dlocdrilling DLocDrilling_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocdrilling
    ADD CONSTRAINT "DLocDrilling_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4250 (class 2606 OID 487241)
-- Name: dlochydro DLocHydro_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlochydro
    ADD CONSTRAINT "DLocHydro_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4251 (class 2606 OID 487246)
-- Name: dloclitho DLocLitho_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dloclitho
    ADD CONSTRAINT "DLocLitho_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4252 (class 2606 OID 487251)
-- Name: dlocpolygon DLocPolygon_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocpolygon
    ADD CONSTRAINT "DLocPolygon_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4253 (class 2606 OID 487256)
-- Name: dlocpolygon DLocPolygon_PolyArea_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocpolygon
    ADD CONSTRAINT "DLocPolygon_PolyArea_fk" FOREIGN KEY (polyarea) REFERENCES public.larea(polyarea);


--
-- TOC entry 4254 (class 2606 OID 487261)
-- Name: dlocquadril DLocQuadril_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocquadril
    ADD CONSTRAINT "DLocQuadril_IDCollection_fk" FOREIGN KEY (idcollection, idpt) REFERENCES public.dloccenter(idcollection, idpt);


--
-- TOC entry 4255 (class 2606 OID 487266)
-- Name: dlocstratumdesc DLocStatumDesc_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dlocstratumdesc
    ADD CONSTRAINT "DLocStatumDesc_IDCollection_fk" FOREIGN KEY (idcollection, idpt, idstratum) REFERENCES public.dloclitho(idcollection, idpt, idstratum);


--
-- TOC entry 4256 (class 2606 OID 487271)
-- Name: dsamarays DSamARays_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamarays
    ADD CONSTRAINT "DSamARays_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4257 (class 2606 OID 487276)
-- Name: dsamgranulo DSamGranulo_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamgranulo
    ADD CONSTRAINT "DSamGranulo_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4259 (class 2606 OID 487281)
-- Name: dsamheavymin2 DSamHeavyMin2_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin2
    ADD CONSTRAINT "DSamHeavyMin2_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsamheavymin(idcollection, idsample);


--
-- TOC entry 4258 (class 2606 OID 487286)
-- Name: dsamheavymin DSamHeavyMin_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamheavymin
    ADD CONSTRAINT "DSamHeavyMin_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4260 (class 2606 OID 487296)
-- Name: dsamminerals DSamMinerals_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamminerals
    ADD CONSTRAINT "DSamMinerals_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4261 (class 2606 OID 487301)
-- Name: dsamminerals DSamMinerals_IDMineral_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamminerals
    ADD CONSTRAINT "DSamMinerals_IDMineral_fk" FOREIGN KEY (idmineral) REFERENCES public.lminerals(idmineral);


--
-- TOC entry 4262 (class 2606 OID 487306)
-- Name: dsamslimplate DSamSlimPlate_IDCollection_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dsamslimplate
    ADD CONSTRAINT "DSamSlimPlate_IDCollection_fk" FOREIGN KEY (idcollection, idsample) REFERENCES public.dsample(idcollection, idsample);


--
-- TOC entry 4268 (class 2606 OID 514545)
-- Name: t_data_log fk_data_log_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_data_log
    ADD CONSTRAINT fk_data_log_user FOREIGN KEY (user_ref) REFERENCES public.duser(id);


--
-- TOC entry 4267 (class 2606 OID 512593)
-- Name: generic_keyword fk_generic_keyword_to_template; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.generic_keyword
    ADD CONSTRAINT fk_generic_keyword_to_template FOREIGN KEY (fk_shared_pk) REFERENCES public.template_data(pk) NOT VALID;


--
-- TOC entry 4265 (class 2606 OID 511745)
-- Name: fos_user_collections fos_user_collections_collection_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_collections
    ADD CONSTRAINT fos_user_collections_collection_id_fkey FOREIGN KEY (collection_id) REFERENCES public.codecollection(pk) ON UPDATE CASCADE;


--
-- TOC entry 4266 (class 2606 OID 511755)
-- Name: fos_user_collections fos_user_collections_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_collections
    ADD CONSTRAINT fos_user_collections_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.duser(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 4263 (class 2606 OID 511673)
-- Name: fos_user_role fos_user_role_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_role
    ADD CONSTRAINT fos_user_role_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.fos_role(id) ON UPDATE CASCADE;


--
-- TOC entry 4264 (class 2606 OID 511760)
-- Name: fos_user_role fos_user_role_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fos_user_role
    ADD CONSTRAINT fos_user_role_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.duser(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 4422 (class 0 OID 0)
-- Dependencies: 8
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2021-11-08 16:17:04

--
-- PostgreSQL database dump complete
--

