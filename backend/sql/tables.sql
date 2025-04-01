-- Adminer 4.17.1 PostgreSQL 17.4 (Debian 17.4-1.pgdg120+2) dump

DROP TABLE IF EXISTS "blocks";
CREATE TABLE "public"."blocks" (
    "id" uuid NOT NULL,
    "hash" character varying(64) NOT NULL,
    "previous_hash" character varying(64),
    "timestamp" timestamp DEFAULT CURRENT_TIMESTAMP,
    "account" character varying(255) NOT NULL,
    "amount" numeric(12,2) NOT NULL,
    "emitter" character varying(255) NOT NULL,
    "receiver" character varying(255) NOT NULL,
    CONSTRAINT "blocks_new_hash_key" UNIQUE ("hash"),
    CONSTRAINT "blocks_new_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "chat_requests";
CREATE TABLE "public"."chat_requests" (
    "id" uuid DEFAULT gen_random_uuid() NOT NULL,
    "sender_login" character varying(100) NOT NULL,
    "receiver_login" character varying(100) NOT NULL,
    "status" character varying(20) DEFAULT 'pending',
    "timestamp" timestamp DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "chat_requests_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "conversations";
CREATE TABLE "public"."conversations" (
    "id" uuid DEFAULT gen_random_uuid() NOT NULL,
    "user1_login" character varying(100) NOT NULL,
    "user2_login" character varying(100) NOT NULL,
    "last_message_timestamp" timestamp DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "conversations_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "facture";
CREATE TABLE "public"."facture" (
    "id" uuid NOT NULL,
    "seller_login" character varying(100) NOT NULL,
    "buyer_login" character varying(100),
    "label" character varying(255) NOT NULL,
    "amount" numeric(12,2) NOT NULL,
    "status" character varying(50) NOT NULL,
    "qr_code" bytea,
    "created_at" timestamp DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "facture_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "messages";
CREATE TABLE "public"."messages" (
    "id" uuid DEFAULT gen_random_uuid() NOT NULL,
    "sender_login" character varying(100) NOT NULL,
    "receiver_login" character varying(100) NOT NULL,
    "content" text NOT NULL,
    "timestamp" timestamp DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "messages_pkey" PRIMARY KEY ("id")
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


ALTER TABLE ONLY "public"."blocks" ADD CONSTRAINT "blocks_account_fkey" FOREIGN KEY (account) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."blocks" ADD CONSTRAINT "blocks_emitter_fkey" FOREIGN KEY (emitter) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."blocks" ADD CONSTRAINT "blocks_receiver_fkey" FOREIGN KEY (receiver) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."chat_requests" ADD CONSTRAINT "chat_requests_receiver_fk" FOREIGN KEY (receiver_login) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."chat_requests" ADD CONSTRAINT "chat_requests_sender_fk" FOREIGN KEY (sender_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."conversations" ADD CONSTRAINT "conversations_user1_fk" FOREIGN KEY (user1_login) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."conversations" ADD CONSTRAINT "conversations_user2_fk" FOREIGN KEY (user2_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."facture" ADD CONSTRAINT "facture_buyer_login_fkey" FOREIGN KEY (buyer_login) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."facture" ADD CONSTRAINT "facture_seller_login_fkey" FOREIGN KEY (seller_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."messages" ADD CONSTRAINT "messages_receiver_fk" FOREIGN KEY (receiver_login) REFERENCES users(login) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."messages" ADD CONSTRAINT "messages_sender_fk" FOREIGN KEY (sender_login) REFERENCES users(login) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."tickets" ADD CONSTRAINT "tickets_idadmin_fkey" FOREIGN KEY (idadmin) REFERENCES users(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."tickets" ADD CONSTRAINT "tickets_user_login_fkey" FOREIGN KEY (user_login) REFERENCES users(login) NOT DEFERRABLE;

-- 2025-03-31 12:12:32.172247+00