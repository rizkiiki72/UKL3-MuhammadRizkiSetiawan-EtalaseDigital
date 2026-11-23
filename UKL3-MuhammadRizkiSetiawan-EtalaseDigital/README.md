# UKK Lvl 3 – Etalase Karya Riski (ukk_lvl3_riski)

Tema warna: merah maroon, abu-abu, putih (`assets/css/theme.css`). Logo Telkom Schools (TS) di sidebar, halaman login/registrasi, dan tab browser (`assets/img/logo.png`).

Struktur folder sama dengan proyek `ukk_lvl3`. Tampilan berbeda:
- **Sidebar** maroon di kiri (menjadi menu geser di HP) berisi menu, kartu pengguna, dan logout.
- Header halaman dengan pencarian, **urutkan** (terbaru / terlama / judul A-Z), dan tiga kartu statistik (total karya, siswa berkarya, karya saya).
- Kartu karya bergaya poster: gambar penuh dengan teks di atasnya, deskripsi muncul saat disorot.
- **Pop-up detail karya berbentuk panel samping** dari kanan: tombol Sebelumnya/Berikutnya atau panah kiri/kanan keyboard untuk pindah karya, Esc untuk menutup. Form tambah admin juga memakai panel yang sama.
- Login/registrasi berupa satu kartu dengan logo melingkar di atas latar maroon melengkung.

## Instalasi (XAMPP)
1. Salin folder `ukk_lvl3_riski` ke `htdocs/` (pastikan `index.php` langsung ada di dalamnya).
2. Buka phpMyAdmin → **Import** → pilih `database.sql` (otomatis membuat database `ukk_lvl3_riski`).
3. Cek `config/kuis_db.php` (host/user/password MySQL).
4. Buka `http://localhost/ukk_lvl3_riski/auth/login.php`.

## Akun default (password: `password123`)
| Email | Role |
|---|---|
| superadmin@stella.csh.id | super_admin |
| admin@stella.csh.id | admin |
| siswa@stella.csh.id | siswa |

## Hak akses
| Fitur | Siswa | Admin | Super Admin |
|---|:-:|:-:|:-:|
| Lihat galeri, tambah project, pop-up detail | ✔ | ✔ | ✔ |
| Edit/hapus project | milik sendiri | semua | semua |
| Kelola Siswa (`admin/siswa.php`) | – | ✔ | ✔ |
| Kelola User + tambah admin (`admin/users.php`) | – | – | ✔ |

Reset password siswa oleh admin menjadi `654321` (ubah di `DEFAULT_RESET_PASSWORD`, `middleware/auth_check.php`).
