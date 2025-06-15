-- GetAccountRank Procedure
DELIMITER //
CREATE PROCEDURE GetAccountRank()
BEGIN
    SELECT 
        u.id as uuid,
        u.name,
        u.username,
        u.email,
        a.name as account_name,
        a.balance,
        DENSE_RANK() OVER (ORDER BY a.balance DESC) as account_rank
    FROM users u
    INNER JOIN budgets b ON b.user_id = u.id
    INNER JOIN accounts a ON a.budget_id = b.id
    WHERE a.type = 'cash'
    ORDER BY account_rank, u.name;
END //
DELIMITER ;

-- User Log
DELIMITER //
CREATE TRIGGER trigger_user_logs_after_insert


-- Transaction Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_monthly_budget_id VARCHAR(36);

    SELECT id INTO v_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = NEW.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(NEW.date, '%Y-%m');
    
    IF v_monthly_budget_id IS NOT NULL THEN
    DECLARE calc_available DECIMAL(19, 4);
    DECLARE calc_activity DECIMAL(19, 4);
    SET calc_activity = activity + NEW.amount;
    SET calc_available = assigned - (activity + NEW.amount);
        UPDATE category_budgets 
        SET activity = calc_activity,
            available = calc_available
        WHERE monthly_budget_id = v_monthly_budget_id 
        AND category_id = NEW.category_id;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER trigger_transactions_after_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_monthly_budget_id VARCHAR(36);

    SELECT id INTO v_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = NEW.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(NEW.date, '%Y-%m');
    
    IF v_monthly_budget_id IS NOT NULL THEN
        UPDATE category_budgets 
        SET activity = activity + NEW.amount,
            available = assigned - (activity + NEW.amount)
        WHERE monthly_budget_id = v_monthly_budget_id 
        AND category_id = NEW.category_id;
    END IF;
END //
DELIMITER ;

-- Connect using the command from Step 1 first!

-- Drop the trigger we were experimenting with
DROP TRIGGER IF EXISTS trigger_transactions_after_insert;

-- Recreate the original trigger WITHOUT any COLLATE clauses
DELIMITER //
CREATE TRIGGER trigger_transactions_after_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_monthly_budget_id VARCHAR(36);

    SELECT id INTO v_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = NEW.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(NEW.date, '%Y-%m');
    
    IF v_monthly_budget_id IS NOT NULL THEN
        UPDATE category_budgets 
        SET activity = activity + NEW.amount,
            available = assigned - (activity + NEW.amount)
        WHERE monthly_budget_id = v_monthly_budget_id 
        AND category_id = NEW.category_id;
    END IF;
END //
DELIMITER ;

-- Step 1: Drop the old trigger
DROP TRIGGER IF EXISTS trigger_transactions_after_insert;

-- Step 2: Create the fully corrected trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_monthly_budget_id VARCHAR(36);

    SELECT id INTO v_monthly_budget_id
    FROM monthly_budgets
    -- This first COLLATE is still good practice to keep
    WHERE budget_id COLLATE utf8mb4_unicode_ci = NEW.budget_id
    -- THIS IS THE CRITICAL FIX: Add COLLATE to the date string comparison
    AND DATE_FORMAT(month, '%Y-%m') COLLATE utf8mb4_unicode_ci = DATE_FORMAT(NEW.date, '%Y-%m');
    
    IF v_monthly_budget_id IS NOT NULL THEN
        UPDATE category_budgets
        SET activity = activity + NEW.amount,
            available = assigned - (activity + NEW.amount)
        WHERE monthly_budget_id = v_monthly_budget_id
        -- This COLLATE is also good practice to keep
        AND category_id COLLATE utf8mb4_unicode_ci = NEW.category_id;
    END IF;
END //
DELIMITER ;

-- Transaction Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_delete
AFTER DELETE ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_monthly_budget_id VARCHAR(36);
    
    SELECT id INTO v_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = OLD.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(OLD.date, '%Y-%m');
    
    IF v_monthly_budget_id IS NOT NULL THEN
        UPDATE category_budgets 
        SET activity = activity - OLD.amount,
            available = assigned - (activity - OLD.amount),
        WHERE monthly_budget_id = v_monthly_budget_id 
        AND category_id = OLD.category_id;
    END IF;
END //
DELIMITER ;

-- Transaction Update Trigger
DELIMITER //
CREATE TRIGGER trigger_transactions_after_update
AFTER UPDATE ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_old_monthly_budget_id VARCHAR(36);
    DECLARE v_new_monthly_budget_id VARCHAR(36);
    
    -- Find the monthly budget ID for the old transaction date
    SELECT id INTO v_old_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = OLD.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(OLD.date, '%Y-%m');
    
    -- Find the monthly budget ID for the new transaction date
    SELECT id INTO v_new_monthly_budget_id
    FROM monthly_budgets 
    WHERE budget_id = NEW.budget_id 
    AND DATE_FORMAT(month, '%Y-%m') = DATE_FORMAT(NEW.date, '%Y-%m');
    
    -- Update the old category budget if it exists
    IF v_old_monthly_budget_id IS NOT NULL AND OLD.category_id IS NOT NULL THEN
        UPDATE category_budgets 
        SET activity = activity - OLD.amount,
            available = assigned - (activity - OLD.amount)
        WHERE monthly_budget_id = v_old_monthly_budget_id 
        AND category_id = OLD.category_id;
    END IF;
    
    -- Update the new category budget if it exists
    IF v_new_monthly_budget_id IS NOT NULL AND NEW.category_id IS NOT NULL THEN
        UPDATE category_budgets 
        SET activity = activity + NEW.amount,
            available = assigned - (activity + NEW.amount)
        WHERE monthly_budget_id = v_new_monthly_budget_id 
        AND category_id = NEW.category_id;
    END IF;
