# Panduan Upload ke GitHub Manual

Berikut adalah langkah-langkah untuk meng-upload (push) source code aplikasi ini ke repository GitHub Anda secara manual melalui terminal.

## Prasyarat
1.  Pastikan **Git** sudah terinstall di komputer Anda. (Ketik `git --version` di terminal untuk cek).
2.  Anda sudah memiliki akun GitHub dan sudah membuat **Repository Baru** (kosong).

---

## Langkah-Langkah

### 1. Buka Terminal
Buka terminal di VS Code (Ctrl + `) atau Command Prompt, pastikan Anda berada di folder project:
`d:\fariz\folder download\accesscontrol-farizahmad.github.io\accesscontrol-farizahmad.github.io`

### 2. Inisiasi Git (Jika belum pernah)
Jika folder ini belum ada `.git` folder-nya, jalankan:
```bash
git init
```

### 3. Cek Status File
Lihat file apa saja yang berubah atau baru:
```bash
git status
```
*File yang berwarna merah artinya belum masuk antrian upload.*

### 4. Masukkan File ke Antrian (Stage)
Masukkan semua file (kecuali yang ada di .gitignore) ke area staging:
```bash
git add .
```

### 5. Simpan Perubahan (Commit)
Berikan label pada penyimpanan kali ini:
```bash
git commit -m "Update fitur API Devices dan perbaikan Modal"
```

### 6. Atur Cabang Utama (Branch)
Pastikan kita bekerja di cabang utama (biasanya `main`):
```bash
git branch -M main
```

### 7. Sambungkan ke GitHub (Remote)
**Hanya lakukan ini jika INI PERTAMA KALI.** Jika sudah pernah disambungkan sebelumnya, lewati langkah ini.
Ganti URL di bawah dengan URL repository GitHub Anda yang sebenarnya:
```bash
git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git
```
*(Contoh: `https://github.com/farizahmad/accesscontrol.git`)*

Jika muncul error "remote origin already exists", artinya sudah tersambung. Anda bisa mengeceknya dengan `git remote -v`.

### 8. Upload ke GitHub (Push)
Kirim data ke server GitHub:
```bash
git push -u origin main
```

---

## Kemungkinan Masalah (Troubleshooting)

### A. Diminta Password tapi Gagal?
Sejak 2021, GitHub tidak lagi menerima password akun biasa untuk terminal. Anda harus menggunakan **Personal Access Token (PAT)**.
1.  Buka GitHub > Settings > Developer Settings > Personal Access Tokens > Tokens (classic).
2.  Generate New Token (pilih scope `repo`).
3.  Copy token tersebut (diawali `ghp_...`).
4.  Saat terminal tanya password, **paste token ini** (password tidak akan muncul di layar saat diketik/paste, langsung Enter saja).

### B. Error "failed to push some refs"?
Artinya di GitHub ada file yang belum Anda miliki di komputer (biasanya README.md yang dibuat otomatis).
Solusinya, ambil dulu data dari GitHub:
```bash
git pull origin main --rebase
```
Baru coba push lagi.

### C. Ingin Mengganti URL Remote?
Jika salah memasukkan URL repository:
```bash
git remote set-url origin https://url-baru-yang-benar.git
```
