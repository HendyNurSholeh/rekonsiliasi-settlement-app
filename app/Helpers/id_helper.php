<?php

if (!function_exists('encryptId')) {
    /**
     * Encrypt ID menggunakan base64 + salt untuk keamanan URL
     * Menghasilkan string yang URL-safe
     * 
     * @param int|string $id
     * @return string
     */
    function encryptId($id) {
        // Salt untuk keamanan tambahan
        $salt = 'TSI-UNDERLYING-DEVISA-2024';
        
        // Gabungkan ID dengan salt dan timestamp untuk uniqueness
        $data = $id . '|' . $salt . '|' . substr(md5(microtime()), 0, 8);
        
        // Encode ke base64
        $encoded = base64_encode($data);
        
        // Make URL-safe: replace characters yang bisa bermasalah di URL
        $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
        
        return $urlSafe;
    }
}

if (!function_exists('decryptId')) {
    /**
     * Decrypt ID dari encrypted string
     * Memvalidasi salt untuk keamanan
     * 
     * @param string $encrypted
     * @return int|null Returns original ID or null if invalid
     */
    function decryptId($encrypted) {
        try {
            // Restore base64 characters dari URL-safe format
            $restored = str_replace(['-', '_'], ['+', '/'], $encrypted);
            
            // Add padding jika diperlukan untuk base64
            $padLength = 4 - strlen($restored) % 4;
            if ($padLength != 4) {
                $restored .= str_repeat('=', $padLength);
            }
            
            // Decode dari base64
            $decoded = base64_decode($restored, true);
            
            if ($decoded === false) {
                return null;
            }
            
            // Split data berdasarkan delimiter
            $parts = explode('|', $decoded);
            
            // Validasi format dan salt
            if (count($parts) < 3 || $parts[1] !== 'TSI-UNDERLYING-DEVISA-2024') {
                return null;
            }
            
            // Ambil ID asli dan pastikan numeric
            $originalId = $parts[0];
            
            if (!is_numeric($originalId)) {
                return null;
            }
            
            return (int)$originalId;
            
        } catch (Exception $e) {
            // Log error jika diperlukan
            log_message('error', 'Decrypt ID failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('generateSecureUrl')) {
    /**
     * Generate secure URL dengan encrypted ID
     * 
     * @param string $baseUrl
     * @param int $id
     * @return string
     */
    function generateSecureUrl($baseUrl, $id) {
        $encryptedId = encryptId($id);
        return rtrim($baseUrl, '/') . '/' . $encryptedId;
    }
}

if (!function_exists('validateEncryptedId')) {
    /**
     * Validate apakah encrypted ID valid tanpa decrypt
     * Useful untuk quick validation
     * 
     * @param string $encrypted
     * @return bool
     */
    function validateEncryptedId($encrypted) {
        // Basic format validation
        if (empty($encrypted) || !is_string($encrypted)) {
            return false;
        }
        
        // Check jika mengandung karakter yang valid untuk base64 URL-safe
        if (!preg_match('/^[A-Za-z0-9\-_]+$/', $encrypted)) {
            return false;
        }
        
        // Try to decrypt untuk validation
        $decrypted = decryptId($encrypted);
        
        return $decrypted !== null;
    }
}

if (!function_exists('createTransactionUrl')) {
    /**
     * Helper khusus untuk membuat URL transaction dengan encrypted ID
     * 
     * @param int $underlyingId
     * @return string
     */
    function createTransactionUrl($underlyingId) {
        return generateSecureUrl(base_url('transaction'), $underlyingId);
    }
}
