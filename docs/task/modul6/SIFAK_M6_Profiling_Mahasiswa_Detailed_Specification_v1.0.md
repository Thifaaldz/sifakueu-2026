# M6 — PROFILING MAHASISWA & REKOMENDASI PROFIL LULUSAN
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M6 — Profiling Mahasiswa & Rekomendasi Profil Lulusan  
**Tahap Implementasi:** Modul Bisnis Kelima setelah M5, M4, M7, dan M1  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, M4 KRS & Penjadwalan, M5 Profiling Dosen, M7 Manajemen Dokumen TA, M1 Sidang Sempro & TA  
**Tujuan:** Menjabarkan kebutuhan, data, scoring, profil mahasiswa, CPL/PLO, minat, kompetensi, rekomendasi mata kuliah, karier, topik TA, pembimbing, hak akses, integrasi, acceptance criteria, dan strategi implementasi M6 secara detail.

---

# 1. Ringkasan Modul

M6 adalah modul yang mengolah data akademik dan non-akademik mahasiswa menjadi profil terstruktur dan rekomendasi pengembangan akademik.

Sumber data utama:

```text
Master Data
+
M4 KRS & Riwayat Mata Kuliah
+
M7 Topik / Dokumen TA
+
M1 Status Sempro / Sidang
+
Data CPL / PLO
+
Minat / Sertifikasi / Portofolio
```

Output utama:

```text
Profil Akademik Mahasiswa
Profil Kompetensi
CPL / PLO
Kekuatan dan Gap
Rekomendasi Mata Kuliah
Rekomendasi Profil Lulusan
Rekomendasi Karier
Rekomendasi Topik TA
Rekomendasi Calon Pembimbing
```

M6 berfungsi sebagai modul recommendation-support, bukan sebagai pengambil keputusan otomatis.

---

# 2. Tujuan M6

M6 bertujuan untuk:

1. membangun profil akademik mahasiswa;
2. menggabungkan data nilai, SKS, IPK, dan histori KRS;
3. memetakan capaian CPL mahasiswa;
4. memetakan kecocokan mahasiswa terhadap PLO/profil lulusan;
5. menyimpan minat mahasiswa;
6. menyimpan sertifikasi;
7. menyimpan portofolio;
8. menyimpan pengalaman MBKM/magang/organisasi jika digunakan;
9. mengidentifikasi kompetensi kuat;
10. mengidentifikasi gap kompetensi;
11. memberikan rekomendasi mata kuliah;
12. memberikan rekomendasi profil lulusan;
13. memberikan rekomendasi karier;
14. memberikan rekomendasi topik TA;
15. memberikan rekomendasi calon pembimbing berdasarkan M5;
16. menyediakan explainability;
17. menyediakan histori rekomendasi;
18. menyediakan data bagi M3 Monitoring & Alert;
19. menjaga tenant isolation;
20. menjaga data ownership dan privacy.

---

# 3. Scope M6

## 3.1 In Scope

```text
Profil Akademik
Riwayat KRS
Riwayat Nilai
IPK
SKS
CPL
PLO
Profil Lulusan
Minat
Sertifikasi
Portofolio
Organisasi
MBKM / Magang
Kekuatan Kompetensi
Gap Kompetensi
Rekomendasi MK
Rekomendasi Karier
Rekomendasi Topik TA
Rekomendasi Pembimbing
Recommendation History
Explainability
Dashboard Mahasiswa
Dashboard Prodi
Audit
```

## 3.2 Out of Scope

Untuk versi awal:

- psikotes otomatis;
- inferensi kepribadian dari data pribadi;
- AI sebagai pengambil keputusan akademik final;
- rekomendasi pekerjaan dari portal lowongan eksternal;
- scraping profil sosial media;
- profiling lintas tenant;
- ranking mahasiswa publik;
- penilaian karakter tanpa data resmi.

---

# 4. Aktor M6

| Aktor | Peran |
|---|---|
| Mahasiswa | Melihat profil, CPL/PLO, rekomendasi, mengisi minat/portofolio tertentu |
| Dosen PA | Melihat profil mahasiswa PA dan rekomendasi akademik |
| Admin Prodi | Mengelola mapping CPL/PLO dan monitoring profil mahasiswa |
| Kaprodi | Melihat dashboard agregat dan gap kompetensi |
| LPM/Gugus Mutu | Melihat capaian CPL/PLO dan laporan mutu |
| Kepala Laboratorium | Melihat profil relevan untuk kebutuhan lab/asisten sesuai scope |
| M4 Service | Menyediakan histori KRS/MK |
| M5 Service | Menyediakan profil dosen dan kandidat pembimbing |
| M7 Service | Menyediakan topik/metadata TA |
| M1 Service | Menyediakan status Sempro/Sidang |
| M3 Service | Menggunakan hasil M6 untuk monitoring dan alert |

