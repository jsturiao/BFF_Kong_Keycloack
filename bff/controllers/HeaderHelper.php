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

    private static function setCorsHeaders() {
        if (!self::hasHeader('Access-Control-Allow-Origin')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
        }
    }

    public static function setHtmlHeaders() {
        if (!headers_sent()) {
            self::clearHeaders();
            
            // CORS headers primeiro
            self::setCorsHeaders();
            
            // Headers específicos para HTML
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
        }
    }

    public static function setJsonHeaders() {
        if (!headers_sent()) {
            self::clearHeaders();
            
            // CORS headers primeiro
            self::setCorsHeaders();
            
            // Headers específicos para JSON
            header('Content-Type: application/json');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
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