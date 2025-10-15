-- =====================================================
-- APPROVE JURNAL SETTLEMENT ENHANCEMENT
-- Database Migration Script
-- =====================================================
-- Purpose: Add net amount columns and update status field
-- Date: 2025-10-15
-- =====================================================

-- =====================================================
-- STEP 1: ADD NEW COLUMNS
-- =====================================================

-- Add AMOUNT_NET_DB_ECR column (Net Amount Debet from ECR)
ALTER TABLE t_settle_produk 
ADD COLUMN IF NOT EXISTS AMOUNT_NET_DB_ECR DECIMAL(18,2) DEFAULT 0 
COMMENT 'Net amount debet dari ECR - untuk validasi balanced transaction';

-- Add AMOUNT_NET_KR_ECR column (Net Amount Credit from ECR)
ALTER TABLE t_settle_produk 
ADD COLUMN IF NOT EXISTS AMOUNT_NET_KR_ECR DECIMAL(18,2) DEFAULT 0 
COMMENT 'Net amount credit dari ECR - untuk validasi balanced transaction';

-- =====================================================
-- STEP 2: UPDATE STATUS FIELD TYPE
-- =====================================================

-- Modify STAT_APPROVER to support new status codes
-- -1: Tidak bisa approve (net amount beda)
-- 0: Belum approve
-- 1: Sudah approve
-- 9: Reject
ALTER TABLE t_settle_produk 
MODIFY COLUMN STAT_APPROVER VARCHAR(2) DEFAULT NULL 
COMMENT 'Status approval: -1=net beda, 0=belum approve, 1=sudah approve, 9=reject';

-- =====================================================
-- STEP 3: BACKFILL DATA (OPTIONAL)
-- =====================================================
-- If you need to populate existing data with calculated net amounts

-- Example: Calculate net debet from tamp_settle_message
UPDATE t_settle_produk sp
SET AMOUNT_NET_DB_ECR = (
    SELECT COALESCE(SUM(CAST(REPLACE(REPLACE(tsm.AMOUNT, ',', ''), '.', '') AS DECIMAL(18,2))), 0)
    FROM tamp_settle_message tsm
    WHERE tsm.KD_SETTLE = sp.KD_SETTLE
      AND tsm.DEBIT_ACCOUNT IS NOT NULL
      AND tsm.DEBIT_ACCOUNT != ''
);

-- Example: Calculate net credit from tamp_settle_message
UPDATE t_settle_produk sp
SET AMOUNT_NET_KR_ECR = (
    SELECT COALESCE(SUM(CAST(REPLACE(REPLACE(tsm.AMOUNT, ',', ''), '.', '') AS DECIMAL(18,2))), 0)
    FROM tamp_settle_message tsm
    WHERE tsm.KD_SETTLE = sp.KD_SETTLE
      AND tsm.CREDIT_ACCOUNT IS NOT NULL
      AND tsm.CREDIT_ACCOUNT != ''
);

-- =====================================================
-- STEP 4: AUTO-UPDATE STATUS FOR NET MISMATCH
-- =====================================================

-- Set status to -1 for records where net amount doesn't match
-- Only update records that are not yet processed (NULL, '', or '0')
UPDATE t_settle_produk 
SET STAT_APPROVER = '-1'
WHERE (STAT_APPROVER IS NULL OR STAT_APPROVER = '' OR STAT_APPROVER = '0')
  AND ABS(AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) > 0.01  -- 0.01 tolerance for floating point
  AND AMOUNT_NET_DB_ECR > 0  -- Only check if there's actual data
  AND AMOUNT_NET_KR_ECR > 0;

-- =====================================================
-- STEP 5: ADD INDEXES FOR PERFORMANCE (OPTIONAL)
-- =====================================================

-- Index for status filtering
CREATE INDEX IF NOT EXISTS idx_settle_produk_stat_approver 
ON t_settle_produk(STAT_APPROVER);

-- Index for date filtering (if not exists)
CREATE INDEX IF NOT EXISTS idx_settle_produk_tgl_data 
ON t_settle_produk(TGL_DATA);

-- Composite index for common queries
CREATE INDEX IF NOT EXISTS idx_settle_produk_tgl_stat 
ON t_settle_produk(TGL_DATA, STAT_APPROVER);

-- =====================================================
-- STEP 6: VERIFICATION QUERIES
-- =====================================================

-- Check if columns are added
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 't_settle_produk' 
  AND COLUMN_NAME IN ('AMOUNT_NET_DB_ECR', 'AMOUNT_NET_KR_ECR', 'STAT_APPROVER');

-- Check distribution of status codes
SELECT 
    STAT_APPROVER,
    CASE 
        WHEN STAT_APPROVER = '-1' THEN 'Net Amount Beda'
        WHEN STAT_APPROVER = '0' THEN 'Belum Approve'
        WHEN STAT_APPROVER = '1' THEN 'Sudah Approve'
        WHEN STAT_APPROVER = '9' THEN 'Reject'
        WHEN STAT_APPROVER IS NULL THEN 'Pending'
        ELSE 'Unknown'
    END AS STATUS_DESC,
    COUNT(*) AS TOTAL
FROM t_settle_produk 
GROUP BY STAT_APPROVER
ORDER BY STAT_APPROVER;

-- Check records with net mismatch
SELECT 
    KD_SETTLE,
    NAMA_PRODUK,
    TGL_DATA,
    AMOUNT_NET_DB_ECR,
    AMOUNT_NET_KR_ECR,
    (AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) AS DIFF,
    STAT_APPROVER
FROM t_settle_produk 
WHERE ABS(AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) > 0.01
  AND AMOUNT_NET_DB_ECR > 0
  AND AMOUNT_NET_KR_ECR > 0
ORDER BY ABS(AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) DESC
LIMIT 20;

-- Check sample data
SELECT 
    id,
    KD_SETTLE,
    NAMA_PRODUK,
    TGL_DATA,
    TOT_JURNAL_KR_ECR,
    AMOUNT_NET_DB_ECR,
    AMOUNT_NET_KR_ECR,
    CASE 
        WHEN ABS(AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) < 0.01 THEN 'MATCH'
        ELSE 'MISMATCH'
    END AS NET_STATUS,
    STAT_APPROVER,
    USER_APPROVER,
    TGL_APPROVER
FROM t_settle_produk 
ORDER BY TGL_DATA DESC
LIMIT 10;

-- =====================================================
-- STEP 7: ROLLBACK SCRIPT (IF NEEDED)
-- =====================================================

/*
-- Rollback: Remove new columns
ALTER TABLE t_settle_produk 
DROP COLUMN IF EXISTS AMOUNT_NET_DB_ECR,
DROP COLUMN IF EXISTS AMOUNT_NET_KR_ECR;

-- Rollback: Revert status field
ALTER TABLE t_settle_produk 
MODIFY COLUMN STAT_APPROVER VARCHAR(1) DEFAULT NULL;

-- Rollback: Reset status -1 to NULL
UPDATE t_settle_produk 
SET STAT_APPROVER = NULL
WHERE STAT_APPROVER = '-1';

-- Rollback: Drop indexes
DROP INDEX IF EXISTS idx_settle_produk_stat_approver ON t_settle_produk;
DROP INDEX IF EXISTS idx_settle_produk_tgl_stat ON t_settle_produk;
*/

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. Backup database before running migration
-- 2. Run verification queries after migration
-- 3. Test thoroughly in development environment first
-- 4. Consider running during low-traffic hours
-- 5. Monitor application logs after deployment
-- =====================================================

-- Migration completed successfully
SELECT 'Migration completed. Please run verification queries.' AS STATUS;
