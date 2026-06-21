# Kumpulan Prompt Siap Pakai — Alur SDD Laravel

> Gunakan prompt ini secara berurutan sesuai fase. Ganti teks dalam `[kurung siku]`
> dengan kebutuhan proyekmu. Prompt ini diasumsikan dipakai di Claude Code, Cursor,
> Gemini CLI, atau tool sejenis yang punya akses baca/tulis file di proyekmu.

---

## FASE 1 — Project Brief

### Prompt 1.1: Generate Project Brief dari ide mentah

```
Saya mau bikin aplikasi web Laravel: [jelaskan idemu 2-3 kalimat, bebas, tidak perlu rapi].

Tolong bantu saya susun jadi Project Brief menggunakan format di
.ai/specs/00-project-brief.md (kalau belum ada filenya, gunakan struktur:
Ringkasan, Masalah yang Diselesaikan, Target User, Tech Stack, Constraint,
Definisi Selesai untuk MVP, Out of Scope).

Untuk Tech Stack, asumsikan: Laravel terbaru, MySQL, [Livewire/Inertia+Vue/Inertia+React],
Tailwind CSS — kecuali saya sebutkan lain.

Jangan asumsikan fitur yang tidak saya sebutkan. Jika ada bagian yang ambigu,
tandai dengan [PERLU KONFIRMASI] daripada menebak.
```

### Prompt 1.2: Review & pertajam brief yang sudah ada

```
Baca .ai/specs/00-project-brief.md.

Review brief ini sebagai jika kamu adalah product manager berpengalaman.
Identifikasi:
1. Bagian yang masih ambigu atau bisa ditafsirkan berbeda
2. Asumsi tersembunyi yang sebaiknya dieksplisitkan
3. Fitur yang kemungkinan "wajib ada" tapi belum disebut di Out of Scope atau Goals

Jangan langsung ubah filenya — tampilkan dulu temuanmu dalam bentuk list,
saya akan putuskan mana yang perlu diperbaiki.
```

---

## FASE 2 — PRD

### Prompt 2.1: Generate PRD dari Project Brief

```
Baca .ai/specs/00-project-brief.md.

Kembangkan menjadi PRD lengkap di .ai/specs/01-prd.md mengikuti struktur:
Overview, Goals & Non-Goals, User Roles & Permissions, Data Model (ringkasan),
Fitur & User Stories (dengan Acceptance Criteria yang testable), Alur Kerja Utama,
Spesifikasi Teknis, Non-Functional Requirements, Risiko & Edge Cases.

Aturan penting:
- Setiap Non-Goal harus dinyatakan POSITIF dan SPESIFIK, contoh: "Sistem TIDAK
  boleh mengimplementasikan multi-bahasa di fase ini" — bukan cuma diam/tidak disebut
- Setiap Acceptance Criteria harus bisa diverifikasi pass/fail, hindari kalimat abstrak
  seperti "UI harus user-friendly"
- Jangan tambahkan fitur yang tidak ada di project brief, meskipun lazim ada di
  aplikasi sejenis — kalau menurutmu suatu fitur penting tapi belum disebut,
  tulis sebagai catatan terpisah, jangan langsung masukkan ke PRD
```

### Prompt 2.2: Minta agent cari celah di PRD (gap analysis)

```
Baca .ai/specs/01-prd.md.

Lakukan gap analysis: cari user story atau flow yang terlewat, edge case yang
belum dipikirkan, dan kontradiksi antar section. Khusus untuk fitur [nama fitur],
pikirkan skenario: apa yang terjadi kalau [kondisi tidak biasa, misal: stok produk
0 saat checkout bersamaan dua user].

Sajikan dalam bentuk tabel: Section | Masalah | Saran Perbaikan.
Jangan edit PRD dulu, tunggu konfirmasi saya.
```

### Prompt 2.3: Update PRD setelah ada keputusan baru

```
Kita perlu update .ai/specs/01-prd.md: [jelaskan perubahan, misal: "fitur payment
gateway dipindah dari MVP ke fase 2, MVP cukup status order manual"].

Update section yang relevan, lalu catat perubahan ini di .ai/specs/changelog.md
dengan format: tanggal, dokumen terdampak, trigger, perubahan, alasan.
```

---

## FASE 3 — Architecture / Technical Plan

### Prompt 3.1: Generate Architecture Plan dari PRD

```
Baca .ai/specs/01-prd.md secara lengkap.

Buatkan Architecture Plan di .ai/plans/architecture.md dengan struktur:
Struktur Folder Proyek, ERD lengkap (tabel + kolom + tipe data + relasi),
Daftar Model & tanggung jawabnya, API/Route Design, Service & Business Logic Layer,
Package pihak ketiga yang dibutuhkan, Strategi Testing, Security Checklist.

Untuk setiap keputusan arsitektur non-trivial (misal: pilih Service Layer vs
Repository Pattern), jelaskan alasannya di section "Keputusan Teknis & Alasannya"
beserta alternatif yang dipertimbangkan dan kenapa ditolak.

Ikuti Laravel best practice: migration pakai foreignId()->constrained(),
model pakai $fillable eksplisit, business logic tidak boleh ditaruh di Controller.
```

