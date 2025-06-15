-- Users Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_users_after_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.id, 'Insert', 
        CONCAT('User created: ', NEW.username), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'username', NEW.username,
            'email', NEW.email,
            'name', NEW.name,
            'role', NEW.role
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Users Update Trigger
DELIMITER //
CREATE TRIGGER trigger_users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.id, 'Update', 
        CONCAT('User updated: ', NEW.username), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'username', OLD.username,
            'email', OLD.email,
            'name', OLD.name,
            'role', OLD.role
        ),
        JSON_OBJECT(
            'username', NEW.username,
            'email', NEW.email,
            'name', NEW.name,
            'role', NEW.role
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Users Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_users_after_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    -- For delete operations, we might not have session info for the deleted user
    -- So we'll use a default or system user ID (could be an admin ID)
    DECLARE v_admin_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get an admin user ID or use a default
    SELECT id INTO v_admin_user_id
    FROM users
    WHERE role = 'admin'
    LIMIT 1;
    
    -- If no admin found, use the deleted user's ID (for logging purposes)
    SET v_admin_user_id = IFNULL(v_admin_user_id, OLD.id);
    
    -- Get the session information for the admin user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_admin_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_admin_user_id, 'Delete', 
        CONCAT('User deleted: ', OLD.username), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'username', OLD.username,
            'email', OLD.email,
            'name', OLD.name,
            'role', OLD.role
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Transactions Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Transaction created: ', NEW.payee, ' (', NEW.amount, ')'), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'account_id', NEW.account_id,
            'category_id', NEW.category_id,
            'budget_id', NEW.budget_id,
            'payee', NEW.payee,
            'date', NEW.date,
            'amount', NEW.amount,
            'memo', NEW.memo
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Transactions Update Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_update
AFTER UPDATE ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Transaction updated: ', NEW.payee, ' (', NEW.amount, ')'), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'account_id', OLD.account_id,
            'category_id', OLD.category_id,
            'payee', OLD.payee,
            'date', OLD.date,
            'amount', OLD.amount,
            'memo', OLD.memo
        ),
        JSON_OBJECT(
            'account_id', NEW.account_id,
            'category_id', NEW.category_id,
            'payee', NEW.payee,
            'date', NEW.date,
            'amount', NEW.amount,
            'memo', NEW.memo
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Transactions Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_delete
AFTER DELETE ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = OLD.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Transaction deleted: ', OLD.payee, ' (', OLD.amount, ')'), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'account_id', OLD.account_id,
            'category_id', OLD.category_id,
            'budget_id', OLD.budget_id,
            'payee', OLD.payee,
            'date', OLD.date,
            'amount', OLD.amount,
            'memo', OLD.memo
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Budgets Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_budgets_after_insert
AFTER INSERT ON budgets
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.user_id, 'Insert', 
        CONCAT('Budget created: ', NEW.name), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'name', NEW.name,
            'description', NEW.description,
            'currency_code', NEW.currency_code
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Budgets Update Trigger
DELIMITER //
CREATE TRIGGER trigger_budgets_after_update
AFTER UPDATE ON budgets
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.user_id, 'Update', 
        CONCAT('Budget updated: ', NEW.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'name', OLD.name,
            'description', OLD.description,
            'currency_code', OLD.currency_code
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'description', NEW.description,
            'currency_code', NEW.currency_code
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Budgets Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_budgets_after_delete
AFTER DELETE ON budgets
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = OLD.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), OLD.user_id, 'Delete', 
        CONCAT('Budget deleted: ', OLD.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'name', OLD.name,
            'description', OLD.description,
            'currency_code', OLD.currency_code
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Groups Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_category_groups_after_insert
AFTER INSERT ON category_groups
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Category group created: ', NEW.name), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'budget_id', NEW.budget_id,
            'name', NEW.name
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Groups Update Trigger
DELIMITER //
CREATE TRIGGER trigger_category_groups_after_update
AFTER UPDATE ON category_groups
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Category group updated: ', NEW.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'budget_id', OLD.budget_id,
            'name', OLD.name
        ),
        JSON_OBJECT(
            'budget_id', NEW.budget_id,
            'name', NEW.name
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Groups Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_category_groups_after_delete
AFTER DELETE ON category_groups
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = OLD.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Category group deleted: ', OLD.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'budget_id', OLD.budget_id,
            'name', OLD.name
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Categories Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_categories_after_insert
AFTER INSERT ON categories
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the category group
    SELECT budget_id INTO v_budget_id
    FROM category_groups
    WHERE id = NEW.category_group_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Category created: ', NEW.name), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'category_group_id', NEW.category_group_id,
            'name', NEW.name
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Categories Update Trigger
DELIMITER //
CREATE TRIGGER trigger_categories_after_update
AFTER UPDATE ON categories
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the category group
    SELECT budget_id INTO v_budget_id
    FROM category_groups
    WHERE id = NEW.category_group_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Category updated: ', NEW.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'category_group_id', OLD.category_group_id,
            'name', OLD.name
        ),
        JSON_OBJECT(
            'category_group_id', NEW.category_group_id,
            'name', NEW.name
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Categories Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_categories_after_delete
AFTER DELETE ON categories
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the category group
    SELECT budget_id INTO v_budget_id
    FROM category_groups
    WHERE id = OLD.category_group_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Category deleted: ', OLD.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'category_group_id', OLD.category_group_id,
            'name', OLD.name
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Accounts Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_accounts_after_insert
AFTER INSERT ON accounts
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Account created: ', NEW.name), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'budget_id', NEW.budget_id,
            'name', NEW.name,
            'type', NEW.type,
            'interest', NEW.interest,
            'minimum_payment_monthly', NEW.minimum_payment_monthly,
            'balance', NEW.balance
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Accounts Update Trigger
DELIMITER //
CREATE TRIGGER trigger_accounts_after_update
AFTER UPDATE ON accounts
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Account updated: ', NEW.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'name', OLD.name,
            'type', OLD.type,
            'interest', OLD.interest,
            'minimum_payment_monthly', OLD.minimum_payment_monthly,
            'balance', OLD.balance
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'type', NEW.type,
            'interest', NEW.interest,
            'minimum_payment_monthly', NEW.minimum_payment_monthly,
            'balance', NEW.balance
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Accounts Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_accounts_after_delete
AFTER DELETE ON accounts
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = OLD.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Account deleted: ', OLD.name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'budget_id', OLD.budget_id,
            'name', OLD.name,
            'type', OLD.type,
            'interest', OLD.interest,
            'minimum_payment_monthly', OLD.minimum_payment_monthly,
            'balance', OLD.balance
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Budgets Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_insert
AFTER INSERT ON category_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_category_name VARCHAR(255);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the monthly budget
    SELECT budget_id INTO v_budget_id
    FROM monthly_budgets
    WHERE id = NEW.monthly_budget_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the category name
    SELECT name INTO v_category_name
    FROM categories
    WHERE id = NEW.category_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Category budget created for: ', v_category_name), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'monthly_budget_id', NEW.monthly_budget_id,
            'category_id', NEW.category_id,
            'assigned', NEW.assigned,
            'activity', NEW.activity,
            'available', NEW.available
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Budgets Update Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_update
AFTER UPDATE ON category_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_category_name VARCHAR(255);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the monthly budget
    SELECT budget_id INTO v_budget_id
    FROM monthly_budgets
    WHERE id = NEW.monthly_budget_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the category name
    SELECT name INTO v_category_name
    FROM categories
    WHERE id = NEW.category_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Category budget updated for: ', v_category_name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'assigned', OLD.assigned,
            'activity', OLD.activity,
            'available', OLD.available
        ),
        JSON_OBJECT(
            'assigned', NEW.assigned,
            'activity', NEW.activity,
            'available', NEW.available
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Category Budgets Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_delete
AFTER DELETE ON category_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_budget_id VARCHAR(36);
    DECLARE v_category_name VARCHAR(255);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the budget ID from the monthly budget
    SELECT budget_id INTO v_budget_id
    FROM monthly_budgets
    WHERE id = OLD.monthly_budget_id;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = v_budget_id;
    
    -- Get the category name
    SELECT name INTO v_category_name
    FROM categories
    WHERE id = OLD.category_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Category budget deleted for: ', v_category_name), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'monthly_budget_id', OLD.monthly_budget_id,
            'category_id', OLD.category_id,
            'assigned', OLD.assigned,
            'activity', OLD.activity,
            'available', OLD.available
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Monthly Budgets Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_monthly_budgets_after_insert
AFTER INSERT ON monthly_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Insert', 
        CONCAT('Monthly budget created for: ', DATE_FORMAT(NEW.month, '%Y-%m')), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'budget_id', NEW.budget_id,
            'month', NEW.month,
            'total_balance', NEW.total_balance,
            'total_assigned', NEW.total_assigned,
            'total_activity', NEW.total_activity,
            'total_available', NEW.total_available
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Monthly Budgets Update Trigger
DELIMITER //
CREATE TRIGGER trigger_monthly_budgets_after_update
AFTER UPDATE ON monthly_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = NEW.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Update', 
        CONCAT('Monthly budget updated for: ', DATE_FORMAT(NEW.month, '%Y-%m')), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'month', OLD.month,
            'total_balance', OLD.total_balance,
            'total_assigned', OLD.total_assigned,
            'total_activity', OLD.total_activity,
            'total_available', OLD.total_available
        ),
        JSON_OBJECT(
            'month', NEW.month,
            'total_balance', NEW.total_balance,
            'total_assigned', NEW.total_assigned,
            'total_activity', NEW.total_activity,
            'total_available', NEW.total_available
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Monthly Budgets Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_monthly_budgets_after_delete
AFTER DELETE ON monthly_budgets
FOR EACH ROW
BEGIN
    DECLARE v_user_id VARCHAR(36);
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the user ID from the budget
    SELECT user_id INTO v_user_id
    FROM budgets
    WHERE id = OLD.budget_id;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = v_user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), v_user_id, 'Delete', 
        CONCAT('Monthly budget deleted for: ', DATE_FORMAT(OLD.month, '%Y-%m')), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'budget_id', OLD.budget_id,
            'month', OLD.month,
            'total_balance', OLD.total_balance,
            'total_assigned', OLD.total_assigned,
            'total_activity', OLD.total_activity,
            'total_available', OLD.total_available
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Subscriptions Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_subscriptions_after_insert
AFTER INSERT ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.user_id, 'Insert', 
        CONCAT('Subscription created: ', NEW.invoice), 
        v_ip_address, v_user_agent,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'user_id', NEW.user_id,
            'invoice', NEW.invoice,
            'payment_method', NEW.payment_method,
            'start_date', NEW.start_date,
            'end_date', NEW.end_date
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Subscriptions Update Trigger
DELIMITER //
CREATE TRIGGER trigger_subscriptions_after_update
AFTER UPDATE ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = NEW.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), NEW.user_id, 'Update', 
        CONCAT('Subscription updated: ', NEW.invoice), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'invoice', OLD.invoice,
            'payment_method', OLD.payment_method,
            'start_date', OLD.start_date,
            'end_date', OLD.end_date
        ),
        JSON_OBJECT(
            'invoice', NEW.invoice,
            'payment_method', NEW.payment_method,
            'start_date', NEW.start_date,
            'end_date', NEW.end_date
        ),
        NOW(), NOW()
    );
