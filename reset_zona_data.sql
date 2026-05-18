-- Script untuk reset dan hitung ulang zona data lama
-- Jalankan: mysql -u root -p ekskul_sma < reset_zona_data.sql

-- Step 1: Reset semua status_zona ke NULL untuk periode dengan tahun ajaran aktif
UPDATE pilihan_ekskul pe
INNER JOIN pendaftaran_siswa ps ON pe.pendaftaran_id = ps.pendaftaran_id
INNER JOIN periode_pendaftaran pp ON ps.periode_id = pp.periode_id
INNER JOIN tahun_ajaran ta ON pp.tahun_ajaran_id = ta.tahun_ajaran_id
SET pe.status_zona = NULL
WHERE ta.is_active = 1
  AND pe.is_deleted = 0;

-- Output hasil
SELECT
    ta.label as 'Tahun Ajaran',
    pp.semester as 'Semester',
    COUNT(DISTINCT pe.pilihan_id) as 'Total Pilihan Direset'
FROM pilihan_ekskul pe
INNER JOIN pendaftaran_siswa ps ON pe.pendaftaran_id = ps.pendaftaran_id
INNER JOIN periode_pendaftaran pp ON ps.periode_id = pp.periode_id
INNER JOIN tahun_ajaran ta ON pp.tahun_ajaran_id = ta.tahun_ajaran_id
WHERE ta.is_active = 1
  AND pe.is_deleted = 0
  AND pe.status_zona IS NULL
GROUP BY ta.tahun_ajaran_id, pp.semester;
