CREATE TABLE positions ("id" serial NOT NULL PRIMARY KEY ,"name" varchar ,"description" varchar ) WITH OIDS;
SELECT setval('positions_id_seq',1);
CREATE TABLE doc_types ("id" serial NOT NULL PRIMARY KEY ,"name" varchar ,"description" varchar ,"extension" varchar ) WITH OIDS;
SELECT setval('doc_types_id_seq',1);
CREATE TABLE task_team_access ("id" serial NOT NULL PRIMARY KEY ,"task_id" numeric DEFAULT 0,"team_id" numeric DEFAULT 0) WITH OIDS;
SELECT setval('task_team_access_id_seq',1);
CREATE TABLE teams_people ("id" serial NOT NULL PRIMARY KEY ,"team_id" numeric DEFAULT 0,"person_id" numeric DEFAULT 0) WITH OIDS;
SELECT setval('teams_people_id_seq',1);
CREATE TABLE project_team_access ("id" serial NOT NULL PRIMARY KEY ,"project_id" numeric DEFAULT 0,"team_id" numeric DEFAULT 0) WITH OIDS;
SELECT setval('project_team_access_id_seq',1);
CREATE TABLE project_people_access ("id" serial NOT NULL PRIMARY KEY ,"project_id" numeric DEFAULT 0,"person_id" numeric DEFAULT 0) WITH OIDS;
SELECT setval('project_people_access_id_seq',1);
CREATE TABLE teams ("id" serial NOT NULL PRIMARY KEY ,"name" varchar ,"description" varchar ) WITH OIDS;
SELECT setval('teams_id_seq',1);
CREATE TABLE charters ("id" serial NOT NULL PRIMARY KEY ,"project_id" numeric DEFAULT 0,"body" varchar ) WITH OIDS;
SELECT setval('charters_id_seq',1);
CREATE TABLE people ("id" serial NOT NULL PRIMARY KEY ,"user_id" numeric DEFAULT 0,"description" varchar ) WITH OIDS;
SELECT setval('people_id_seq',1);
CREATE TABLE task_types ("id" serial NOT NULL PRIMARY KEY ,"name" varchar ,"description" varchar ) WITH OIDS;
SELECT setval('task_types_id_seq',1);
CREATE TABLE task_people_access ("id" serial NOT NULL PRIMARY KEY ,"task_id" numeric DEFAULT 0,"person_id" numeric DEFAULT 0) WITH OIDS;
SELECT setval('task_people_access_id_seq',1);
CREATE TABLE projects ("id" serial NOT NULL PRIMARY KEY ,"name" varchar ,"champion_id" numeric DEFAULT 0,"sponsor_id" numeric DEFAULT 0,"leader_id" numeric DEFAULT 0,"start_date" date ,"complete_date" date ,"priority" numeric DEFAULT 0,"sub" varchar DEFAULT 'no'::character varying,"main_id" numeric DEFAULT 0,"edate" date ) WITH OIDS;
SELECT setval('projects_id_seq',1);
CREATE TABLE tasks ("id" serial NOT NULL PRIMARY KEY ,"project_id" numeric DEFAULT 0,"name" varchar ,"leader_id" numeric DEFAULT 0,"notes" varchar ,"priority" numeric DEFAULT 0,"start_time" timestamp ,"end_time" timestamp ,"main_id" numeric DEFAULT 0,"sub" varchar DEFAULT 'no'::character varying) WITH OIDS;
SELECT setval('tasks_id_seq',1);