### Prompt 3.2: Review arsitektur — Plan Mode review

```
Baca .ai/plans/architecture.md yang baru dibuat.

Sebagai senior Laravel engineer, review rencana ini khusus untuk:
1. Skalabilitas — bagian mana yang akan jadi bottleneck kalau data tumbuh 10x?
2. Security — apakah ada risiko mass assignment, N+1 query, atau missing
   authorization yang belum diantisipasi?
3. Testing strategy — apakah cukup untuk menangkap regresi di fitur kritis
   seperti [sebutkan fitur kritis, misal: checkout]?

Jangan revisi file dulu. Tampilkan temuan sebagai list prioritas
(Critical / Should Fix / Nice to Have), saya akan putuskan mana yang dieksekusi.
```

### Prompt 3.3: Catat keputusan teknis baru saat development

```
Saya baru putuskan: [jelaskan keputusan teknis, misal: "pakai database queue
karena hosting belum support Redis"].

Catat ini di .ai/plans/tech-decisions.md dengan format: tanggal, konteks,
opsi yang dipertimbangkan, keputusan, alasan, dampak ke task lain (jika ada).

Kalau keputusan ini berdampak ke task file yang sudah ada di .ai/tasks/,
sebutkan task mana yang perlu diupdate dan apa yang perlu diubah — tapi
jangan edit task file-nya dulu, tunggu konfirmasi saya.
```

---

## FASE 4 — Pecah jadi Tasks per Fase

### Prompt 4.1: Generate semua task file dari architecture plan

```
Baca .ai/specs/01-prd.md dan .ai/plans/architecture.md.

Pecah implementasi menjadi beberapa fase kerja yang masing-masing:
- Bisa dikerjakan dan ditest secara terisolasi (seperti unit kerja TDD)
- Punya dependency yang jelas ke fase lain (sebutkan eksplisit)
- Cukup kecil untuk satu sesi kerja agent (jangan gabungkan banyak fitur besar
  jadi satu fase)

Untuk setiap fase, buat file terpisah di .ai/tasks/phase-N-[nama].md mengikuti
struktur di .ai/tasks/_template-phase.md (Tujuan, Scope, Non-Goals eksplisit,
Referensi ke PRD/architecture, Acceptance Criteria testable, Catatan untuk agent).

Urutkan fase berdasarkan dependency: database dulu, baru auth, baru fitur inti,
baru fitur sekunder, baru polish/UI.

Tampilkan dulu daftar fase yang kamu rencanakan (judul + 1 kalimat tujuan
masing-masing) sebelum membuat semua file — saya mau review urutannya dulu.
```

### Prompt 4.2: Generate satu task file spesifik

```
Berdasarkan .ai/specs/01-prd.md section [nomor/nama section] dan
.ai/plans/architecture.md section [nomor/nama section], buatkan task file
.ai/tasks/phase-[N]-[nama].md.

Ikuti template di .ai/tasks/_template-phase.md.

Scope HANYA mencakup: [sebutkan batasan eksplisit, misal: "CRUD produk dan
upload gambar saja, tidak termasuk kategori atau review produk"].

Pastikan Non-Goals section secara eksplisit menyebutkan fitur yang sengaja
ditunda ke fase lain.
```

### Prompt 4.3: Pecah fase yang ternyata terlalu besar

```
Task .ai/tasks/phase-[N]-[nama].md ternyata scope-nya terlalu besar untuk
satu sesi kerja.

Pecah jadi 2-3 sub-fase yang lebih kecil dengan dependency berurutan
(phase-Na, phase-Nb, dst), masing-masing tetap mengikuti struktur template.
Pastikan setiap sub-fase tetap punya Acceptance Criteria yang independen
dan bisa diverifikasi sendiri.
```

---

## FASE 5 — Implementasi

### Prompt 5.1: Mulai sesi kerja untuk satu task (PALING SERING DIPAKAI)

```
Sebelum mulai, baca dalam urutan ini:
1. AGENTS.md (konvensi & batasan proyek)
2. .ai/specs/01-prd.md (untuk konteks fitur ini, fokus section yang relevan)
3. .ai/plans/architecture.md (untuk struktur teknis yang harus diikuti)
4. .ai/tasks/phase-[N]-[nama].md (task yang akan dikerjakan sekarang)

Kerjakan SEMUA item di section "Scope" pada task file tersebut.
JANGAN kerjakan apapun yang ada di section "Non-Goals Fase Ini".

Setelah selesai, jalankan test yang relevan dan pastikan semua
Acceptance Criteria di task file terpenuhi. Tampilkan ringkasan:
file apa saja yang dibuat/diubah, dan apakah ada acceptance criteria
yang belum bisa dipenuhi (jika ada, jelaskan kenapa).
```

