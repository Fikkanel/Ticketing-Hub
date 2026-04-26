# Panduan Setup CI/CD: Push GitHub & Pull ke VPS

Dokumen ini menjelaskan langkah-langkah untuk membuat sistem otomatisasi (CI/CD) di mana setiap kali Anda menekan `git push` dari komputer Anda (lokal), server VPS Anda akan secara otomatis men-download pembaruan terbaru.

---

## 1. Persiapan Git di Komputer Lokal

Pastikan Anda sudah memiliki repository di akun GitHub Anda (misalnya: `github.com/username-anda/docker-lemp`). Di komputer lokal Anda (tempat Anda ngoding), jalankan:

```bash
# Inisialisasi git jika belum
git init

# Tambahkan remote repository GitHub Anda
git remote add origin https://github.com/username-anda/docker-lemp.git

# Pastikan branch utama bernama 'main'
git branch -M main
```

---

## 2. Membuat Koneksi Aman antara GitHub dan VPS (SSH Key)

Agar GitHub dapat memberi perintah `pull` ke server VPS tanpa dimintai password, kita harus membuat "kunci" digital (SSH Key).

1. **Masuk ke VPS Anda** melalui Terminal/PowerShell:
   ```bash
   ssh root@IP_VPS_ANDA
   ```
2. **Buat Kunci SSH** khusus untuk GitHub:
   ```bash
   ssh-keygen -t rsa -b 4096 -C "github-actions"
   ```
   *(Tekan `Enter` 3x saat ditanya file name dan passphrase agar menggunakan default dan tanpa password).*
3. **Izinkan Kunci Tersebut untuk Login** ke VPS:
   ```bash
   cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
   ```
4. **Tampilkan Private Key** untuk disalin ke GitHub:
   ```bash
   cat ~/.ssh/id_rsa
   ```
   *Blok dan copy semua teks mulai dari `-----BEGIN RSA PRIVATE KEY-----` sampai `-----END RSA PRIVATE KEY-----`.*

---

## 3. Menambahkan "Secrets" di GitHub

Jangan pernah menaruh Private Key langsung di dalam file kode. Gunakan fitur rahasia GitHub (Secrets).

1. Buka halaman Repository GitHub Anda.
2. Klik tab **Settings** (Pengaturan).
3. Di panel sebelah kiri, cari bagian **Security**, klik **Secrets and variables** > **Actions**.
4. Klik tombol hijau **New repository secret**.
5. Tambahkan 3 variabel berikut satu per satu:

   * **Nama:** `VPS_HOST`
     * **Secret:** Isi dengan *IP Address VPS Anda* (contoh: 194.xxx.xxx.xxx)
   * **Nama:** `VPS_USERNAME`
     * **Secret:** Isi dengan *username login VPS* (contoh: `root` atau `ubuntu`)
   * **Nama:** `VPS_SSH_KEY`
     * **Secret:** Paste *Private Key yang panjang* hasil salinan di Langkah 2.

---

## 4. Pastikan File Konfigurasi Workflow Sudah Ada

Sistem otomatisasi GitHub berjalan berdasarkan file berekstensi `.yml`. Saya sudah membuatkan file ini di komputer Anda dengan nama `.github/workflows/deploy.yml`.

Jika Anda ingin melihat isinya atau menyesuaikan path, pastikan isinya seperti ini:

```yaml
name: Deploy to VPS

on:
  push:
    branches:
      - main # Workflow akan berjalan ketika ada push ke branch main

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout Code
      uses: actions/checkout@v3

    - name: Deploy to VPS via SSH
      uses: appleboy/ssh-action@v1.0.0
      with:
        host: ${{ secrets.VPS_HOST }}
        username: ${{ secrets.VPS_USERNAME }}
        key: ${{ secrets.VPS_SSH_KEY }}
        port: 22 
        script: |
          # GANTI BAGIAN INI dengan letak folder proyek Anda di VPS
          cd /var/www/docker-lemp
          
          # Tarik kode terbaru dari GitHub ke VPS
          git pull origin main
          
          # Jika Anda butuh me-restart container, jalankan:
          docker compose down
          docker compose up -d --build
```

> [!IMPORTANT]
> **Penting!** Sebelum ini bisa bekerja, Anda **WAJIB** sudah pernah melakukan `git clone` repository Anda ke dalam VPS secara manual satu kali di folder `/var/www/docker-lemp`. (Silakan lihat `vps_setup_guide.md` yang telah dibuat sebelumnya).

---

## 5. Tes Otomatisasi (Push Kode)

Semuanya sudah siap! Sekarang saatnya menguji dengan mengirim (push) kode terbaru dari komputer Anda ke GitHub.

Jalankan perintah ini di komputer lokal:

```bash
git add .
git commit -m "Setup CI/CD GitHub Actions"
git push -u origin main
```

**Cara Cek Keberhasilan:**
1. Buka Repository GitHub Anda.
2. Klik tab **Actions**.
3. Anda akan melihat proses bernama "Deploy to VPS" sedang berjalan (warna kuning/loading).
4. Jika berhasil, akan berubah menjadi centang hijau ✅. Artinya VPS Anda sudah otomatis ter-update dan container sudah di-restart dengan kode terbaru!
