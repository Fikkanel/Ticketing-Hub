# Panduan Setup VPS Baru & Deploy LEMP (via Docker)

Karena proyek Anda (`docker-lemp`) sudah menggunakan Docker, praktik terbaiknya adalah **tidak** menginstal Nginx, PHP, dan MySQL secara manual langsung di VPS. Sebaliknya, kita hanya perlu menginstal **Docker dan Docker Compose** di VPS Anda. Docker akan secara otomatis menjalankan Nginx, PHP, dan MySQL sesuai dengan konfigurasi yang sudah ada di `docker-compose.yml` Anda.

Panduan ini diasumsikan menggunakan OS **Ubuntu 22.04 / 24.04** pada VPS Anda.

---

## 1. Akses VPS & Update Sistem

Login ke VPS Anda menggunakan SSH melalui terminal (Command Prompt/PowerShell):
```bash
ssh root@IP_VPS_ANDA
```

Setelah berhasil masuk, perbarui daftar paket sistem agar mendapatkan versi terbaru:
```bash
apt update && apt upgrade -y
```

> [!TIP]
> **Menambah Swap (Opsional tapi Direkomendasikan)**
> Jika VPS Anda memiliki RAM 1GB - 2GB, disarankan untuk menambah *Swap file* agar MySQL atau Composer tidak crash saat kehabisan memori.
> ```bash
> fallocate -l 1G /swapfile
> chmod 600 /swapfile
> mkswap /swapfile
> swapon /swapfile
> echo '/swapfile none swap sw 0 0' | tee -a /etc/fstab
> ```

---

## 2. Instalasi Docker & Docker Compose

Jalankan perintah berikut satu per satu untuk menginstal Docker:

```bash
# Hapus versi lama (jika ada)
apt-get remove docker docker-engine docker.io containerd runc

# Instal dependensi dasar
apt-get install -y ca-certificates curl gnupg lsb-release

# Tambahkan GPG Key resmi Docker
mkdir -m 0755 -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Tambahkan repository Docker
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instal Docker Engine dan Docker Compose
apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Cek apakah Docker sudah terinstal dengan benar:
```bash
docker --version
docker compose version
```

---

## 3. Persiapan SSH Key untuk GitHub Actions (CI/CD)

Agar GitHub bisa masuk ke VPS untuk otomatisasi deployment, buat SSH key:

```bash
ssh-keygen -t rsa -b 4096 -C "github-actions"
```
*(Tekan `Enter` terus pada setiap pertanyaan untuk lokasi default dan tanpa password/passphrase).*

Masukkan kunci Publik ke daftar kunci yang diizinkan (Authorized keys):
```bash
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
```

Tampilkan Kunci Privat (Private Key) Anda untuk disalin ke GitHub:
```bash
cat ~/.ssh/id_rsa
```
> [!IMPORTANT]
> Copy dari `-----BEGIN RSA PRIVATE KEY-----` sampai `-----END RSA PRIVATE KEY-----` lalu masukkan ke menu **Settings > Secrets and variables > Actions** di GitHub dengan nama `VPS_SSH_KEY`. (Jangan lupa juga buat secret `VPS_HOST` dan `VPS_USERNAME`).

---

## 4. Mengambil Kode Proyek (Clone Git)

Arahkan ke direktori publik `/var/www/` dan clone repo GitHub Anda:

```bash
mkdir -p /var/www
cd /var/www

# Jika repository Anda PRIVATE, gunakan personal access token atau konfigurasi deploy key.
# Jika PUBLIC, langsung saja:
git clone https://github.com/username-anda/nama-repo-anda.git docker-lemp

cd docker-lemp
```

---

## 5. Menjalankan Docker LEMP Stack

Sekarang, kita jalankan aplikasi Anda menggunakan konfigurasi yang sudah ada.

```bash
# Beri akses eksekusi dan permissions pada src (jika diperlukan oleh container)
chmod -R 775 src/

# Jalankan Docker Compose di background
docker compose up -d --build
```

### Konfigurasi Laravel Pertama Kali (Jika Ada)
Karena ini adalah instalasi baru, Anda perlu menginstal dependensi (vendor) untuk proyek Laravel (TixKita / API) yang ada di dalam `src/`. Masuk ke container PHP Anda dan jalankan Composer:

```bash
# Ganti 'php' dengan nama container PHP Anda jika berbeda (cek dengan `docker ps`)
docker compose exec php bash

# Sekarang Anda berada di dalam container PHP, navigasi ke direktori laravel:
cd src/tixkita.id  # atau src/pemrogaman-api

# Instal dependensi composer
composer install --optimize-autoloader --no-dev

# Setup .env dan App Key
cp .env.example .env
php artisan key:generate

# Migrasi Database
php artisan migrate --seed

# Optimasi konfigurasi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Keluar dari container
exit
```

---

## 6. Selesai!

VPS Anda sekarang sudah menjalankan Docker LEMP Stack.
Setiap kali Anda menekan `git push` dari laptop lokal Anda, script GitHub Actions yang sebelumnya dibuat akan otomatis masuk ke `/var/www/docker-lemp` dan menarik pembaruan terbaru, serta merestart container jika diperlukan.

> [!WARNING]
> **Firewall (UFW)**
> Jika Anda menggunakan Cloud Provider seperti DigitalOcean atau AWS, pastikan **Port 80 (HTTP)**, **Port 443 (HTTPS)**, dan **Port 22 (SSH)** sudah terbuka di setting firewall VPS Anda agar website bisa diakses dari internet.