---

# 5. Panel dan Menu

## 5.1 Mahasiswa Panel

```text
Profil Akademik
├── Ringkasan
├── IPK & SKS
├── Riwayat Mata Kuliah
├── CPL Saya
├── PLO Saya
├── Kekuatan
└── Gap Kompetensi

Profil Saya
├── Minat
├── Sertifikasi
├── Portofolio
├── Organisasi
└── MBKM / Magang

Rekomendasi
├── Mata Kuliah
├── Profil Lulusan
├── Karier
├── Topik TA
└── Calon Pembimbing

Riwayat Rekomendasi
```

---

## 5.2 Dosen PA Panel

```text
Profil Mahasiswa PA
├── Akademik
├── CPL/PLO
├── Gap Kompetensi
├── Rekomendasi MK
├── Rekomendasi Profil Lulusan
└── Catatan Pendampingan
```

---

## 5.3 Admin Panel

```text
Profiling Mahasiswa
├── Daftar Mahasiswa
├── Profil Akademik
├── CPL
├── PLO
├── Mapping CPL-MK
├── Mapping PLO-CPL
├── Profil Lulusan
├── Gap Kompetensi
├── Rekomendasi
└── Laporan
```

---

## 5.4 Pimpinan Panel

```text
Dashboard Profil Mahasiswa
├── Distribusi CPL
├── Distribusi PLO
├── Gap Kompetensi
├── Profil Lulusan
├── Mahasiswa Berisiko
└── Tren Capaian
```

---

# 6. Dependensi Data

M6 menggunakan:

```text
Master Data
├── Mahasiswa
├── Program Studi
├── Mata Kuliah
├── Kurikulum
├── Semester
└── Tahun Akademik

M4
├── KRS
├── Mata Kuliah Diambil
├── SKS
└── Riwayat Akademik

M5
├── Profil Dosen
├── Keahlian
├── Rumpun
├── KBK
└── Recommendation Candidate

M7
├── Judul TA
├── Topik
└── Keywords

M1
├── Status Sempro
└── Status Sidang
```

---

# 7. Struktur Data Utama

Kelompok data:

```text
Profil Mahasiswa
Kompetensi
CPL
PLO
Minat
Portofolio
Sertifikasi
MBKM
Rekomendasi
History
```

---

# 8. mahasiswa_profile

```text
mahasiswa_profile
├── id
├── mahasiswa_id
├── academic_score
├── competency_score
├── profile_status
├── last_recalculated_at
└── timestamps
```

---

# 9. mahasiswa_interest

```text
mahasiswa_interest
├── id
├── mahasiswa_id
├── interest_area
├── level
├── source
├── verified
└── timestamps
```

Level:

```text
LOW
MEDIUM
HIGH
```

---

# 10. mahasiswa_certifications

```text
mahasiswa_certifications
├── id
├── mahasiswa_id
├── name
├── issuer
├── field
├── issued_at
├── expired_at nullable
├── file_id nullable
├── verified
└── timestamps
```

---

# 11. mahasiswa_portfolios

```text
mahasiswa_portfolios
├── id
├── mahasiswa_id
├── title
├── category
├── description
├── url nullable
├── file_id nullable
├── skills_json nullable
└── timestamps
```

---

# 12. mahasiswa_organizations

```text
mahasiswa_organizations
├── id
├── mahasiswa_id
├── organization_name
├── role
├── start_date
├── end_date nullable
├── description
└── timestamps
```

---

# 13. mahasiswa_mbkm

```text
mahasiswa_mbkm
├── id
├── mahasiswa_id
├── program_type
├── institution
├── role
├── start_date
├── end_date
├── field
├── description
└── timestamps
```

---

# 14. cpl_master

```text
cpl_master
├── id
├── prodi_id
├── code
├── name
├── description
├── category
├── active
└── timestamps
```

Kategori contoh:

```text
SIKAP
PENGETAHUAN
KETERAMPILAN_UMUM
KETERAMPILAN_KHUSUS
```

---

# 15. mata_kuliah_cpl

```text
mata_kuliah_cpl
├── id
├── mata_kuliah_id
├── cpl_id
├── weight
└── timestamps
```

Weight:

```text
0–1
```

atau skala lain yang dikonfigurasi.

---

# 16. mahasiswa_cpl_scores

```text
mahasiswa_cpl_scores
├── id
├── mahasiswa_id
├── cpl_id
├── semester_id nullable
├── score
├── source
├── calculated_at
└── timestamps
```

---

# 17. plo_master