### Prompt 5.2: Review hasil kerja agent sebelum lanjut

```
Tunjukkan diff/ringkasan semua perubahan yang baru kamu buat untuk
.ai/tasks/phase-[N]-[nama].md.

Untuk setiap file yang diubah, jelaskan singkat apa fungsinya dan
kenapa pendekatan itu yang dipilih (terutama jika ada keputusan
yang tidak eksplisit disebut di task file).

Apakah ada bagian dari Acceptance Criteria yang kamu interpretasikan
secara longgar atau ambigu? Sebutkan.
```

### Prompt 5.3: Update Log Eksekusi setelah task selesai & diverifikasi

```
Task .ai/tasks/phase-[N]-[nama].md sudah saya review dan semua
Acceptance Criteria terpenuhi.

Update section "Log Eksekusi" di file tersebut: isi tanggal selesai,
daftar file yang dibuat/diubah, dan catatan deviasi dari plan (jika ada).
Ubah juga status di bagian atas file dari "In Progress" jadi "Done".
```

### Prompt 5.4: Lanjut ke task berikutnya dengan konteks bersih

```
Phase [N] sudah selesai dan terverifikasi.

Mulai sesi baru untuk .ai/tasks/phase-[N+1]-[nama].md mengikuti
prosedur yang sama: baca AGENTS.md, PRD section relevan,
architecture.md, lalu task file ini.

Sebelum mulai coding, konfirmasi ke saya: apakah ada dependency dari
phase sebelumnya yang perlu dicek dulu sudah tersedia?
```

---

## Prompt untuk Situasi Khusus

### Saat agent menyimpang dari scope (overreach)

```
Tolong hentikan dulu. Yang kamu kerjakan barusan — [sebutkan apa yang
keluar dari scope] — tidak ada di section Scope task file ini, bahkan
masuk ke Non-Goals.

Revert/hapus bagian itu. Kerjakan ulang hanya yang ada di section Scope.
Ke depan, kalau kamu merasa ada fitur yang "seharusnya" ditambahkan tapi
tidak ada di task file, tanya saya dulu sebelum implementasi.
```

### Saat butuh perbaikan kecil (skip alur penuh SDD)

```
Ini perbaikan kecil, tidak perlu task file baru: [jelaskan bug/perubahan kecil].

Langsung perbaiki di [file/lokasi terkait]. Tetap ikuti konvensi di AGENTS.md.
```

### Saat mulai sesi baru tapi lupa konteks proyek (recovery)

```
Ini proyek Laravel yang sudah berjalan. Sebelum saya kasih instruksi,
tolong baca dulu:
1. AGENTS.md
2. .ai/specs/01-prd.md (skim saja, untuk gambaran umum)
3. .ai/plans/architecture.md (skim saja)
4. .ai/tasks/ — cek file mana yang statusnya masih "In Progress" atau
   "Not Started", urutkan berdasarkan dependency

Setelah itu, ringkas ke saya: task apa yang sebaiknya dikerjakan
selanjutnya, dan apakah ada blocker dari task sebelumnya.
```

### Saat perlu generate test untuk task yang sudah selesai

```
Baca .ai/tasks/phase-[N]-[nama].md, khususnya section Acceptance Criteria.

Generate Feature Test (pakai Pest) yang memverifikasi setiap acceptance
criteria di task tersebut. Satu test method untuk satu criteria, beri
nama method yang deskriptif (format: it_[melakukan_apa]).

Jalankan test-nya dan laporkan hasilnya.
```

### Saat code review sebelum merge/deploy

```
Review semua perubahan kode untuk .ai/tasks/phase-[N]-[nama].md
sebagai senior Laravel developer yang melakukan code review sebelum merge.

Cek khusus:
1. Apakah ada N+1 query yang terlewat?
2. Apakah semua input tervalidasi dengan Form Request?
3. Apakah ada mass assignment risk ($fillable tidak lengkap)?
4. Apakah authorization (Policy/Gate) sudah benar untuk setiap action?
5. Apakah ada hardcoded value yang seharusnya di .env atau config?

Beri rating: Aman untuk merge / Perlu perbaikan minor / Perlu perbaikan major,
dengan daftar temuan spesifik per kategori di atas.
```

---

## Tips Menyusun Prompt Sendiri

Jika butuh prompt di luar daftar ini, pola dasarnya selalu:

```
[BACA DULU]  → sebutkan file mana yang harus dibaca agent untuk konteks
[KERJAKAN]   → instruksi spesifik, gunakan kata kerja jelas
[BATASAN]    → apa yang TIDAK boleh dikerjakan (eksplisit, bukan diasumsikan)
[VERIFIKASI] → bagaimana cara tahu hasilnya benar (test, acceptance criteria)
[OUTPUT]     → format hasil yang diharapkan (ringkasan, diff, list, dll)
```

Semakin besar/berisiko perubahan yang diminta, semakin penting bagian
BATASAN dan VERIFIKASI — jangan pernah skip untuk task yang menyentuh
database, auth, atau payment.
