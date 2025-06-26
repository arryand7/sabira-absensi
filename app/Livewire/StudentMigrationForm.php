<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\Student;
use Illuminate\Support\Str;

class StudentMigrationForm extends Component
{
    public $toClassId;
    public $search = '';
    public $availableStudents = [];
    public $selectedStudents = [];

    public function updated($propertyName)
    {
        if ($propertyName === 'search') {
            if (strlen($this->search) >= 2) {
                $this->availableStudents = Student::query()
                    ->where('nama_lengkap', 'like', '%' . $this->search . '%')
                    ->orWhere('nis', 'like', '%' . $this->search . '%')
                    ->get()
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->nama_lengkap,
                        'nis' => $s->nis,
                    ])
                    ->toArray();
            } else {
                $this->availableStudents = [];
            }
        }
    }

    public function addStudent($studentId)
    {
        $student = collect($this->availableStudents)->firstWhere('id', $studentId);
        if ($student && !collect($this->selectedStudents)->contains('id', $studentId)) {
            $this->selectedStudents[] = $student;
        }

        // Reset search
        $this->search = '';
        $this->availableStudents = [];
    }

    public function removeStudent($studentId)
    {
        $this->selectedStudents = collect($this->selectedStudents)
            ->reject(fn($s) => $s['id'] === $studentId)
            ->values()
            ->toArray();
    }

    public function promoteStudents()
    {
        $toClass = ClassGroup::find($this->toClassId);
        if (!$toClass) {
            session()->flash('error', 'Kelas tujuan tidak valid.');
            return;
        }

        foreach ($this->selectedStudents as $student) {
            ClassGroupStudent::updateOrCreate(
                [
                    'student_id' => $student['id'],
                    'academic_year_id' => $toClass->academic_year_id,
                ],
                [
                    'class_group_id' => $this->toClassId,
                ]
            );
        }

        session()->flash('success', count($this->selectedStudents) . ' siswa berhasil dipindahkan.');
        $this->selectedStudents = [];
        $this->availableStudents = [];
        $this->search = '';
    }

    public function render()
    {
        $toClasses = ClassGroup::with('academicYear')
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->get();

        return view('livewire.student-migration-form', [
            'toClasses' => $toClasses,
        ]);
    }
}
