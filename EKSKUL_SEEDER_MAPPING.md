# EkskulSeeder Update - Mapping Summary

## Updated Data (dengan numeric scale mapping)

| No | Nama | Biaya | Fasilitas | Intensitas | Perubahan |
|---|---|---|---|---|---|
| 1 | Art | 2 (Sedikit Biaya) | 1 (Seluruhnya dibawa sendiri) | 3 (Sedang) | ✅ Biaya & Intensitas |
| 2 | Futsal | 1 (Tidak Ada Biaya) | 5 (Disediakan sekolah) | 2 (Tinggi) | ✓ Sudah benar |
| 3 | Mandarin | 4 (Sedikit Mahal) | 3 (Sebagian-sebagian) | 4 (Rendah) | ✅ Biaya & Intensitas |
| 4 | Monologue | 3 (Terjangkau) | 3 (Sebagian-sebagian) | 3 (Sedang) | ✓ Sudah benar |
| 5 | Badminton | 2 (Sedikit Biaya) | 4 (Lebih banyak sekolah) | 3 (Sedang) | ✅ Biaya & Intensitas |
| 6 | Traditional Dance | 1 (Tidak Ada Biaya) | 5 (Disediakan sekolah) | 2 (Tinggi) | ✓ Sudah benar |
| 7 | Web Programming | 4 (Sedikit Mahal) | 2 (Lebih banyak sendiri) | 3 (Sedang) | ✅ Biaya, Fasilitas & Intensitas |
| 8 | Web Design | 4 (Sedikit Mahal) | 2 (Lebih banyak sendiri) | 3 (Sedang) | ✅ Biaya, Fasilitas & Intensitas |
| 9 | BTQ | 3 (Terjangkau) | 2 (Lebih banyak sendiri) | 4 (Rendah) | ✅ Fasilitas & Intensitas |
| 10 | Karate | 5 (Mahal) | 2 (Lebih banyak sendiri) | 2 (Tinggi) | ✅ Fasilitas |
| 11 | Basketball | 1 (Tidak Ada Biaya) | 5 (Disediakan sekolah) | 2 (Tinggi) | ✓ Sudah benar |
| 12 | English Debate | 1 (Tidak Ada Biaya) | 5 (Disediakan sekolah) | 4 (Rendah) | ✓ Sudah benar |
| 13 | Teather | 3 (Terjangkau) | 2 (Lebih banyak sendiri) | 3 (Sedang) | ✅ Fasilitas & Intensitas |
| 14 | Taekwondo | 5 (Mahal) | 2 (Lebih banyak sendiri) | 2 (Tinggi) | ✅ Fasilitas |

---

## Numeric Scale Reference

### Biaya Tambahan (1-5)
1. **Tidak Ada Biaya** (Rp 0)
2. **Sedikit Biaya** (Rp 1.000 - Rp 100.000)
3. **Terjangkau** (Rp 101.000 - Rp 200.000)
4. **Sedikit Mahal** (Rp 201.000 - Rp 300.000)
5. **Mahal** (Rp 301.000+)

### Fasilitas Level (1-5)
1. **Seluruhnya dibawa sendiri**
2. **Beberapa dari sekolah, lebih banyak dibawa sendiri**
3. **Sebagian disediakan sekolah, sebagian sendiri**
4. **Beberapa dibawa sendiri, lebih banyak disediakan sekolah**
5. **Sepenuhnya disediakan sekolah**

### Intensitas Kegiatan (1-5)
1. **Intensitas Sangat Tinggi**
2. **Intensitas Tinggi**
3. **Intensitas Sedang**
4. **Intensitas Rendah**
5. **Intensitas Sangat Rendah**

---

## File Updated
- `database/seeders/EkskulSeeder.php` - 11 entries updated (entries 1, 3, 5, 7, 8, 9, 10, 13, 14)

## Status
✅ **COMPLETE** - Semua data seeder sudah disesuaikan dengan dropdown options dan numeric mapping.

## Next Step
Run migration & seeding:
```bash
php artisan migrate --no-seed
php artisan db:seed --class=EkskulSeeder
```

Or fresh start:
```bash
php artisan migrate:fresh --seed
```