```text
plo_master
├── id
├── prodi_id
├── code
├── name
├── description
├── active
└── timestamps
```

---

# 18. plo_cpl_mapping

```text
plo_cpl_mapping
├── id
├── plo_id
├── cpl_id
├── weight
└── timestamps
```

---

# 19. mahasiswa_plo_scores

```text
mahasiswa_plo_scores
├── id
├── mahasiswa_id
├── plo_id
├── score
├── rank_order nullable
├── calculated_at
└── timestamps
```

---

# 20. graduate_profiles

```text
graduate_profiles
├── id
├── prodi_id
├── code
├── name
├── description
├── active
└── timestamps
```

Contoh:

```text
Software Engineer
Data Analyst
Business Analyst
IT Auditor
System Analyst
UI/UX Specialist
Data Engineer
```

---

# 21. graduate_profile_plo

```text
graduate_profile_plo
├── id
├── graduate_profile_id
├── plo_id
├── weight
└── timestamps
```

---

# 22. mahasiswa_graduate_profile_scores

```text
mahasiswa_graduate_profile_scores
├── id
├── mahasiswa_id
├── graduate_profile_id
├── score
├── rank_order
├── generated_at
└── timestamps
```

---

# 23. competency_gaps

```text
competency_gaps
├── id
├── mahasiswa_id
├── competency_type
├── competency_reference_id
├── current_score
├── target_score
├── gap_score
├── severity
└── timestamps
```

Severity:

```text
LOW
MEDIUM
HIGH
```

---

# 24. student_recommendations

```text
student_recommendations
├── id
├── mahasiswa_id
├── recommendation_type
├── reference_type nullable
├── reference_id nullable
├── score
├── rank_order nullable
├── reason_summary
├── status
├── generated_at
└── timestamps
```

Type:

```text
COURSE
GRADUATE_PROFILE
CAREER
THESIS_TOPIC
SUPERVISOR
```

---

# 25. recommendation_histories

```text
recommendation_histories
├── id
├── mahasiswa_id
├── recommendation_type
├── payload_json
├── model_version
├── generated_at
└── timestamps
```

---

# 26. Sumber Nilai Akademik

M6 tidak membuat nilai akademik sendiri.

Sumber:

```text
Histori mata kuliah
Nilai
SKS
IPK
```

berasal dari data akademik resmi.

Jika nilai belum tersedia pada M4, integrasi dilakukan dengan sumber akademik yang ditentukan sistem.

---

# 27. Perhitungan CPL

Konsep:

```text
Mata Kuliah
↓
Nilai Mahasiswa
↓
Mapping MK → CPL
↓
Bobot
↓
Score CPL
```

Formula dasar:

```text
CPL Score =
Σ(nilai_normalisasi_mk × bobot_mk_cpl)
/
Σ(bobot_mk_cpl)
```

---

# 28. Normalisasi Nilai

Nilai akademik dapat dinormalisasi ke:

```text
0–100
```

Jika sumber menggunakan huruf:

```text
A
A-
B+
...
```

gunakan mapping resmi tenant.

Jangan hardcode jika kebijakan nilai dapat berubah.

---

# 29. Perhitungan PLO

Konsep:

```text
CPL
↓
PLO-CPL Mapping
↓
PLO Score
```

Formula:

```text
PLO Score =
Σ(CPL Score × Weight)
/
Σ(Weight)
```

---

# 30. Profil Lulusan

Profil lulusan menggunakan PLO.

Contoh:

```text
Software Engineer
requires:
PLO-01 = 0.25
PLO-03 = 0.30
PLO-05 = 0.25
PLO-07 = 0.20
```

Score:

```text
Graduate Profile Score =
Σ(PLO score × weight)
```

---

# 31. Top-N Profil Lulusan

Output:

| Rank | Profil Lulusan | Skor | Alasan |
|---:|---|---:|---|
| 1 | Software Engineer | 88 | CPL teknis kuat |
| 2 | System Analyst | 83 | Analisis dan proses baik |
| 3 | Data Analyst | 76 | Dasar data cukup kuat |

---

# 32. Kekuatan Kompetensi

Sistem dapat menampilkan:

```text
Top CPL
Top PLO
Top Skill
Top Interest
```

Contoh:

```text
Kekuatan:
- Software Design
- Database
- Requirement Analysis
```

---

# 33. Gap Kompetensi

Contoh:

```text
Target PLO = 80
Current = 62

Gap = 18
```

Severity dapat ditentukan:

```text
0–9   LOW
10–19 MEDIUM
>=20  HIGH
```

Threshold configurable.

---

# 34. Rekomendasi Mata Kuliah

Input:

```text
Gap CPL/PLO
Kurikulum
MK belum diambil
Prasyarat
Semester
Minat
```

