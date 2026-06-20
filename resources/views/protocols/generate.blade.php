@extends('layouts.app')

@section('title', 'Генерация протокола')

@section('content')
<div class="container">
    <h2>Генерация протокола</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('protocols.generate.post') }}">
        @csrf
        <div class="mb-3">
            <label for="school_code" class="form-label">Школа / Институт</label>
            <select name="school_code" id="school_code" class="form-select" required>
                @foreach($schools as $school)
                    <option value="{{ $school->code }}">{{ $school->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="academic_year" class="form-label">Учебный год</label>
            <select name="academic_year" id="academic_year" class="form-select" required>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="month" class="form-label">Месяц</label>
                <select name="month" id="month" class="form-select" required>
                    <option value="1">Январь</option>
                    <option value="2">Февраль</option>
                    <option value="3">Март</option>
                    <option value="4">Апрель</option>
                    <option value="5">Май</option>
                    <option value="6">Июнь</option>
                    <option value="7">Июль</option>
                    <option value="8">Август</option>
                    <option value="9" selected>Сентябрь</option>
                    <option value="10">Октябрь</option>
                    <option value="11">Ноябрь</option>
                    <option value="12">Декабрь</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="day" class="form-label">День</label>
                <input type="number" name="day" id="day" class="form-control" value="1" min="1" max="31" required>
            </div>
            <div class="col-md-4">
                <label for="protocol_number" class="form-label">Номер протокола</label>
                <input type="text" name="protocol_number" id="protocol_number" class="form-control" placeholder="001" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Сгенерировать и скачать</button>
    </form>
</div>
@endsection