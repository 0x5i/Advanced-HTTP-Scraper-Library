# Advanced HTTP Scraper Library  

## 📌 Deskripsi  
Advanced HTTP Scraper Library adalah pustaka PHP yang fleksibel untuk melakukan scraping data dengan fitur:  
- HTTP client berbasis cURL  
- Rotasi **User-Agent** dan **Proxy**  
- Batasan kecepatan request (Rate Limiting)  
- **Retry Mechanism** untuk menangani error  
- **Debugging ke log file**  
- Bantuan **Regex Utility**  

## 🚀 Instalasi  
Pastikan PHP Anda memiliki ekstensi **cURL** aktif.  

1. **Clone repositori ini:**  
   ```sh
   git clone https://github.com/YouJerk/Advanced-HTTP-Scraper-Library/
   cd Advanced-HTTP-Scraper-Library
   ```

2. **Gunakan dalam proyek PHP:**  
   ```php
   require 'HttpClient.php';

   $client = new HttpClient();
   ```

## 🔧 Penggunaan  

### 1️⃣ **Menggunakan HTTP Client**  

#### 🔹 Melakukan Request GET  
```php
$client = new HttpClient();
$response = $client->get('https://example.com')->getResponse();
echo $response;
```

#### 🔹 Melakukan Request POST  
```php
$data = ['username' => 'admin', 'password' => 'secret'];
$response = $client->post('https://example.com/login', $data)->getResponse();
echo $response;
```

#### 🔹 Mengatur Header Custom  
```php
$client->setHeader('Authorization', 'Bearer TOKEN_ABC123');
```

#### 🔹 Menggunakan Proxy  
```php
$client->addProxy('http://proxyserver:8080')->rotateProxy();
```

#### 🔹 Menyetel User-Agent Acak  
```php
$client->setRandomUserAgent();
```

#### 🔹 Menetapkan Waktu Jeda Antar Request  
```php
$client->setRequestDelay(1000); // 1000 ms (1 detik)
```

#### 🔹 Debugging & Logging  
```php
$client->enableDebug('debug.log');
```

---

### 2️⃣ **Menggunakan Regex Utility**  
```php
require 'RegexHelper.php';

$text = "Email saya adalah example@mail.com";
$pattern = "/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i";

$result = RegexHelper::match($pattern, $text);
print_r($result);
```

## 📜 Fitur Lengkap  
| Fitur                   | Status  |
|-------------------------|---------|
| HTTP Client             | ✅ Selesai |
| Rotasi Proxy            | ✅ Selesai |
| User-Agent Acak         | ✅ Selesai |
| Rate Limiting           | ✅ Selesai |
| Retry Mechanism         | ✅ Selesai |
| Debugging & Logging     | ✅ Selesai |
| Regex Utility           | ✅ Selesai |
| Cookie & Custom Header  | 🔜 Akan ditambahkan |

## 🛠️ Lisensi  
Kode ini tersedia di bawah lisensi **MIT**.  

## 🤝 Kontribusi  
Pull Request dan perbaikan sangat diterima! Jika ada bug, silakan buat **Issue**.  

---

Dokumentasi ini cocok untuk GitHub dengan format **terstruktur dan mudah dipahami**.
