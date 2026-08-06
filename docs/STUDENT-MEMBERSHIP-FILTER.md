# Technical Documentation: Student Membership Filter & Promotion System (/promotion)

## Overview
The `/promotion` page (`Keanggotaan Siswa`) provides administrative tools to search, filter, select, and manage student memberships across formal & muadalah education programs, regular & non-regular class types, and active academic years.

---

## Key Features

### 1. Server-Side Filter System
All search queries and filters are handled server-side through Eloquent query builder constraints without loading entire datasets into memory:
- **Search**: Case-insensitive partial matching on `students.nama_lengkap` and `students.nis`.
- **Education Program (`program_id`)**: Filters students belonging to active class groups under the selected `education_programs.id` (Formal vs Muadalah).
- **Class Type (`class_type`)**: Filters by `reguler` or `non_reguler`.
- **Source Class Group (`source_class_group_id`)**: Filters students with active membership in a specific class.
- **Grade Level (`grade_level`)**: Filters by target grade level (e.g. `X`, `XI`, `XII`).
- **Membership Status (`membership_status`)**: Supports `all`, `has_active`, `no_active`, `in_target`, and `not_in_target`.
- **Hide Target Class Members (`hide_target_members`)**: Server-side exclusion of students who already hold active membership in `to_class_id`.
- **Query Preservation**: Preserves query parameters across pagination (`->withQueryString()`) and reload events.

---

### 2. Action Modes & Multi-Membership Safety

#### Mode A: Tambahkan ke Kelas (`add`)
- **Use Case**: Non-regular classes, extracurriculars, or supplementary courses.
- **Behavior**: Creates a new `class_group_student` record without closing existing active memberships. Skips duplicates if the student is already active in the target class.

#### Mode B: Pindahkan Kelas Reguler (`transfer`)
- **Use Case**: Regular class transfers (e.g., advancing from grade X to grade XI).
- **Behavior**:
  - Closes old active regular class memberships in the same education program and academic year by updating `status = 'transferred'` and `left_at = now()`.
  - Creates a new active membership record for the target class (`status = 'active'`, `joined_at = now()`).
  - Historical records remain intact in `class_group_student` for auditing.
  - Non-regular memberships (e.g. clubs, tahfidz) are preserved and never closed.
  - Target classes of type `non_reguler` reject the `transfer` mode via server validation.

---

### 3. Multi-Page Selection State
- Student selections (checkboxes) are managed via Alpine.js and synced with `sessionStorage` (`sabira_promotion_selected_ids`).
- Admin can navigate across pagination pages or apply filters without losing selected student IDs.
- Submitting the form re-validates all selected IDs on the server inside a database transaction (`DB::transaction`).

---

### 4. Confirmation & Preview Modal
- Before executing bulk updates, a confirm dialog displays a breakdown summary:
  - Selected target class details (Program, Type, Academic Year)
  - Selected action mode
  - Total selected students count
  - Count of students skipped (already in target class)
  - Count of eligible students to process
  - Count of old regular memberships to be closed (if mode is transfer)
- Submit button dynamically displays exact action text (e.g., "Tambahkan 15 Siswa" or "Pindahkan 12 Siswa").

---

## Database Architecture & Eager Loading

```
students
  └── class_group_student (pivot: academic_year_id, status, joined_at, left_at)
        └── class_groups (jenis_kelas, education_program_id, class_type, grade_level, academic_year_id)
```

- **Query Optimization**: Eager loads `activeClassGroups.educationProgram` and `activeClassGroups.academicYear` to avoid N+1 query overhead.
- **Transactions**: All bulk actions run inside `DB::transaction()` to ensure atomicity.

---

## Automated Test Coverage (`tests/Feature/StudentPromotionTest.php`)
- **Authentication & Authorization**: Admin & Superadmin access allowed; unauthorized roles blocked (403).
- **Filters**: Search by name/NIS, education program, class type, source class, membership status, and hide target checkbox verified.
- **Action Modes**: Add vs Transfer behavior, old membership closure, non-regular protection, duplicate detection tested.
- **Pagination & Query String**: Query state preservation verified.
