# Changelog

## Format

- **Tanggal**: YYYY-MM-DD
- **Dokumen Terdampak**: Nama file yang diubah
- **Trigger**: Alasan perubahan (gap analysis, bug fix, requirement change, dll)
- **Perubahan**: Deskripsi perubahan yang dilakukan
- **Alasan**: Penjelasan mengapa perubahan diperlukan

---

## [2026-06-21] - Gap Analysis & PRD Enhancement

- **Tanggal**: 2026-06-21
- **Dokumen Terdampak**: .ai/specs/01-prd.md
- **Trigger**: Gap analysis menyeluruh terhadap PRD v1.0 untuk mengidentifikasi kontradiksi, user story yang terlewat, dan edge case yang belum dipikirkan
- **Perubahan**:
  1. Menambahkan validasi ulang stok saat approval transaksi (US-009) untuk mencegah race condition dan stok negatif
  2. Menambahkan penanganan concurrent approval dengan database transaction dan row locking
  3. Menambahkan user story baru "Transaksi Saya" untuk Staff/Kasir (US-026i)
  4. Menambahkan visibility approval history untuk creator transaksi (US-023)
  5. Menambahkan aturan self-approval untuk stock adjustment (US-008)
  6. Menambahkan export ke laporan stok saat ini dan low stock (US-016, US-018)
  7. Menambahkan fitur "Remember Me" pada login (US-011)
  8. Menambahkan filter status (approved/pending/rejected) di laporan mutasi (US-017)
  9. Menambahkan edge case untuk concurrent approval dengan stok terbatas (EC-16)
  10. Menambahkan validasi supplier pada form stok masuk jika produk belum memiliki supplier
  11. Menambahkan perhitungan selisih otomatis pada stock adjustment (US-008)
  12. Menambahkan filter "Hari Ini/Minggu Ini/Bulan Ini" di halaman transaksi
- **Alasan**:
  - Mencegah bug stok negatif akibat race condition pada concurrent approval
  - Memperbaiki kontradiksi antara schema database dan user story stock adjustment
  - Meningkatkan visibility dan tracking untuk Staff/Kasir
  - Memperbaiki gap pada export laporan yang belum mencakup semua jenis laporan
  - Meningkatkan usability dengan fitur remember me dan filter waktu
  - Mengatasi edge case yang berisiko tinggi (stok negatif, concurrent transaction)

---

_Dokumen ini dibuat untuk mencatat semua perubahan signifikan pada spesifikasi produk._
