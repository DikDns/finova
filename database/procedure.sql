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

-- Calculate Average Monthly Expense
DELIMITER //
CREATE FUNCTION GetAverageMonthlyExpense()
RETURNS DECIMAL(19, 4)
DETERMINIST
BEGIN
    DECLARE avg_monthly_expense DECIMAL(19, 4);
    
    SELECT ABS(AVG(monthly_total)) INTO avg_monthly_expense
    FROM (
        SELECT DATE_FORMAT(date, '%Y-%m') AS month,
               SUM(amount) as monthly_total
        FROM transactions
        WHERE amount < 0
        GROUP BY DATE_FORMAT(date, '%Y-%m')
    ) AS monthly_expenses;
    
    RETURN COALESCE(avg_monthly_expense, 0);
END //
DELIMITER ;

-- Calculate Average Daily Expense
DELIMITER //
CREATE FUNCTION GetAverageDailyExpense()
RETURNS DECIMAL(19, 4)
DETERMINIST
BEGIN
    DECLARE avg_daily_expense DECIMAL(19, 4);
    
    SELECT ABS(AVG(daily_total)) INTO avg_daily_expense
    FROM (
        SELECT DATE(date) AS day,
               SUM(amount) as daily_total
        FROM transactions
        WHERE amount < 0
        GROUP BY DATE(date)
    ) AS daily_expenses;
    
    RETURN COALESCE(avg_daily_expense, 0);
END //
DELIMITER ;

-- Get Most Active Expense Category
DELIMITER //
CREATE FUNCTION GetMostActiveExpenseCategory()
RETURNS VARCHAR(255)
DETERMINIST
BEGIN
    DECLARE most_active_category VARCHAR(255);
    
    SELECT c.name INTO most_active_category
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.amount < 0
    GROUP BY t.category_id, c.name
    ORDER BY COUNT(*) DESC
    LIMIT 1;
    
    RETURN COALESCE(most_active_category, 'No Category');
END //
DELIMITER ;

-- Get Highest Monthly Expense
DELIMITER //
CREATE FUNCTION GetHighestMonthlyExpense()
RETURNS DECIMAL(19, 4)
DETERMINIST
BEGIN
    DECLARE highest_expense DECIMAL(19, 4);
    
    SELECT ABS(MIN(monthly_total)) INTO highest_expense
    FROM (
        SELECT DATE_FORMAT(date, '%Y-%m') AS month,
               SUM(amount) as monthly_total
        FROM transactions
        WHERE amount < 0
        GROUP BY DATE_FORMAT(date, '%Y-%m')
    ) AS monthly_expenses;
    
    RETURN COALESCE(highest_expense, 0);
END //
DELIMITER ;