END //
DELIMITER ;

-- Test
INSERT INTO budgets (id, user_id, name) VALUES
('BUD12345', '019772e2-85f9-739d-be2d-543ff40342ba', 'Test Budget 2023');

INSERT INTO monthly_budgets (id, budget_id, month, total_balance, total_assigned, total_activity, total_available) 
VALUES ('MBUD12345', (SELECT id FROM budgets WHERE id = 'BUD12345'), '2023-10-01', 0, 0, 0, 0);

INSERT INTO category_groups (id, budget_id, name) VALUES 
('CAGR12345', (SELECT id FROM budgets WHERE id = 'BUD12345'), 'Living Expenses');

INSERT INTO categories (id, category_group_id, name) VALUES
('CAT12345', (SELECT id FROM category_groups WHERE name = 'Living Expenses'), 'Groceries');

INSERT INTO category_budgets (id, monthly_budget_id, category_id, assigned, activity, available) VALUES
('CBUD12345', (SELECT id FROM monthly_budgets WHERE id = 'MBUD12345'), (SELECT id FROM categories WHERE id = 'CAT12345'), 1000, 0, 1000);

INSERT INTO accounts (id, budget_id, name, type, balance) VALUES
('ACCT12345', (SELECT id FROM budgets WHERE name = 'Test Budget 2023'), 'Main Account', 'cash', 0);

-- Test Insert Trigger
INSERT INTO transactions (id, account_id, category_id, budget_id, payee, date, amount, memo) 
VALUES (
    'TRANS12344',
    'ACCT12345',
    'CAT12345',
    'BUD12345',
    'Supermarket',
    '2023-10-17',
    1000,
    'Weekly Buy'
);

-- Verify the category_budgets was updated correctly
SELECT 'After INSERT' as test_case, c.name as category, cb.assigned, cb.activity, cb.available
FROM category_budgets cb
JOIN categories c ON c.id = cb.category_id
JOIN monthly_budgets mb ON mb.id = cb.monthly_budget_id
WHERE c.name = 'Groceries' AND DATE_FORMAT(mb.month, '%Y-%m') = '2023-10';

-- Test Update Trigger
UPDATE transactions
SET amount = -200.00
WHERE payee = 'Supermarket' AND DATE_FORMAT(date, '%Y-%m') = '2023-10';

-- Verify the category_budgets was updated correctly
SELECT 'After UPDATE' as test_case, c.name as category, cb.assigned, cb.activity, cb.available
FROM category_budgets cb
JOIN categories c ON c.id = cb.category_id
JOIN monthly_budgets mb ON mb.id = cb.monthly_budget_id
WHERE c.name = 'Groceries' AND DATE_FORMAT(mb.month, '%Y-%m') = '2023-10';

-- Test Delete Trigger
DELETE FROM transactions
WHERE payee = 'Supermarket' AND DATE_FORMAT(date, '%Y-%m') = '2023-10';

-- Verify the category_budgets was updated correctly
SELECT 'After DELETE' as test_case, c.name as category, cb.assigned, cb.activity, cb.available
FROM category_budgets cb
JOIN categories c ON c.id = cb.category_id
JOIN monthly_budgets mb ON mb.id = cb.monthly_budget_id
WHERE c.name = 'Groceries' AND DATE_FORMAT(mb.month, '%Y-%m') = '2023-10';

-- -- Monthly Budget Totals Trigger
-- DELIMITER //
-- CREATE TRIGGER trigger_update_monthly_totals
-- AFTER UPDATE ON category_budgets
-- FOR EACH ROW
-- BEGIN
--     -- Get the monthly budget details
--     SET @monthly_budget_id = NEW.monthly_budget_id;
--     SET @budget_id = (SELECT budget_id FROM monthly_budgets WHERE id = @monthly_budget_id);
--     SET @month = (SELECT month FROM monthly_budgets WHERE id = @monthly_budget_id);
    
--     -- Calculate totals with proper rounding
--     SET @total_assigned = (SELECT ROUND(SUM(assigned), 2) FROM category_budgets WHERE monthly_budget_id = @monthly_budget_id);
--     SET @total_activity = (SELECT ROUND(SUM(activity), 2) FROM category_budgets WHERE monthly_budget_id = @monthly_budget_id);
--     SET @total_available = (SELECT ROUND(SUM(available), 2) FROM category_budgets WHERE monthly_budget_id = @monthly_budget_id);
    
--     -- Get income and rollover with proper date handling
--     SET @current_income = (SELECT COALESCE(ROUND(SUM(amount), 2), 0) 
--         FROM transactions 
--         WHERE budget_id = @budget_id 
--         AND DATE_FORMAT(date, '%Y-%m-01') = DATE_FORMAT(@month, '%Y-%m-01') 
--         AND amount > 0);
    
--     SET @rollover = (SELECT COALESCE(total_available, 0) 
--         FROM monthly_budgets 
--         WHERE budget_id = @budget_id 
--         AND month = DATE_SUB(@month, INTERVAL 1 MONTH));
    
--     -- Update monthly budget totals
--     UPDATE monthly_budgets
--     SET
--         total_assigned = @total_assigned,
--         total_activity = @total_activity,
--         total_available = @total_available,
--         total_balance = ROUND(@rollover + @current_income - @total_assigned, 2)
--     WHERE id = @monthly_budget_id;
-- END //
-- DELIMITER ;