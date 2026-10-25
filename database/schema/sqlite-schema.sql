CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" varchar not null,
  "queue" varchar not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs"(
  "connection",
  "queue",
  "failed_at"
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "customers"(
  "id" integer primary key autoincrement not null,
  "shop_id" integer not null,
  "shopify_customer_id" varchar not null,
  "email" varchar,
  "first_name" varchar,
  "last_name" varchar,
  "phone" varchar,
  "accepts_marketing" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("shop_id") references "shops"("id") on delete cascade
);
CREATE INDEX "customers_shop_id_index" on "customers"("shop_id");
CREATE INDEX "customers_shopify_customer_id_index" on "customers"(
  "shopify_customer_id"
);
CREATE INDEX "customers_email_index" on "customers"("email");
CREATE INDEX "customers_shop_id_shopify_customer_id_index" on "customers"(
  "shop_id",
  "shopify_customer_id"
);
CREATE UNIQUE INDEX "customers_shopify_customer_id_unique" on "customers"(
  "shopify_customer_id"
);
CREATE TABLE IF NOT EXISTS "shops"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "domain" varchar not null,
  "shopify_shop_id" varchar not null,
  "access_token" text,
  "email" varchar,
  "status" varchar not null default 'active',
  "installed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "shops_shopify_shop_id_index" on "shops"("shopify_shop_id");
CREATE INDEX "shops_domain_index" on "shops"("domain");
CREATE INDEX "shops_status_index" on "shops"("status");
CREATE UNIQUE INDEX "shops_domain_unique" on "shops"("domain");
CREATE UNIQUE INDEX "shops_shopify_shop_id_unique" on "shops"(
  "shopify_shop_id"
);
CREATE TABLE IF NOT EXISTS "wishlists"(
  "id" integer primary key autoincrement not null,
  "shop_id" integer not null,
  "customer_id" integer not null,
  "name" varchar not null,
  "is_public" tinyint(1) not null default '0',
  "is_default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("shop_id") references "shops"("id") on delete cascade,
  foreign key("customer_id") references "customers"("id") on delete cascade
);
CREATE INDEX "wishlists_shop_id_index" on "wishlists"("shop_id");
CREATE INDEX "wishlists_customer_id_index" on "wishlists"("customer_id");
CREATE INDEX "wishlists_shop_id_customer_id_index" on "wishlists"(
  "shop_id",
  "customer_id"
);
CREATE INDEX "wishlists_shop_id_is_public_index" on "wishlists"(
  "shop_id",
  "is_public"
);
CREATE TABLE IF NOT EXISTS "wishlist_items"(
  "id" integer primary key autoincrement not null,
  "wishlist_id" integer not null,
  "shopify_product_id" varchar not null,
  "shopify_variant_id" varchar,
  "product_title" varchar not null,
  "variant_title" varchar,
  "price" numeric,
  "compare_at_price" numeric,
  "quantity" integer not null default '1',
  "image_url" varchar,
  "handle" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("wishlist_id") references "wishlists"("id") on delete cascade
);
CREATE INDEX "wishlist_items_wishlist_id_index" on "wishlist_items"(
  "wishlist_id"
);
CREATE INDEX "wishlist_items_shopify_product_id_index" on "wishlist_items"(
  "shopify_product_id"
);
CREATE INDEX "wishlist_items_shopify_variant_id_index" on "wishlist_items"(
  "shopify_variant_id"
);
CREATE INDEX "wishlist_items_wishlist_id_shopify_product_id_index" on "wishlist_items"(
  "wishlist_id",
  "shopify_product_id"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_10_25_165157_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(5,'2026_10_25_165507_create_customers_table',1);
INSERT INTO migrations VALUES(6,'2026_10_25_165508_create_shops_table',1);
INSERT INTO migrations VALUES(7,'2026_10_25_165509_create_wishlists_table',1);
INSERT INTO migrations VALUES(8,'2026_10_25_165510_create_wishlist_items_table',1);
