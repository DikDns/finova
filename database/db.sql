-- Category Budgets Insert Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_insert
AFTER INSERT ON category_budgets
FOR EACH ROW
BEGIN
    UPDATE monthly_budgets
    SET
        total_assigned = total_assigned + NEW.assigned,
        total_activity = total_activity + NEW.activity,
        total_available = total_available + NEW.available,
        -- The balance is reduced by what's newly assigned and adjusted by any initial activity
        total_balance = total_balance - NEW.assigned + NEW.activity
    WHERE id = NEW.monthly_budget_id;
END; //
DELIMITER ;

-- Category Budgets Update Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_update
AFTER UPDATE ON category_budgets
FOR EACH ROW
BEGIN
    -- Calculate the change (delta) for each column
    DECLARE delta_assigned DECIMAL(19, 4);
    DECLARE delta_activity DECIMAL(19, 4);
    DECLARE delta_available DECIMAL(19, 4);

    SET delta_assigned = NEW.assigned - OLD.assigned;
    SET delta_activity = NEW.activity - OLD.activity;
    SET delta_available = NEW.available - OLD.available;

    UPDATE monthly_budgets
    SET
        total_assigned = total_assigned + delta_assigned,
        total_activity = total_activity + delta_activity,
        total_available = total_available + delta_available,
        -- The balance is adjusted by the change in assignments and activity
        total_balance = total_balance - delta_assigned + delta_activity
    WHERE id = NEW.monthly_budget_id;
END; //
DELIMITER ;

-- Category Budgets Delete Trigger
DELIMITER //
CREATE TRIGGER trigger_category_budgets_after_delete
AFTER DELETE ON category_budgets
FOR EACH ROW
BEGIN
    UPDATE monthly_budgets
    SET
        total_assigned = total_assigned - OLD.assigned,
        total_activity = total_activity - OLD.activity,
        total_available = total_available - OLD.available,
        -- The balance is increased by the de-allocated funds and adjusted by the removed activity
        total_balance = total_balance + OLD.assigned - OLD.activity
    WHERE id = OLD.monthly_budget_id;
END; //
DELIMITER ;
