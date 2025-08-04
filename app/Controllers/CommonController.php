<?php

namespace App\Controllers;

class CommonController extends BaseController
{
    /**
     * Get CSRF Token for AJAX requests
     * Can be used throughout the entire application
     */
    public function getCsrfToken()
    {
        return $this->response->setJSON([
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Health check endpoint
     */
    public function healthCheck()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_token' => csrf_hash()
        ]);
    }
}
