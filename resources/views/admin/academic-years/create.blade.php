<x-app-layout>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <h2 class="text-xl font-semibold mb-4">
        {{ isset($academicYear) ? 'Edit' : 'Tambah' }} Tahun Ajaran
    </h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
            <ul class="text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif


    <form method="POST" action="{{ isset($academicYear) ? route('academic-years.update', $academicYear) : route('academic-years.store') }}">
        @csrf
        @if (isset($academicYear)) @method('PUT') @endif

        <div class="mb-4">
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name', $academicYear->name ?? '') }}" class="border rounded w-full p-2">
        </div>

        <div class="mb-4">
            <label>Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ old('start_date', $academicYear->start_date ?? '') }}" class="border rounded w-full p-2">
        </div>

        <div class="mb-4">
            <label>Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ old('end_date', $academicYear->end_date ?? '') }}" class="border rounded w-full p-2">
        </div>

        <div class="mb-4">
            <label><input type="checkbox" name="is_active" {{ old('is_active', $academicYear->is_active ?? false) ? 'checked' : '' }}> Jadikan Aktif</label>
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            {{ isset($academicYear) ? 'Update' : 'Simpan' }}
        </button>
    </form>
</x-app-layout>