Flow:

```text
Gap Kompetensi
↓
Cari MK yang berkontribusi ke CPL terkait
↓
Filter:
- belum lulus
- tersedia
- memenuhi prasyarat
↓
Ranking
↓
Recommendation
```

---

# 35. Formula Rekomendasi MK

Contoh:

```text
Score =
0.45 × gap_relevance
+
0.25 × curriculum_relevance
+
0.20 × interest_match
+
0.10 × academic_feasibility
```

Bobot configurable.

---

# 36. Rekomendasi Karier

Input:

```text
PLO
Graduate Profile
Minat
Portofolio
Sertifikasi
```

Output berupa:

```text
Career Recommendation
```

Contoh:

```text
Software Engineer
System Analyst
Data Analyst
```

Bukan jaminan pekerjaan.

---

# 37. Rekomendasi Topik TA

Input:

```text
Minat
Top CPL/PLO
MK unggul
Portofolio
Topik sebelumnya
```

Output:

```text
Topik / Area
bukan judul final otomatis
```

Contoh:

```text
Enterprise Systems
Data Analytics
Software Quality
Information Security
UI/UX
```

---

# 38. Integrasi M7 untuk Topik TA

M7 menyediakan:

```text
judul
topik
keywords
```

M6 dapat menggunakan data tersebut untuk:

```text
update profile
history
recommendation refinement
```

---

# 39. Rekomendasi Pembimbing

M6 menggunakan M5.

Flow:

```text
Topik TA Mahasiswa
↓
Mapping ke rumpun / keahlian
↓
M5 Candidate Search
↓
Cek beban
↓
Top-N Pembimbing
```

---

# 40. Formula Kandidat Pembimbing

M6 tidak menghitung ulang seluruh profil dosen.

Gunakan output M5:

```text
matching_score
workload
expertise
availability
```

Kemudian tambahkan konteks mahasiswa jika diperlukan:

```text
student_topic_match
```

---

# 41. Human-in-the-Loop

Rekomendasi M6 tidak boleh menjadi keputusan final otomatis.

Contoh:

```text
M6 rekomendasi pembimbing
↓
Admin/Kaprodi review
↓
Keputusan final dilakukan di proses yang berwenang
```

---

# 42. Explainability

Setiap rekomendasi harus memiliki alasan.

Contoh:

```text
Software Engineer — 88

CPL Teknis        32/35
PLO Rekayasa      25/30
Portofolio        18/20
Minat             13/15
```

---

# 43. Status Recommendation

```text
GENERATED
VIEWED
ACCEPTED_AS_REFERENCE
DISMISSED
SUPERSEDED
```

---

# 44. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M6-FR-001 | Sistem dapat membentuk profil mahasiswa. |
| M6-FR-002 | Sistem dapat membaca histori akademik. |
| M6-FR-003 | Sistem dapat membaca KRS/histori MK dari M4. |
| M6-FR-004 | Sistem dapat menyimpan minat mahasiswa. |
| M6-FR-005 | Sistem dapat menyimpan sertifikasi mahasiswa. |
| M6-FR-006 | Sistem dapat menyimpan portofolio. |
| M6-FR-007 | Sistem dapat menyimpan data organisasi. |
| M6-FR-008 | Sistem dapat menyimpan data MBKM/magang. |
| M6-FR-009 | Sistem dapat mengelola CPL master. |
| M6-FR-010 | Sistem dapat mengelola mapping MK-CPL. |
| M6-FR-011 | Sistem dapat menghitung score CPL mahasiswa. |
| M6-FR-012 | Sistem dapat mengelola PLO. |
| M6-FR-013 | Sistem dapat mengelola mapping CPL-PLO. |
| M6-FR-014 | Sistem dapat menghitung score PLO. |
| M6-FR-015 | Sistem dapat mengelola profil lulusan. |
| M6-FR-016 | Sistem dapat menghitung kecocokan profil lulusan. |
| M6-FR-017 | Sistem dapat mengidentifikasi kekuatan. |
| M6-FR-018 | Sistem dapat mengidentifikasi gap kompetensi. |
| M6-FR-019 | Sistem dapat menghasilkan rekomendasi MK. |
| M6-FR-020 | Sistem dapat menghasilkan rekomendasi profil lulusan. |
| M6-FR-021 | Sistem dapat menghasilkan rekomendasi karier. |
| M6-FR-022 | Sistem dapat menghasilkan rekomendasi topik TA. |
| M6-FR-023 | Sistem dapat meminta kandidat pembimbing dari M5. |
| M6-FR-024 | Sistem dapat menampilkan Top-N rekomendasi. |
| M6-FR-025 | Sistem dapat menjelaskan alasan recommendation. |
| M6-FR-026 | Sistem menyimpan histori recommendation. |
| M6-FR-027 | Dosen PA dapat melihat profil mahasiswa PA. |
| M6-FR-028 | Kaprodi dapat melihat dashboard agregat. |
| M6-FR-029 | LPM dapat melihat CPL/PLO sesuai scope. |
| M6-FR-030 | M3 dapat membaca gap dan indikator M6. |
| M6-FR-031 | Semua perubahan penting diaudit. |
| M6-FR-032 | Seluruh proses tenant-aware. |

