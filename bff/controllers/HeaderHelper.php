<?php
class HeaderHelper {
    private static function hasHeader($header) {
        $headers = headers_list();
        foreach ($headers as $h) {
            if (stripos($h, $header) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function setHtmlHeaders() {
        if (!headers_sent()) {
            self::clearHeaders();
            
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
            
            self::setCorsHeaders();
        }
    }

    public static function setJsonHeaders() {
        if (!headers_sent()) {
            self::clearHeaders();
            
            header('Content-Type: application/json');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
            
            self::setCorsHeaders();
        }
    }

    public static function setCorsHeaders() {
        if (!headers_sent()) {
            if (!self::hasHeader('Access-Control-Allow-Origin')) {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization');
            }
        }
    }

    private static function clearHeaders() {
        // Lista de headers comuns que queremos remover
        $headersToRemove = [
            'Content-Type',
            'Cache-Control',
            'Pragma',
            'Expires',
            'Access-Control-Allow-Origin',
            'Access-Control-Allow-Methods',
            'Access-Control-Allow-Headers'
        ];
        
        foreach ($headersToRemove as $header) {
            header_remove($header);
        }
    }
}