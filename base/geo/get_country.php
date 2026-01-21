<?php
/**
 * GeoIP Country Detection
 * Uses GeoLite2-Country.mmdb to detect visitor's country
 * 
 * For PHP 7.0, install with:
 * composer require "geoip2/geoip2:~2.0"
 * 
 * Version 2.x supports PHP 7.0+
 */
require_once 'vendor/autoload.php';

use GeoIp2\Database\Reader;

class GeoCountry {
    
    private $reader;
    private $dbPath;
    
    public function __construct($dbPath = null) {
        $this->dbPath = is_null($dbPath) ? __DIR__ . '/GeoLite2-Country.mmdb' : $dbPath;
        
        if (!file_exists($this->dbPath)) {
            throw new Exception("GeoLite2 database not found at: " . $this->dbPath);
        }
        
        $this->reader = new Reader($this->dbPath);
    }
    
    /**
     * Get visitor's IP address
     * Handles proxy and forwarded IPs
     */
    public function getVisitorIP() {
        $ip = null;
        
        // Check for forwarded IP (behind proxy/load balancer)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    /**
     * Get country information for an IP address
     * 
     * @param string $ip IP address to lookup (optional, defaults to visitor IP)
     * @return array Country information or null if not found
     */
    public function getCountry($ip = null) {
        try {
            if ($ip === null) {
                $ip = $this->getVisitorIP();
            }
            
            // Skip local/private IPs
            if ($this->isPrivateIP($ip)) {
                return array(
                    'success' => false,
                    'ip' => $ip,
                    'error' => 'Private or local IP address'
                );
            }
            
            $record = $this->reader->country($ip);
            
            return array(
                'success' => true,
                'ip' => $ip,
                'country_code' => $record->country->isoCode,
                'country_name' => $record->country->name,
                'country_names' => $record->country->names, // All language translations
                'continent_code' => $record->continent->code,
                'continent_name' => $record->continent->name
            );
            
        } catch (GeoIp2\Exception\AddressNotFoundException $e) {
            return array(
                'success' => false,
                'ip' => $ip,
                'error' => 'Address not found in database'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'ip' => $ip,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Get the country code or an empty string if not found
     */
    public function getCountryCodeOrEmpty($ip = null) {
        $result = $this->getCountry($ip);
        return (isset($result['success']) && $result['success']) ? $result['country_code'] : '';
    }
    
    /**
     * Check if IP is private/local
     */
    private function isPrivateIP($ip) {
        // Check for localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }
        
        // Check for private ranges
        $privateRanges = array(
            '10.0.0.0|10.255.255.255',
            '172.16.0.0|172.31.255.255',
            '192.168.0.0|192.168.255.255'
        );
        
        $longIp = ip2long($ip);
        if ($longIp !== false) {
            foreach ($privateRanges as $range) {
                list($start, $end) = explode('|', $range);
                if ($longIp >= ip2long($start) && $longIp <= ip2long($end)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Simple usage: just get the country code
     */
    public function getCountryCode($ip = null) {
        $result = $this->getCountry($ip);
        return $result['success'] ? $result['country_code'] : null;
    }
    
    /**
     * Simple usage: just get the country name
     */
    public function getCountryName($ip = null) {
        $result = $this->getCountry($ip);
        return $result['success'] ? $result['country_name'] : null;
    }
    
    public function __destruct() {
        if ($this->reader) {
            $this->reader->close();
        }
    }
}
