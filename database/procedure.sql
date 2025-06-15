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