# Kebijakan Jam Pelajaran Dinamis

## Tujuan

Kolom `JAM` pada jadwal mingguan tidak lagi berasal dari array hardcoded. Setiap Program Pendidikan memiliki kebijakan slot waktu sendiri melalui tabel `schedule_time_slots`.

## Jalur implementasi

```text
Admin > Akademik > Kebijakan Jam
→ admin.schedule-time-slots.*
→ ScheduleTimeSlotController
→ Store/UpdateScheduleTimeSlotRequest
→ ScheduleTimeSlotPolicy
→ ScheduleTimeSlot + EducationProgram
→ ScheduleGridService
→ TeacherScheduleController
→ guru/schedule/index.blade.php
```

## Kebijakan awal

- Program Formal: 8 jam pelajaran pagi, `07:15–13:05`, dengan istirahat `09:55–10:25`. Slot 6–8 tidak berlaku pada Jumat.
- Program Muadalah: 6 jam pelajaran sore, `16:00–20:00`.

Seluruh nilai dapat diubah admin. Baris dapat diberi label, dijadikan istirahat, dinonaktifkan, diurutkan, atau dibatasi agar tidak berlaku pada Jumat.

## Perilaku filter

- `Program Formal` menampilkan jadwal Formal dan sumbu jam Formal.
- `Program Muadalah` menampilkan jadwal Muadalah dan sumbu jam Muadalah.
- `Semua program` menampilkan grid terpisah per program agar sumbu pagi dan sore tidak tercampur.

Record jadwal lama yang tidak beririsan dengan slot kebijakan tidak dihapus. Jadwal tersebut tetap tersedia pada mode Daftar dan ditandai sebagai jadwal di luar kebijakan untuk ditinjau admin.

## Program guru, kelas, dan jadwal

Program jadwal disimpan secara eksplisit pada `schedules.education_program_id`. Nilai ini tidak dibatasi oleh jenis profil guru atau program asal kelas. Contohnya, guru Formal dapat mengajar kelas X.3 pada slot Program Muadalah apabila program jadwalnya dipilih Muadalah.

Form tambah dan edit jadwal menyediakan pilihan Program Pendidikan. Jika request lama tidak mengirim pilihan tersebut, aplikasi menentukan program dari rentang waktu yang cocok secara unik, lalu menggunakan program kelas sebagai fallback. Migration awal juga memakai aturan ini untuk mengklasifikasikan jadwal lama tanpa mengubah guru, kelas, mata pelajaran, hari, atau jam.

## Authorization

Hanya `admin` dan `super_admin` yang dapat melihat atau mengubah kebijakan slot. Guru hanya mengonsumsi kebijakan tersebut melalui jadwalnya.