---

# 45. Business Rules M6

| ID | Rule |
|---|---|
| M6-BR-001 | Mahasiswa hanya melihat profil detail miliknya sendiri kecuali role lain berwenang. |
| M6-BR-002 | Dosen PA hanya melihat mahasiswa yang menjadi scope-nya. |
| M6-BR-003 | Score CPL harus menggunakan data akademik resmi. |
| M6-BR-004 | Mapping MK-CPL harus berasal dari prodi yang sesuai. |
| M6-BR-005 | Mapping PLO-CPL harus konsisten dengan prodi. |
| M6-BR-006 | Recommendation bersifat saran. |
| M6-BR-007 | Recommendation harus memiliki alasan/explainability. |
| M6-BR-008 | Recommendation lama tetap disimpan sebagai history. |
| M6-BR-009 | Tenant A tidak dapat membaca profil mahasiswa Tenant B. |
| M6-BR-010 | Data sensitif mahasiswa tidak boleh diekspor tanpa permission. |
| M6-BR-011 | Profil lulusan tidak boleh digunakan sebagai label permanen mahasiswa. |
| M6-BR-012 | Perubahan formula/bobot harus diaudit. |
| M6-BR-013 | Rekomendasi pembimbing menggunakan data M5, bukan duplikasi profil dosen. |
| M6-BR-014 | M6 tidak menentukan kelulusan mahasiswa. |

---

# 46. Permission M6

```text
view_own_student_profile
update_own_interest
manage_own_portfolio
manage_own_certification
manage_own_mbkm

view_own_cpl
view_own_plo
view_own_recommendation

view_pa_student_profile
view_pa_student_recommendation

manage_cpl
manage_plo
manage_cpl_mapping
manage_plo_mapping
manage_graduate_profile

recalculate_student_profile
generate_student_recommendation
view_student_recommendation

view_student_profile_dashboard
view_quality_cpl_plo
export_student_profile_report
```

---

# 47. Role Mapping

## Mahasiswa

```text
view_own_student_profile
update_own_interest
manage_own_portfolio
manage_own_certification
manage_own_mbkm
view_own_cpl
view_own_plo
view_own_recommendation
```

## Dosen PA

```text
view_pa_student_profile
view_pa_student_recommendation
```

## Admin Prodi

```text
manage_cpl
manage_plo
manage_cpl_mapping
manage_plo_mapping
manage_graduate_profile
recalculate_student_profile
generate_student_recommendation
view_student_recommendation
```

## Kaprodi

```text
view_student_profile_dashboard
view_student_recommendation
export_student_profile_report
```

## LPM/Gugus Mutu

```text
view_quality_cpl_plo
view_student_profile_dashboard
```

---

# 48. Policy / Data Scope

```text
Mahasiswa
→ data sendiri.

Dosen PA
→ mahasiswa PA.

Admin Prodi
→ mahasiswa prodi scope.

Kaprodi
→ prodi sendiri.

LPM
→ agregat/quality scope.

Tenant
→ database tenant aktif.
```

---

# 49. Dashboard Mahasiswa

Widget:

```text
IPK
SKS
CPL Tertinggi
CPL Terendah
Top PLO
Top Graduate Profile
Gap Kompetensi
Rekomendasi MK
Topik TA
```

---

# 50. Dashboard Dosen PA

Widget:

```text
Mahasiswa PA
Gap Tinggi
CPL Rendah
Rekomendasi MK
Progress Akademik
```

---

# 51. Dashboard Kaprodi

Widget:

```text
Distribusi PLO
Top Graduate Profile
CPL Average
Gap Tinggi
Mahasiswa Risiko Akademik
Trend per Angkatan
```

---

# 52. Dashboard LPM

Widget:

```text
CPL Average
PLO Average
Gap per CPL
Gap per Angkatan
Trend
Coverage MK-CPL
```

---

# 53. Integrasi dengan M4

M6 membaca:

```text
KRS
Mata Kuliah
SKS
Semester
Riwayat Pengambilan
```

dan nilai akademik resmi jika tersedia.

Event contoh:

```text
M4.KRS_FINALIZED
M4.COURSE_HISTORY_UPDATED
```

M6 dapat memicu recalculation.

---

# 54. Integrasi dengan M5

M6 meminta:

```text
candidate supervisor
expertise match
workload
```

M6 tidak menyimpan profil dosen secara duplikat.

---

# 55. Integrasi dengan M7

M6 membaca:

```text
judul TA
topik
keywords
```

untuk refinement profil.

M6 dapat mengirim:

```text
recommended topic area
```

ke UI mahasiswa, bukan langsung mengubah judul TA.

---

# 56. Integrasi dengan M1

M6 dapat membaca:

```text
status sempro
status sidang
```

untuk menggambarkan progress akademik.

M6 tidak mengubah hasil sidang.

---

# 57. Integrasi dengan M3

M3 menggunakan:

```text
CPL gap
PLO gap
Academic profile
Recommendation status
Progress
```

Contoh:

```text
CPL kritis < threshold
↓
M3 Warning
```

---

# 58. Integrasi Shared Services

## Audit

```text
CPL mapping changed
PLO mapping changed
profile recalculated
recommendation generated
weights changed
```

## Notification

Contoh:

```text
rekomendasi baru tersedia
gap kompetensi tinggi
profil selesai diperbarui
```

## Queue

```text
bulk recalculation
bulk recommendation
large export
```

## Scheduler

```text
nightly profile recalculation
recommendation refresh
stale profile detection
```

---

# 59. Audit Events

```text
STUDENT_PROFILE_RECALCULATED
STUDENT_INTEREST_UPDATED
STUDENT_PORTFOLIO_ADDED
STUDENT_CERTIFICATION_ADDED

CPL_MAPPING_UPDATED
PLO_MAPPING_UPDATED

CPL_SCORE_CALCULATED
PLO_SCORE_CALCULATED
GRADUATE_PROFILE_CALCULATED
COMPETENCY_GAP_CALCULATED

COURSE_RECOMMENDATION_GENERATED
CAREER_RECOMMENDATION_GENERATED
THESIS_TOPIC_RECOMMENDATION_GENERATED
SUPERVISOR_RECOMMENDATION_GENERATED
```

---

# 60. Internal Events M6

Diterbitkan:

```text
M6.STUDENT_PROFILE_UPDATED
M6.CPL_UPDATED
M6.PLO_UPDATED
M6.COMPETENCY_GAP_UPDATED
M6.RECOMMENDATION_GENERATED
```

Diterima:

```text
M4.KRS_FINALIZED
M4.COURSE_HISTORY_UPDATED

M7.TA_CREATED
M7.TA_METADATA_UPDATED

M1.SIDANG_RESULT_PUBLISHED

M5.RECOMMENDATION_GENERATED
```

---

# 61. Service Layer

```text
StudentProfileService
StudentAcademicProfileService
CplService
PloService
GraduateProfileService
CompetencyGapService
CourseRecommendationService
CareerRecommendationService
ThesisTopicRecommendationService
SupervisorRecommendationService
StudentRecommendationHistoryService
```

---

# 62. Action Layer

```text
RecalculateStudentProfileAction
CalculateCplAction
CalculatePloAction
CalculateGraduateProfileAction
CalculateCompetencyGapAction
GenerateCourseRecommendationAction
GenerateCareerRecommendationAction
GenerateThesisTopicRecommendationAction
GenerateSupervisorRecommendationAction
```

---

# 63. Filament Resource

```text
CplResource
PloResource
GraduateProfileResource
StudentProfileResource
```

---

# 64. Custom Pages

```text
StudentProfileDashboardPage
CplMappingPage
PloMappingPage
CompetencyGapPage
StudentRecommendationPage
QualityDashboardPage
```

---

# 65. Livewire Components

```text
CplRadarChart
PloSummary
GraduateProfileRanking
CompetencyGapTable
CourseRecommendationList
CareerRecommendationList
ThesisTopicRecommendationList
SupervisorRecommendationList
```

---

# 66. Search dan Filter

Admin:

```text
Search:
NIM
Nama

Filter:
Prodi
Angkatan
Semester
CPL
PLO
Gap Severity
Graduate Profile
Recommendation Type
```

---

# 67. Recommendation Configuration

Contoh:

```text
m6.course.weight.gap = 0.45
m6.course.weight.curriculum = 0.25
m6.course.weight.interest = 0.20
m6.course.weight.feasibility = 0.10

m6.gap.medium_threshold = 10
m6.gap.high_threshold = 20
```

---

# 68. Versioning Formula

Perubahan formula/bobot harus memiliki versi.

Contoh:

```text
M6-RULE-V1
M6-RULE-V2
```