END //
DELIMITER ;

-- Subscriptions Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_subscriptions_after_delete
AFTER DELETE ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE v_ip_address VARCHAR(45);
    DECLARE v_user_agent TEXT;
    
    -- Get the session information for the user
    SELECT ip_address, user_agent INTO v_ip_address, v_user_agent
    FROM sessions
    WHERE user_id = OLD.user_id
    ORDER BY last_activity DESC
    LIMIT 1;
    
    -- Set default values if session info is not available
    SET v_ip_address = IFNULL(v_ip_address, '127.0.0.1');
    SET v_user_agent = IFNULL(v_user_agent, 'System');
    
    INSERT INTO user_logs (
        id, user_id, action, description, ip_address, user_agent, 
        old_values, new_values, created_at, updated_at
    )
    VALUES (
        UUID(), OLD.user_id, 'Delete', 
        CONCAT('Subscription deleted: ', OLD.invoice), 
        v_ip_address, v_user_agent,
        JSON_OBJECT(
            'id', OLD.id,
            'user_id', OLD.user_id,
            'invoice', OLD.invoice,
            'payment_method', OLD.payment_method,
            'start_date', OLD.start_date,
            'end_date', OLD.end_date
        ),
        NULL,
        NOW(), NOW()
    );
END //
DELIMITER ;