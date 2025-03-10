<?php
/**
 * Advanced HTTP Scraper Library
 * Author: A$htaroth
 * 
 * A powerful, flexible HTTP client with built-in regex utilities,
 * JSON handling, proxy rotation, rate limiting, and more.
 */

class HttpClient {
    private $curl;
    private $baseUrl = '';
    private $options = [];
    private $headers = [];
    private $response = '';
    private $statusCode = 0;
    private $error = '';
    private $requestDelay = 0;
    private $lastRequestTime = 0;
    private $proxyList = [];
    private $currentProxyIndex = -1;
    private $maxRetries = 3;
    private $debug = false;
    private $logFile = null;

    // User Agent components
    private static $ua = [
        'os' => ['Windows NT 10.0', 'Macintosh; Intel Mac OS X 10_15_7', 'X11; Ubuntu', 'Android 12; SM-S908B'],
        'browser' => ['Chrome/100.0.4896.127', 'Firefox/99.0', 'Safari/605.1.15', 'Edge/100.0.1185.39']
    ];

    /**
     * Constructor
     * 
     * @param string $baseUrl Base URL for all requests
     * @param bool $useRandomUA Use random user agent
     */
    public function __construct($baseUrl = '', $useRandomUA = true) {
        if (!extension_loaded('curl')) throw new Exception('cURL extension required');
        $this->baseUrl = $baseUrl;
        $this->options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FAILONERROR => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => ''
        ];
        if ($useRandomUA) $this->setRandomUserAgent();
    }

    public function enableDebug($logFile = null) {
        $this->debug = true;
        $this->logFile = $logFile;
        if ($logFile) {
            $this->setOption(CURLOPT_VERBOSE, true);
            $this->setOption(CURLOPT_STDERR, fopen($logFile, 'a'));
        }
        return $this;
    }

    public function setRequestDelay($milliseconds) {
        $this->requestDelay = $milliseconds;
        return $this;
    }

    public function addProxy($proxy) {
        $this->proxyList[] = $proxy;
        return $this;
    }

    private function rotateProxy() {
        if (empty($this->proxyList)) return $this;
        for ($i = 0; $i < count($this->proxyList); $i++) {
            $this->currentProxyIndex = ($this->currentProxyIndex + 1) % count($this->proxyList);
            $this->setProxy($this->proxyList[$this->currentProxyIndex]);
            if ($this->testProxy($this->proxyList[$this->currentProxyIndex])) {
                return $this;
            }
        }
        return $this;
    }

    private function testProxy($proxy) {
        $testCurl = curl_init('https://www.google.com');
        curl_setopt_array($testCurl, [
            CURLOPT_PROXY => $proxy,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $result = curl_exec($testCurl);
        curl_close($testCurl);
        return $result !== false;
    }

    private function enforceRateLimit() {
        $currentTime = microtime(true);
        if ($this->lastRequestTime > 0 && $this->requestDelay > 0) {
            $elapsed = ($currentTime - $this->lastRequestTime) * 1000;
            if ($elapsed < $this->requestDelay) {
                usleep(($this->requestDelay - $elapsed) * 1000);
            }
        }
        $this->lastRequestTime = microtime(true);
    }

    public function get($url, $params = []) {
        $url = $this->buildUrl($url, $params);
        return $this->executeRequest($url, 'GET');
    }

    public function post($url, $data = [], $contentType = 'application/x-www-form-urlencoded') {
        $this->setHeader('Content-Type', $contentType);
        return $this->executeRequest($url, 'POST', $data);
    }

    private function buildUrl($url, $params) {
        if ($this->baseUrl) {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    private function executeRequest($url, $method, $data = null) {
        for ($attempt = 0; $attempt < $this->maxRetries; $attempt++) {
            $this->enforceRateLimit();
            $this->curl = curl_init($url);
            curl_setopt_array($this->curl, $this->options);
            if ($method === 'POST') {
                curl_setopt($this->curl, CURLOPT_POST, true);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->formatHeaders());

            $this->response = curl_exec($this->curl);
            $this->statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->error = curl_error($this->curl);

            if ($this->statusCode >= 200 && $this->statusCode < 300) break;
            if ($attempt < $this->maxRetries - 1) {
                $this->rotateProxy();
            }
        }
        curl_close($this->curl);
        return $this;
    }

    private function formatHeaders() {
        return array_map(function($name, $value) {
            return "$name: $value";
        }, array_keys($this->headers), $this->headers);
    }

    public function getResponse() {
        return $this->response;
    }

    public function getLastStatusCode() {
        return $this->statusCode;
    }

    public function getLastError() {
        return $this->error;
    }

    public function setRandomUserAgent() {
        $os = self::$ua['os'][array_rand(self::$ua['os'])];
        $browser = self::$ua['browser'][array_rand(self::$ua['browser'])];
        $this->setHeader('User-Agent', "Mozilla/5.0 ($os) AppleWebKit/537.36 (KHTML, like Gecko) $browser");
    }

    public function setProxy($proxy) {
        $this->setOption(CURLOPT_PROXY, $proxy);
    }

    private function setOption($option, $value) {
        $this->options[$option] = $value;
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
    }
}

class RegexHelper {
    public static function match($pattern, $subject) {
        preg_match($pattern, $subject, $matches);
        return $matches;
    }

    public static function matchAll($pattern, $subject) {
        preg_match_all($pattern, $subject, $matches);
        return $matches[0];
    }

    public static function replace($pattern, $replacement, $subject) {
        return preg_replace($pattern, $replacement, $subject);
    }

    public static function split($pattern, $subject) {
        return preg_split($pattern, $subject);
    }

    public static function test($pattern, $subject) {
        return preg_match($pattern, $subject) === 1;
    }

    public static function escape($subject) {
        return preg_quote($subject, '/');
    }
}
?>
