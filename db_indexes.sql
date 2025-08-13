-- Performance optimization indexes for IPO Pulse database
-- These indexes will improve query performance for common operations

-- Index for IPO queries by status and date
CREATE INDEX IF NOT EXISTS idx_ipos_status ON ipos(status);
CREATE INDEX IF NOT EXISTS idx_ipos_dates ON ipos(open_date, close_date);
CREATE INDEX IF NOT EXISTS idx_ipos_status_date ON ipos(status, open_date);

-- Index for user lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_mobile ON users(mobile);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Index for admin user lookups
CREATE INDEX IF NOT EXISTS idx_admin_username ON admin_users(username);

-- Index for news by publication date
CREATE INDEX IF NOT EXISTS idx_news_published ON news(published_at DESC);

-- Index for demat accounts by user
CREATE INDEX IF NOT EXISTS idx_demat_user ON demat_accounts(user_id);

-- Index for IPO applications
CREATE INDEX IF NOT EXISTS idx_applications_user ON ipo_applications(user_id);
CREATE INDEX IF NOT EXISTS idx_applications_ipo ON ipo_applications(ipo_id);
CREATE INDEX IF NOT EXISTS idx_applications_status ON ipo_applications(status);

-- Index for notifications
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_read ON notifications(user_id, is_read);

-- Composite index for common IPO queries
CREATE INDEX IF NOT EXISTS idx_ipos_status_open_date ON ipos(status, open_date DESC);