Recommendation history menyimpan:

```text
model_version
```

agar hasil lama tetap dapat dijelaskan.

---

# 69. QA Test Scenario — Profil

```text
M6-TC-001
Mahasiswa melihat profil sendiri
Expected:
allowed.

M6-TC-002
Mahasiswa melihat profil mahasiswa lain
Expected:
403.

M6-TC-003
Dosen PA melihat mahasiswa PA
Expected:
allowed.
```

---

# 70. QA Test Scenario — CPL

```text
M6-TC-010
Nilai valid + mapping valid
Expected:
CPL score dihitung.

M6-TC-011
Tidak ada mapping
Expected:
CPL tidak dihitung / warning.

M6-TC-012
Data tenant lain
Expected:
tidak digunakan.
```

---

# 71. QA Test Scenario — PLO

```text
M6-TC-020
CPL tersedia
Expected:
PLO score dihitung.

M6-TC-021
Bobot mapping invalid
Expected:
validation error.
```

---

# 72. QA Test Scenario — Recommendation MK

```text
M6-TC-030
Mahasiswa memiliki gap
Expected:
MK relevan direkomendasikan.

M6-TC-031
MK sudah lulus
Expected:
tidak direkomendasikan ulang kecuali rule mengizinkan.

M6-TC-032
Prasyarat belum memenuhi
Expected:
ranking/filter disesuaikan.
```

---

# 73. QA Test Scenario — Profil Lulusan

```text
M6-TC-040
PLO lengkap
Expected:
Top-N graduate profile tersedia.

M6-TC-041
Explainability dibuka
Expected:
breakdown skor tampil.
```

---

# 74. QA Test Scenario — Pembimbing

```text
M6-TC-050
Topik TA tersedia
Expected:
candidate dari M5 tersedia.

M6-TC-051
Dosen overload
Expected:
ranking/penalti mengikuti M5.
```

---

# 75. QA Test Scenario — Tenant Isolation

```text
M6-TC-060
FASILKOM membaca profil FEB
Expected:
ditolak.

M6-TC-061
Queue recalculation tenant FEB
Expected:
hanya DB FEB digunakan.

M6-TC-062
Export lintas tenant
Expected:
ditolak.
```

---

# 76. Acceptance Criteria M6

M6 dinyatakan siap apabila:

- [ ] profil mahasiswa dapat dibentuk;
- [ ] histori KRS dapat dibaca;
- [ ] minat dapat disimpan;
- [ ] sertifikasi dapat disimpan;
- [ ] portofolio dapat disimpan;
- [ ] MBKM/magang dapat disimpan;
- [ ] CPL master tersedia;
- [ ] mapping MK-CPL tersedia;
- [ ] score CPL dapat dihitung;
- [ ] PLO master tersedia;
- [ ] mapping CPL-PLO tersedia;
- [ ] score PLO dapat dihitung;
- [ ] profil lulusan tersedia;
- [ ] Top-N profil lulusan dapat dihitung;
- [ ] gap kompetensi dapat dihitung;
- [ ] rekomendasi MK tersedia;
- [ ] rekomendasi karier tersedia;
- [ ] rekomendasi topik TA tersedia;
- [ ] kandidat pembimbing dari M5 tersedia;
- [ ] explainability tersedia;
- [ ] recommendation history tersimpan;
- [ ] dashboard mahasiswa tersedia;
- [ ] dashboard Dosen PA tersedia;
- [ ] dashboard Kaprodi tersedia;
- [ ] dashboard LPM tersedia;
- [ ] M3 dapat membaca indikator M6;
- [ ] audit log berjalan;
- [ ] Queue tenant-aware;
- [ ] Scheduler tenant-aware;
- [ ] tenant isolation lulus.

---

# 77. Definition of Done M6

M6 dianggap selesai apabila:

1. migration selesai;
2. model dan relation selesai;
3. permission tersedia;
4. policy tersedia;
5. service layer tersedia;
6. action layer tersedia;
7. Mahasiswa Panel terintegrasi;
8. Dosen PA Panel terintegrasi;
9. Admin Panel terintegrasi;
10. Pimpinan Panel terintegrasi;
11. CPL mapping selesai;
12. CPL scoring berjalan;
13. PLO mapping selesai;
14. PLO scoring berjalan;
15. graduate profile scoring berjalan;
16. competency gap berjalan;
17. course recommendation berjalan;
18. career recommendation berjalan;
19. thesis topic recommendation berjalan;
20. M5 supervisor recommendation terintegrasi;
21. explainability tersedia;
22. recommendation history tersedia;
23. audit log berjalan;
24. Queue tenant-aware;
25. Scheduler tenant-aware;
26. integrasi M4 berjalan;
27. integrasi M5 berjalan;
28. integrasi M7 berjalan;
29. integrasi M1 berjalan;
30. integrasi M3 interface tersedia;
31. functional test lulus;
32. authorization test lulus;
33. tenant isolation test lulus;
34. dokumentasi internal tersedia.

