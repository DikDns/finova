-- Fix collation issues by standardizing to utf8mb4_unicode_ci
-- This script should be run before re-applying triggers.sql

-- Get the database name
SET @db_name = DATABASE();

-- Change database default collation
-- Direct execution instead of prepared statement
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Drop all triggers (will be recreated later)
DROP TRIGGER IF EXISTS trigger_users_after_insert;
DROP TRIGGER IF EXISTS trigger_users_after_update;
DROP TRIGGER IF EXISTS trigger_users_after_delete;
DROP TRIGGER IF EXISTS trigger_transactions_after_insert;
DROP TRIGGER IF EXISTS trigger_transactions_after_update;
DROP TRIGGER IF EXISTS trigger_transactions_after_delete;
DROP TRIGGER IF EXISTS trigger_budgets_after_insert;
DROP TRIGGER IF EXISTS trigger_budgets_after_update;
DROP TRIGGER IF EXISTS trigger_budgets_after_delete;
DROP TRIGGER IF EXISTS trigger_category_groups_after_insert;
DROP TRIGGER IF EXISTS trigger_category_groups_after_update;
DROP TRIGGER IF EXISTS trigger_category_groups_after_delete;
DROP TRIGGER IF EXISTS trigger_categories_after_insert;
DROP TRIGGER IF EXISTS trigger_categories_after_update;
DROP TRIGGER IF EXISTS trigger_categories_after_delete;
DROP TRIGGER IF EXISTS trigger_accounts_after_insert;
DROP TRIGGER IF EXISTS trigger_accounts_after_update;
DROP TRIGGER IF EXISTS trigger_accounts_after_delete;
DROP TRIGGER IF EXISTS trigger_category_budgets_after_insert;
DROP TRIGGER IF EXISTS trigger_category_budgets_after_update;
DROP TRIGGER IF EXISTS trigger_category_budgets_after_delete;
DROP TRIGGER IF EXISTS trigger_monthly_budgets_after_insert;
DROP TRIGGER IF EXISTS trigger_monthly_budgets_after_update;
DROP TRIGGER IF EXISTS trigger_monthly_budgets_after_delete;
DROP TRIGGER IF EXISTS trigger_subscriptions_after_insert;
DROP TRIGGER IF EXISTS trigger_subscriptions_after_update;
DROP TRIGGER IF EXISTS trigger_subscriptions_after_delete;

-- Convert all tables to utf8mb4_unicode_ci
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE password_reset_tokens CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE budgets CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE category_groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE category_budgets CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE monthly_budgets CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE accounts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE transactions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE user_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE export_reports CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE subscriptions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ai_chats CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- After running this script, execute the trigger.sql file again to recreate all triggers
