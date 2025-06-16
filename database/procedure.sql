-- GetAccountRank Procedure
DELIMITER //
DROP PROCEDURE IF EXISTS GetAccountRank;
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

-- Calculate Total Monthly Expense for a month
DELIMITER //
DROP FUNCTION IF EXISTS GetTotalMonthlyExpenseForMonth;
CREATE FUNCTION GetTotalMonthlyExpenseForMonth(p_year INT, p_month INT)
RETURNS DECIMAL(19, 4)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE total_expense DECIMAL(19, 4);
    
    SELECT COALESCE(ABS(SUM(amount)), 0) INTO total_expense
    FROM transactions
    WHERE 
        amount < 0
        AND YEAR(date) = p_year
        AND MONTH(date) = p_month;
    
    RETURN total_expense;
END //
DELIMITER ;

-- Calculate Average Monthly Expense for a month
DELIMITER //
DROP FUNCTION IF EXISTS GetAverageExpenseForMonth;
CREATE FUNCTION GetAverageExpenseForMonth(p_year INT, p_month INT)
RETURNS DECIMAL(19, 4)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE avg_expense DECIMAL(19, 4);
    
    SELECT COALESCE(ABS(AVG(amount)), 0) INTO avg_expense
    FROM transactions
    WHERE 
        amount < 0
        AND YEAR(date) = p_year
        AND MONTH(date) = p_month;
    
    RETURN avg_expense;
END //
DELIMITER ;

-- Calculate Average Daily Expense for a day
DELIMITER //
DROP FUNCTION IF EXISTS GetAverageExpenseForDay;
CREATE FUNCTION GetAverageExpenseForDay(p_year INT, p_month INT, p_day INT)
RETURNS DECIMAL(19, 4)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE avg_expense_for_day DECIMAL(19, 4);
    SELECT COALESCE(ABS(AVG(amount)), 0) INTO avg_expense_for_day
    FROM transactions
    WHERE 
        amount < 0
        AND YEAR(date) = p_year
        AND MONTH(date) = p_month
        AND DAY(date) = p_day;
    RETURN avg_expense_for_day;
END //
DELIMITER ;

-- Get Highest Expense for a month
DELIMITER //
DROP FUNCTION IF EXISTS GetHighestExpenseForMonth;
CREATE FUNCTION GetHighestExpenseForMonth(p_year INT, p_month INT)
RETURNS DECIMAL(19, 4)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE highest_expense DECIMAL(19, 4);
    SELECT COALESCE(ABS(MIN(amount)), 0) INTO highest_expense
    FROM transactions
    WHERE 
        amount < 0
        AND YEAR(date) = p_year 
        AND MONTH(date) = p_month;
    
    RETURN highest_expense;
END //
DELIMITER ;

-- Get Most Active Category for a month
DELIMITER //
DROP FUNCTION IF EXISTS GetMostActiveExpenseCategoryForMonth;
CREATE FUNCTION GetMostActiveExpenseCategoryForMonth(p_year INT, p_month INT)
RETURNS VARCHAR(255)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE most_active_category VARCHAR(255);
    
    SELECT c.name INTO most_active_category
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE 
        t.amount < 0
        AND YEAR(t.date) = p_year
        AND MONTH(t.date) = p_month
    GROUP BY t.category_id, c.name
    ORDER BY COUNT(*) DESC
    LIMIT 1;
    
    RETURN COALESCE(most_active_category, 'No Category');
END //
DELIMITER ;