---

# 78. Urutan Implementasi M6

Urutan yang disarankan:

```text
1. Student Profile Foundation
↓
2. Interest / Portfolio / Certification
↓
3. CPL Master
↓
4. MK-CPL Mapping
↓
5. CPL Calculation
↓
6. PLO Master
↓
7. PLO-CPL Mapping
↓
8. PLO Calculation
↓
9. Graduate Profile
↓
10. Graduate Profile Scoring
↓
11. Competency Gap
↓
12. Course Recommendation
↓
13. Career Recommendation
↓
14. Thesis Topic Recommendation
↓
15. M5 Supervisor Recommendation
↓
16. Explainability
↓
17. Recommendation History
↓
18. Dashboards
↓
19. M3 Integration
↓
20. QA
```

---

# 79. Sprint Rekomendasi

## Sprint M6-1 — Student Profile Foundation

```text
Profile
Interest
Portfolio
Certification
MBKM
Permission
Policy
Audit
```

Output:

```text
Profil mahasiswa non-akademik terstruktur.
```

---

## Sprint M6-2 — CPL

```text
CPL Master
MK-CPL Mapping
Normalization
CPL Calculation
```

Output:

```text
CPL mahasiswa dapat dihitung.
```

---

## Sprint M6-3 — PLO & Graduate Profile

```text
PLO
CPL-PLO Mapping
PLO Calculation
Graduate Profile
Ranking
```

Output:

```text
Top-N profil lulusan tersedia.
```

---

## Sprint M6-4 — Competency Gap & Course Recommendation

```text
Gap
Severity
Course Recommendation
Explainability
```

Output:

```text
Gap dan rekomendasi akademik tersedia.
```

---

## Sprint M6-5 — Career, TA & Supervisor

```text
Career Recommendation
Thesis Topic Recommendation
M5 Supervisor Candidate
History
```

Output:

```text
Rekomendasi pengembangan mahasiswa tersedia.
```

---

## Sprint M6-6 — Dashboard & Integration

```text
Mahasiswa Dashboard
Dosen PA Dashboard
Kaprodi Dashboard
LPM Dashboard
M3 Integration
Queue
Scheduler
QA
```

Output:

```text
M6 siap menjadi sumber analitik untuk M3.
```

---

# 80. Output Akhir M6

Setelah M6 selesai:

```text
Mahasiswa memiliki profil akademik
+
CPL dapat dihitung
+
PLO dapat dihitung
+
Profil lulusan dapat diranking
+
Gap kompetensi dapat diketahui
+
MK dapat direkomendasikan
+
Karier dapat direkomendasikan
+
Topik TA dapat direkomendasikan
+
Calon pembimbing dapat direkomendasikan
+
Dosen PA dapat melihat profil mahasiswa
+
Kaprodi/LPM dapat melihat agregat
+
M3 memperoleh indikator monitoring
```

---

# 81. Hubungan dengan Roadmap Berikutnya

Setelah:

```text
M5 ✓
M4 ✓
M7 ✓
M1 ✓
M6 ✓
```

modul berikutnya adalah:

```text
M3 — Monitoring & Alert
```

M3 akan menggabungkan:

```text
M4
→ KRS / SKS / progress akademik

M7
→ progress TA / revisi

M1
→ status Sempro / Sidang

M6
→ CPL / PLO / competency gap / profile
```

Sehingga M3 benar-benar memiliki data yang cukup untuk menghasilkan monitoring yang bermakna.

---

# 82. Kesimpulan

M6 bukan hanya halaman profil mahasiswa.

M6 terdiri dari empat lapisan:

```text
PROFILE LAYER
├── Akademik
├── Minat
├── Sertifikasi
├── Portofolio
└── MBKM

OUTCOME LAYER
├── CPL
├── PLO
├── Graduate Profile
└── Competency Gap

RECOMMENDATION LAYER
├── Mata Kuliah
├── Karier
├── Topik TA
└── Pembimbing

ANALYTICS LAYER
├── Dashboard
├── History
├── Explainability
└── Integration M3
```

Dengan struktur ini, M6 menjadi sumber analitik mahasiswa yang dapat digunakan oleh Dosen PA, Kaprodi, LPM, dan M3 tanpa menggantikan keputusan akademik manusia.

---

**SIFAK — M6 Profiling Mahasiswa & Rekomendasi Profil Lulusan — Detailed Specification v1.0**
