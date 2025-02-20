-- Adminer 4.8.1 PostgreSQL 17.2 (Debian 17.2-1.pgdg120+1) dump

DROP TABLE IF EXISTS "blocks";
CREATE TABLE "public"."blocks" (
                                   "id" uuid NOT NULL,
                                   "hash" character varying(64) NOT NULL,
                                   "previous_hash" character varying(64),
                                   "timestamp" timestamp DEFAULT CURRENT_TIMESTAMP,
                                   "transaction_id" uuid NOT NULL,
                                   CONSTRAINT "blocks_hash_key" UNIQUE ("hash"),
                                   CONSTRAINT "blocks_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "facture";
CREATE TABLE "public"."facture" (
                                    "id" uuid NOT NULL,
                                    "seller_login" character varying(100) NOT NULL,
                                    "label" character varying(255) NOT NULL,
                                    "amount" numeric(12,2) NOT NULL,
                                    "status" character varying(50) NOT NULL,
                                    "qr_code" bytea,
                                    CONSTRAINT "facture_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "tickets";
CREATE TABLE "public"."tickets" (
                                    "id" uuid DEFAULT gen_random_uuid() NOT NULL,
                                    "user_login" character varying(100) NOT NULL,
                                    "idadmin" uuid,
                                    "message" text NOT NULL,
                                    "type" character varying(50) NOT NULL,
                                    "status" character varying(20) DEFAULT 'en attente' NOT NULL,
                                    CONSTRAINT "tickets_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "transactions";
CREATE TABLE "public"."transactions" (
                                         "id" uuid NOT NULL,
                                         "account" character varying(255) NOT NULL,
                                         "amount" numeric(12,2) NOT NULL,
                                         "type" character varying(10) NOT NULL,
                                         CONSTRAINT "transactions_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "users";
CREATE TABLE "public"."users" (
                                  "id" uuid NOT NULL,
                                  "login" character varying(100) NOT NULL,
                                  "email" character varying(150) NOT NULL,
                                  "password" character varying(255) NOT NULL,
                                  "role" integer NOT NULL,
                                  CONSTRAINT "users_email_key" UNIQUE ("email"),
                                  CONSTRAINT "users_login_key" UNIQUE ("login"),
                                  CONSTRAINT "users_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


ALTER TABLE ONLY "public"."blocks" ADD CONSTRAINT "blocks_transaction_id_fkey" FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE NOT DEFERRABLE;

ALTER TABLE ONLY "public"."facture" ADD CONSTRAINT "facture_seller_login_fkey" FOREIGN KEY (seller_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."tickets" ADD CONSTRAINT "tickets_idadmin_fkey" FOREIGN KEY (idadmin) REFERENCES users(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."tickets" ADD CONSTRAINT "tickets_user_login_fkey" FOREIGN KEY (user_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."transactions" ADD CONSTRAINT "transactions_account_fkey" FOREIGN KEY (account) REFERENCES users(login) NOT DEFERRABLE;

