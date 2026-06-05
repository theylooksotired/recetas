<?php
/**
 * @class GoogleSearchConsole
 *
 * Utility methods to fetch Search Console data and render a basic HTML table.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class GoogleSearchConsole extends Controller
{

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode((string) $data), '+/', '-_'), '=');
    }

    private static function normalizePrivateKey($privateKey)
    {
        $privateKey = trim((string) $privateKey);
        if ($privateKey == '') {
            return '';
        }
        return str_replace(["\\r\\n", "\\n", "\\r"], ["\n", "\n", "\n"], $privateKey);
    }

    private static function getServiceAccountAccessToken()
    {
        $serviceAccountEmail = self::getParameterFirst([
            'google_search_console_service_account_email',
            'gsc_service_account_email'
        ]);
        $privateKeyRaw = self::getParameterFirst([
            'google_search_console_service_account_private_key',
            'gsc_service_account_private_key'
        ]);
        $privateKeyId = self::getParameterFirst([
            'google_search_console_service_account_private_key_id',
            'gsc_service_account_private_key_id'
        ]);

        if ($serviceAccountEmail == '' || $privateKeyRaw == '') {
            return ['skip' => true];
        }

        $privateKey = self::normalizePrivateKey($privateKeyRaw);
        $iat = time();
        $exp = $iat + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        if ($privateKeyId != '') {
            $header['kid'] = $privateKeyId;
        }

        $claims = [
            'iss' => $serviceAccountEmail,
            'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $iat,
            'exp' => $exp
        ];

        $jwtHeader = self::base64UrlEncode(json_encode($header));
        $jwtClaims = self::base64UrlEncode(json_encode($claims));
        $jwtSigningInput = $jwtHeader . '.' . $jwtClaims;

        $signature = '';
        $signed = openssl_sign($jwtSigningInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$signed) {
            return ['error' => 'No se pudo firmar el JWT con la private key del Service Account.'];
        }

        $jwt = $jwtSigningInput . '.' . self::base64UrlEncode($signature);
        $tokenRequest = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        $curl = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $tokenRequest,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        $responseRaw = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError != '') {
            return ['error' => 'Error de conexion al obtener token con Service Account: ' . $curlError];
        }

        $response = json_decode((string) $responseRaw, true);
        if ($httpCode < 200 || $httpCode >= 300 || !isset($response['access_token'])) {
            $errorMessage = (isset($response['error_description'])) ? $response['error_description'] : 'No se pudo obtener access_token con Service Account.';
            if (isset($response['error']) && $errorMessage == '') {
                $errorMessage = $response['error'];
            }
            return ['error' => 'No se pudo autenticar con Service Account: ' . $errorMessage];
        }

        return ['access_token' => $response['access_token']];
    }

    private static function normalizeSiteUrl($siteUrl)
    {
        $siteUrl = trim((string) $siteUrl);
        if ($siteUrl == '') {
            return '';
        }
        if (strpos($siteUrl, 'sc-domain:') === 0) {
            return strtolower($siteUrl);
        }
        return rtrim(strtolower($siteUrl), '/') . '/';
    }

    private static function request($url, $accessToken, $payload = null)
    {
        $curl = curl_init($url);
        $headers = ['Authorization: Bearer ' . $accessToken];
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $responseRaw = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError != '') {
            return ['error' => $curlError, 'httpCode' => $httpCode, 'response' => []];
        }

        return [
            'httpCode' => $httpCode,
            'response' => json_decode((string) $responseRaw, true)
        ];
    }

    private static function getAccessibleSites($accessToken)
    {
        $request = self::request('https://searchconsole.googleapis.com/webmasters/v3/sites', $accessToken);
        if (isset($request['error'])) {
            return ['error' => 'Error al consultar propiedades de Search Console: ' . $request['error']];
        }

        if ($request['httpCode'] < 200 || $request['httpCode'] >= 300) {
            $message = 'No se pudo cargar la lista de propiedades de Search Console.';
            if (isset($request['response']['error']['message'])) {
                $message = $request['response']['error']['message'];
            }
            return ['error' => $message];
        }

        $entries = (isset($request['response']['siteEntry']) && is_array($request['response']['siteEntry'])) ? $request['response']['siteEntry'] : [];
        $sites = [];
        foreach ($entries as $entry) {
            if (isset($entry['siteUrl']) && trim((string) $entry['siteUrl']) != '') {
                $sites[] = trim((string) $entry['siteUrl']);
            }
        }
        return ['sites' => $sites];
    }

    private static function resolveSiteUrl($accessToken, $recipeUrl)
    {
        $configuredSiteUrl = self::getParameterFirst([
            'google_search_console_site_url',
            'gsc_site_url'
        ]);
        $sitesResult = self::getAccessibleSites($accessToken);
        if (isset($sitesResult['error'])) {
            if ($configuredSiteUrl != '') {
                return ['siteUrl' => $configuredSiteUrl];
            }
            return ['error' => $sitesResult['error']];
        }

        $sites = $sitesResult['sites'];
        $normalizedMap = [];
        foreach ($sites as $site) {
            $normalizedMap[self::normalizeSiteUrl($site)] = $site;
        }

        $candidates = [];
        if ($configuredSiteUrl != '') {
            $candidates[] = $configuredSiteUrl;
        }

        $host = (string) parse_url($recipeUrl, PHP_URL_HOST);
        if ($host != '') {
            $hostLower = strtolower($host);
            $hostNoWww = preg_replace('/^www\./', '', $hostLower);
            $candidates[] = 'https://' . $hostLower . '/';
            $candidates[] = 'http://' . $hostLower . '/';
            $candidates[] = 'https://www.' . $hostNoWww . '/';
            $candidates[] = 'https://' . $hostNoWww . '/';
            $candidates[] = 'sc-domain:' . $hostNoWww;
        }

        foreach ($candidates as $candidate) {
            $normalizedCandidate = self::normalizeSiteUrl($candidate);
            if (isset($normalizedMap[$normalizedCandidate])) {
                return ['siteUrl' => $normalizedMap[$normalizedCandidate]];
            }
        }

        if (!empty($sites)) {
            $available = implode(', ', $sites);
            return [
                'error' => 'No se encontro una propiedad accesible que coincida con la URL de la receta. Configura google_search_console_site_url con una de estas propiedades: ' . $available
            ];
        }

        return ['error' => 'La cuenta autenticada no tiene propiedades accesibles en Search Console.'];
    }

    private static function getParameterFirst($codes)
    {
        foreach ($codes as $code) {
            $value = trim((string) Parameter::code($code));
            if ($value != '') {
                return $value;
            }
        }
        return '';
    }

    private static function getAccessToken()
    {
        $serviceAccountResult = self::getServiceAccountAccessToken();
        if (!isset($serviceAccountResult['skip'])) {
            return $serviceAccountResult;
        }

        $accessToken = self::getParameterFirst([
            'google_search_console_access_token',
            'gsc_access_token'
        ]);
        if ($accessToken != '') {
            return ['access_token' => $accessToken];
        }

        $clientId = self::getParameterFirst([
            'google_search_console_client_id',
            'google_client_id',
            'gsc_client_id'
        ]);
        $clientSecret = self::getParameterFirst([
            'google_search_console_client_secret',
            'google_client_secret',
            'gsc_client_secret'
        ]);
        $refreshToken = self::getParameterFirst([
            'google_search_console_refresh_token',
            'google_refresh_token',
            'gsc_refresh_token'
        ]);

        if ($clientId == '' || $clientSecret == '' || $refreshToken == '') {
            return [
                'error' => 'Faltan credenciales de Google Search Console. Configura: google_search_console_client_id, google_search_console_client_secret y google_search_console_refresh_token.'
            ];
        }

        $tokenRequest = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]);

        $curl = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $tokenRequest,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        $responseRaw = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError != '') {
            return ['error' => 'Error de conexion al obtener token de Google: ' . $curlError];
        }

        $response = json_decode((string) $responseRaw, true);
        if ($httpCode < 200 || $httpCode >= 300 || !isset($response['access_token'])) {
            $errorMessage = (isset($response['error_description'])) ? $response['error_description'] : 'No se pudo obtener access_token.';
            return ['error' => 'No se pudo autenticar con Google: ' . $errorMessage];
        }

        return ['access_token' => $response['access_token']];
    }

    public static function renderTable($rows, $recipeUrl)
    {
        $html = '';
        $html .= '<h3>Top queries (ultimos 30 dias)</h3>';
        $html .= '<p><strong>URL:</strong> <a href="' . htmlspecialchars($recipeUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank">' . htmlspecialchars($recipeUrl, ENT_QUOTES, 'UTF-8') . '</a></p>';
        $html .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:1000px">';
        $html .= '<thead><tr><th>Query</th><th>Clicks</th><th>Impressions</th><th>CTR</th><th>Position</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $query = (isset($row['keys'][0])) ? $row['keys'][0] : '';
            $clicks = (isset($row['clicks'])) ? (float) $row['clicks'] : 0;
            $impressions = (isset($row['impressions'])) ? (float) $row['impressions'] : 0;
            $ctr = (isset($row['ctr'])) ? ((float) $row['ctr'] * 100) : 0;
            $position = (isset($row['position'])) ? (float) $row['position'] : 0;
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . number_format($clicks, 0, '.', ',') . '</td>';
            $html .= '<td>' . number_format($impressions, 0, '.', ',') . '</td>';
            $html .= '<td>' . number_format($ctr, 2, '.', ',') . '%</td>';
            $html .= '<td>' . number_format($position, 2, '.', ',') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    public static function loadRows($recipeUrl)
    {
        $tokenInfo = self::getAccessToken();
        if (isset($tokenInfo['error'])) {
            return ['error' => $tokenInfo['error']];
        }

        $resolvedSite = self::resolveSiteUrl($tokenInfo['access_token'], $recipeUrl);
        if (isset($resolvedSite['error'])) {
            return ['error' => $resolvedSite['error']];
        }
        $siteUrl = $resolvedSite['siteUrl'];

        $path = (string) parse_url($recipeUrl, PHP_URL_PATH);
        if ($path == '') {
            $path = '/';
        }

        $payload = [
            'startDate' => date('Y-m-d', strtotime('-30 days')),
            'endDate' => date('Y-m-d', strtotime('-1 day')),
            'dimensions' => ['query'],
            'rowLimit' => 25,
            'dimensionFilterGroups' => [[
                'filters' => [[
                    'dimension' => 'page',
                    'operator' => 'contains',
                    'expression' => $path
                ]]
            ]]
        ];

        $apiUrl = 'https://searchconsole.googleapis.com/webmasters/v3/sites/' . rawurlencode($siteUrl) . '/searchAnalytics/query';
        $request = self::request($apiUrl, $tokenInfo['access_token'], $payload);
        if (isset($request['error'])) {
            return ['error' => 'Error de conexion con Search Console: ' . $request['error']];
        }

        $response = $request['response'];
        if ($request['httpCode'] < 200 || $request['httpCode'] >= 300) {
            $errorMessage = 'No se pudo consultar Search Console.';
            if (isset($response['error']['message'])) {
                $errorMessage = $response['error']['message'];
            }
            return ['error' => $errorMessage . ' Site URL usado: ' . $siteUrl];
        }

        return ['rows' => (isset($response['rows']) && is_array($response['rows'])) ? $response['rows'] : []];
    }

}
