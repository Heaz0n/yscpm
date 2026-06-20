@extends('layouts.app')

@section('title', 'Дашборд | Отчеты и аналитика')

@section('content')
<style>
    /* ВСЕ СТИЛИ ИЗ ОРИГИНАЛЬНОГО ФАЙЛА – копируем без изменений */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Roboto', sans-serif; background: #f0f2f5; min-height: 100vh; color: #1e293b; }
    .container { max-width: 1600px; margin: 0 auto; padding: 20px; }
    .dashboard-header { background: white; border-radius: 16px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
    .dashboard-header h1 { font-size: 26px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 12px; }
    .dashboard-header h1 i { color: #3b82f6; background: #eef2ff; padding: 10px; border-radius: 12px; }
    .action-bar { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border: none; border-radius: 30px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; background: white; color: #1e293b; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-primary { background: #3b82f6; color: white; border: none; box-shadow: 0 4px 6px -1px rgba(59,130,246,0.3); }
    .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
    .btn-outline { background: white; color: #3b82f6; border: 1px solid #3b82f6; }
    .btn-outline:hover { background: #eef2ff; }
    .btn:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .filters-panel { background: white; border-radius: 16px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 6px; min-width: 160px; }
    .filter-group label { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-group select { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 30px; font-size: 14px; background: white; transition: border 0.2s; cursor: pointer; }
    .filter-group select:hover, .filter-group select:focus { border-color: #3b82f6; outline: none; }
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px; transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-2px); }
    .kpi-icon { width: 56px; height: 56px; border-radius: 16px; background: #eef2ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .kpi-content h3 { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 4px; }
    .kpi-content .value { font-size: 26px; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .kpi-content .unit { font-size: 14px; color: #94a3b8; margin-left: 4px; }
    .chart-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
    .chart-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .chart-card.full-width { grid-column: span 2; }
    .chart-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .chart-header h2 { font-size: 18px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .chart-controls { display: flex; gap: 8px; }
    .chart-controls button { background: #f1f5f9; border: none; border-radius: 30px; padding: 6px 12px; font-size: 13px; cursor: pointer; color: #475569; transition: all 0.2s; }
    .chart-controls button.active { background: #3b82f6; color: white; }
    .chart-wrapper { height: 280px; position: relative; }
    .table-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 30px; overflow-x: auto; }
    .table-container h2 { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 14px 8px; background: #f8fafc; font-weight: 600; font-size: 14px; color: #475569; border-bottom: 2px solid #e2e8f0; }
    td { padding: 12px 8px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    .text-right { text-align: right; }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
    .modal.active { display: flex; }
    .modal-content { background: white; border-radius: 24px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
    .color-picker-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1100; }
    .color-picker-modal.active { display: flex; }
    .color-picker-container { background: white; border-radius: 24px; padding: 24px; width: 320px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
    .color-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
    .color-option { width: 100%; aspect-ratio: 1 / 1; border-radius: 12px; cursor: pointer; border: 2px solid transparent; }
    .color-option:hover { transform: scale(1.05); border-color: #fff; box-shadow: 0 0 0 2px #3b82f6; }
    .color-hex-input { display: flex; gap: 8px; margin-bottom: 20px; }
    .color-hex-input input { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 30px; font-family: monospace; font-size: 14px; }
    .color-hex-input button { padding: 10px 16px; background: #3b82f6; color: white; border: none; border-radius: 30px; cursor: pointer; }
    .color-picker-actions { display: flex; justify-content: flex-end; gap: 12px; }
    .color-picker-actions button { padding: 8px 16px; border-radius: 30px; cursor: pointer; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .close-modal { background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; }
    .loader { border: 4px solid #f3f3f3; border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; display: none; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .color-indicator { display: inline-block; width: 20px; height: 20px; border-radius: 4px; margin-left: 8px; cursor: pointer; border: 1px solid #cbd5e1; vertical-align: middle; }
    @media (max-width: 1024px) { .chart-grid { grid-template-columns: 1fr; } .chart-card.full-width { grid-column: span 1; } }
</style>

<div class="container mt-4">
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-pie"></i> Аналитический дашборд</h1>
        <div class="action-bar">
            <button class="btn" id="exportCsvBtn"><i class="fas fa-download"></i> Экспорт CSV</button>
            <a href="#" class="btn btn-outline" id="generateProtocolBtn"><i class="fas fa-file-pdf"></i> Сформировать протокол</a>
            <button class="btn btn-primary" id="refreshBtn"><i class="fas fa-sync-alt"></i> Обновить</button>
        </div>
    </div>

    <div class="filters-panel">
        <div class="filter-group">
            <label for="academic_year">Учебный год</label>
            <select name="academic_year" id="academic_year">
                @foreach($academic_years as $year)
                    <option value="{{ $year }}" {{ $year == $current_academic_year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="month_from">С месяц</label>
            <select name="month_from" id="month_from">
                <option value="0">Все</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ $months_ru[$m] }}</option>
                @endfor
            </select>
        </div>
        <div class="filter-group">
            <label for="month_to">По месяц</label>
            <select name="month_to" id="month_to">
                <option value="0">Все</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ $months_ru[$m] }}</option>
                @endfor
            </select>
        </div>
        @if($user_role === 'admin')
        <div class="filter-group">
            <label for="school">Школа</label>
            <select name="school" id="school">
                <option value="all">Все школы</option>
                @foreach($schools as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div style="flex-grow:1;"></div>
        <button class="btn btn-outline" id="resetFilters"><i class="fas fa-undo-alt"></i> Сброс</button>
    </div>

    <div id="loader" class="loader"></div>
    <div class="kpi-grid" id="kpiContainer"></div>

    <div class="chart-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h2><i class="fas fa-chart-pie" style="color:#3b82f6;"></i> По категориям</h2>
            </div>
            <div class="chart-wrapper"><canvas id="categoriesChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-header">
                <h2><i class="fas fa-chart-line" style="color:#3b82f6;"></i> Динамика выплат 
                    <span class="color-indicator" id="globalLineColorIndicator" style="background-color:#4e79a7;" title="Цвет линии (глобальный)"></span>
                </h2>
                <div class="chart-controls" id="monthlyChartControls">
                    <button class="active" data-type="bar">Столбцы</button>
                    <button data-type="line">Линия</button>
                </div>
            </div>
            <div class="chart-wrapper"><canvas id="monthlyChart"></canvas></div>
            <div style="font-size:12px; color:#64748b; text-align:center; margin-top:8px;">
                <i class="fas fa-mouse-pointer"></i> Клик на столбец/точку → изменить цвет этого месяца; клик на линию → изменить цвет линии
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">
                <h2><i class="fas fa-wallet" style="color:#3b82f6;"></i> По бюджетам</h2>
            </div>
            <div class="chart-wrapper"><canvas id="budgetChart"></canvas></div>
        </div>
        <div class="chart-card" id="schoolsChartCard" style="display: none;">
            <div class="chart-header">
                <h2><i class="fas fa-school" style="color:#3b82f6;"></i> По школам</h2>
            </div>
            <div class="chart-wrapper"><canvas id="schoolsChart"></canvas></div>
        </div>
    </div>

    <div class="table-container">
        <h2><i class="fas fa-trophy" style="color:#f59e0b;"></i> Топ-10 студентов по сумме выплат</h2>
        <table>
            <thead><tr><th>#</th><th>ФИО студента</th><th>Группа</th><th class="text-right">Сумма (₽)</th></tr></thead>
            <tbody id="topStudentsBody"></tbody>
        </table>
    </div>

    <div class="table-container">
        <h2><i class="fas fa-list" style="color:#3b82f6;"></i> Детализация по категориям</h2>
        <table>
            <thead><tr><th>Категория</th><th class="text-right">Количество людей</th><th class="text-right">Сумма (₽)</th></tr></thead>
            <tbody id="categoriesBody"></tbody>
        </table>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <a href="javascript:history.back()" class="btn"><i class="fas fa-arrow-left"></i> Назад</a>
    </div>
</div>

<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalCategoryTitle">Студенты категории</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div id="modalContent" style="max-height: 400px; overflow-y: auto;"></div>
    </div>
</div>

<div id="colorPickerModal" class="color-picker-modal">
    <div class="color-picker-container">
        <h4>Выберите цвет <button id="closeColorPickerBtn">&times;</button></h4>
        <div class="color-grid" id="colorGrid"></div>
        <div class="color-hex-input">
            <input type="text" id="colorHexInput" placeholder="#RRGGBB" maxlength="7">
            <button id="applyHexBtn">Применить</button>
        </div>
        <div class="color-picker-actions">
            <button id="cancelColorBtn" class="btn-outline">Отмена</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ---------- Кастомный пикер цвета ----------
    const colorPickerModal = document.getElementById('colorPickerModal');
    const colorGrid = document.getElementById('colorGrid');
    const colorHexInput = document.getElementById('colorHexInput');
    const applyHexBtn = document.getElementById('applyHexBtn');
    const cancelColorBtn = document.getElementById('cancelColorBtn');
    const closeColorPickerBtn = document.getElementById('closeColorPickerBtn');
    let currentColorCallback = null;
    const presetColors = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac', '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];
    function buildColorGrid() {
        colorGrid.innerHTML = '';
        presetColors.forEach(color => {
            const div = document.createElement('div');
            div.className = 'color-option';
            div.style.backgroundColor = color;
            div.addEventListener('click', () => { if (currentColorCallback) { currentColorCallback(color); closeColorPicker(); } });
            colorGrid.appendChild(div);
        });
    }
    function openColorPicker(initialColor, onSelect) {
        currentColorCallback = onSelect;
        colorHexInput.value = initialColor;
        colorPickerModal.classList.add('active');
    }
    function closeColorPicker() {
        colorPickerModal.classList.remove('active');
        currentColorCallback = null;
    }
    function applyHexColor() {
        let hex = colorHexInput.value.trim();
        if (!hex.startsWith('#')) hex = '#' + hex;
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            if (currentColorCallback) { currentColorCallback(hex); closeColorPicker(); }
        } else alert('Введите корректный HEX-цвет, например #3b82f6');
    }
    colorPickerModal.addEventListener('click', (e) => { if (e.target === colorPickerModal) closeColorPicker(); });
    closeColorPickerBtn.addEventListener('click', closeColorPicker);
    cancelColorBtn.addEventListener('click', closeColorPicker);
    applyHexBtn.addEventListener('click', applyHexColor);
    buildColorGrid();

    // ---------- Основной код дашборда ----------
    let categoriesChart, monthlyChart, budgetChart, schoolsChart;
    let currentMonthlyChartType = 'bar';
    let currentMonthlyData = [];
    let monthColors = {};
    let globalLineColor = '#4e79a7';

    function loadColors() {
        const savedMonthColors = localStorage.getItem('monthlyChartMonthColors');
        if (savedMonthColors) { try { monthColors = JSON.parse(savedMonthColors); } catch(e) {} }
        const savedGlobalLine = localStorage.getItem('monthlyChartGlobalLineColor');
        if (savedGlobalLine) { globalLineColor = savedGlobalLine; document.getElementById('globalLineColorIndicator').style.backgroundColor = globalLineColor; }
    }
    function saveMonthColors() { localStorage.setItem('monthlyChartMonthColors', JSON.stringify(monthColors)); }
    function saveGlobalLineColor() { localStorage.setItem('monthlyChartGlobalLineColor', globalLineColor); document.getElementById('globalLineColorIndicator').style.backgroundColor = globalLineColor; }
    function getColorForMonth(month, defaultColor = '#4e79a7') { return monthColors[month] || defaultColor; }
    function setColorForMonth(month, color) { monthColors[month] = color; saveMonthColors(); updateMonthlyChart(); }
    function showColorPickerForMonth(monthNumber) { openColorPicker(getColorForMonth(monthNumber, '#4e79a7'), (newColor) => setColorForMonth(monthNumber, newColor)); }
    function showColorPickerForGlobalLine() { openColorPicker(globalLineColor, (newColor) => { globalLineColor = newColor; saveGlobalLineColor(); updateMonthlyChart(); }); }

    async function loadReportData() {
        const year = document.getElementById('academic_year').value;
        const month_from = document.getElementById('month_from').value;
        const month_to = document.getElementById('month_to').value;
        const school = document.getElementById('school') ? document.getElementById('school').value : '{{ $user_school_code }}';
        document.getElementById('loader').style.display = 'block';
        const url = `{{ route('reports.data') }}?academic_year=${year}&month_from=${month_from}&month_to=${month_to}&school=${school}`;
        try {
            const response = await fetch(url);
            const data = await response.json();
            updateKPI(data.metrics, data.totalProtocols);
            updateTopStudents(data.topStudents);
            updateCategoriesTable(data.categories);
            updateOtherCharts(data);
            currentMonthlyData = data.monthly || [];
            updateMonthlyChart();
            const schoolsCard = document.getElementById('schoolsChartCard');
            if (data.schools && data.schools.length > 0) schoolsCard.style.display = 'block';
            else schoolsCard.style.display = 'none';
            updateGenerateProtocolLink();
        } catch (error) { console.error(error); }
        finally { document.getElementById('loader').style.display = 'none'; }
    }
    function updateKPI(metrics, totalProtocols) {
        const container = document.getElementById('kpiContainer');
        container.innerHTML = `
            <div class="kpi-card"><div class="kpi-icon"><i class="fas fa-ruble-sign"></i></div><div class="kpi-content"><h3>Общая сумма выплат</h3><div class="value">${new Intl.NumberFormat('ru-RU').format(metrics.total_amount)} <span class="unit">₽</span></div></div></div>
            <div class="kpi-card"><div class="kpi-icon"><i class="fas fa-users"></i></div><div class="kpi-content"><h3>Количество людей</h3><div class="value">${new Intl.NumberFormat('ru-RU').format(metrics.total_students)}</div></div></div>
            <div class="kpi-card"><div class="kpi-icon"><i class="fas fa-credit-card"></i></div><div class="kpi-content"><h3>Количество выплат</h3><div class="value">${new Intl.NumberFormat('ru-RU').format(metrics.total_payments)}</div></div></div>
            <div class="kpi-card"><div class="kpi-icon"><i class="fas fa-file-alt"></i></div><div class="kpi-content"><h3>Сгенерировано протоколов</h3><div class="value">${new Intl.NumberFormat('ru-RU').format(totalProtocols)}</div></div></div>
            <div class="kpi-card"><div class="kpi-icon"><i class="fas fa-calculator"></i></div><div class="kpi-content"><h3>Средний размер выплаты</h3><div class="value">${new Intl.NumberFormat('ru-RU').format(metrics.avg_amount)} <span class="unit">₽</span></div></div></div>
        `;
    }
    function updateTopStudents(students) {
        const tbody = document.getElementById('topStudentsBody');
        if (!students.length) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Нет данных</td></tr>'; return; }
        let html = '';
        students.forEach((s, idx) => { html += `<tr><td>${idx+1}</td><td>${escapeHtml(s.full_name)}</td><td>${escapeHtml(s.group_name)}</td><td class="text-right">${new Intl.NumberFormat('ru-RU').format(s.total)}</td></tr>`; });
        tbody.innerHTML = html;
    }
    function updateCategoriesTable(categories) {
        const tbody = document.getElementById('categoriesBody');
        if (!categories.length) { tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Нет данных</td></tr>'; return; }
        let html = '';
        categories.forEach(cat => { html += `<tr><td>${escapeHtml(cat.category_short)}</td><td class="text-right">${new Intl.NumberFormat('ru-RU').format(cat.cnt)}</td><td class="text-right">${new Intl.NumberFormat('ru-RU').format(cat.total)}</td></tr>`; });
        tbody.innerHTML = html;
    }
    function updateOtherCharts(data) { updateCategoriesPie(data.categories); updateBudgetChart(data.budgets); updateSchoolsChart(data.schools); }
    function updateCategoriesPie(categories) {
        if (categoriesChart) categoriesChart.destroy();
        const ctx = document.getElementById('categoriesChart').getContext('2d');
        const filtered = categories.filter(c => Number(c.total) > 0);
        if (!filtered.length) { ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height); ctx.fillText('Нет данных', ctx.canvas.width/2, ctx.canvas.height/2); return; }
        const totals = filtered.map(c => c.total);
        const colors = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7','#9c755f','#bab0ac'];
        categoriesChart = new Chart(ctx, {
            type: 'pie',
            data: { labels: filtered.map(c=>c.category_short), datasets: [{ data: totals, backgroundColor: colors.slice(0,filtered.length), borderWidth:1, borderColor:'#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${new Intl.NumberFormat('ru-RU').format(ctx.raw)} ₽` } } }, onClick: (e, item) => { if(item.length){ const index=item[0].index; showCategoryDetail(filtered[index].id, filtered[index].category_short); } } }
        });
    }
    function updateMonthlyChart() {
        if (!currentMonthlyData.length) { const ctx = document.getElementById('monthlyChart').getContext('2d'); if(monthlyChart) monthlyChart.destroy(); ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height); ctx.fillText('Нет данных', ctx.canvas.width/2, ctx.canvas.height/2); return; }
        const monthNames = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        const labels = currentMonthlyData.map(item => monthNames[item.month-1] || item.month);
        const data = currentMonthlyData.map(item => item.total);
        const monthsNumbers = currentMonthlyData.map(item => item.month);
        if (monthlyChart) monthlyChart.destroy();
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        if (currentMonthlyChartType === 'bar') {
            const bgColors = monthsNumbers.map(m => getColorForMonth(m, '#4e79a7'));
            monthlyChart = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Сумма выплат (₽)', data, backgroundColor: bgColors, borderColor: bgColors, borderWidth:1, borderRadius:4, barPercentage:0.7, categoryPercentage:0.8 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('ru-RU').format(v)+' ₽' } } }, onClick: (e, active) => { if(active.length) showColorPickerForMonth(monthsNumbers[active[0].index]); } }
            });
        } else {
            const pointColors = monthsNumbers.map(m => getColorForMonth(m, '#4e79a7'));
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets: [{ label: 'Сумма выплат (₽)', data, borderColor: globalLineColor, backgroundColor: 'transparent', borderWidth:3, pointBackgroundColor: pointColors, pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:5, pointHoverRadius:7, tension:0.3, fill:false }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('ru-RU').format(v)+' ₽' } } }, onClick: (e, active) => { if(active.length && active[0].datasetIndex===0) showColorPickerForMonth(monthsNumbers[active[0].index]); else showColorPickerForGlobalLine(); } }
            });
        }
    }
    function updateBudgetChart(budgets) {
        if (budgetChart) budgetChart.destroy();
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const filtered = budgets.filter(b => Number(b.total)>0);
        if (!filtered.length) { ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height); ctx.fillText('Нет данных', ctx.canvas.width/2, ctx.canvas.height/2); return; }
        budgetChart = new Chart(ctx, {
            type: 'doughnut',
            data: { labels: filtered.map(b=>b.budget), datasets: [{ data: filtered.map(b=>b.total), backgroundColor: ['#4e79a7','#f28e2b','#e15759'], borderWidth:1, borderColor:'#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${new Intl.NumberFormat('ru-RU').format(ctx.raw)} ₽` } } } }
        });
    }
    function updateSchoolsChart(schools) {
        if (!schools || !schools.length) return;
        if (schoolsChart) schoolsChart.destroy();
        const ctx = document.getElementById('schoolsChart').getContext('2d');
        schoolsChart = new Chart(ctx, {
            type: 'bar',
            data: { labels: schools.map(s=>s.school_name), datasets: [{ label: 'Сумма выплат (₽)', data: schools.map(s=>Number(s.total)), backgroundColor: 'rgba(59,130,246,0.7)', borderColor: 'rgba(59,130,246,1)', borderWidth:1, borderRadius:8 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('ru-RU').format(v)+' ₽' } } }, plugins: { tooltip: { callbacks: { label: ctx => new Intl.NumberFormat('ru-RU').format(ctx.raw)+' ₽' } } } }
        });
    }
    async function showCategoryDetail(categoryId, categoryName) {
        const year = document.getElementById('academic_year').value;
        const month_from = document.getElementById('month_from').value;
        const month_to = document.getElementById('month_to').value;
        const school = document.getElementById('school') ? document.getElementById('school').value : '{{ $user_school_code }}';
        document.getElementById('loader').style.display = 'block';
        const url = `{{ route('reports.category-detail') }}?category_id=${categoryId}&academic_year=${year}&month_from=${month_from}&month_to=${month_to}&school=${school}`;
        try {
            const resp = await fetch(url);
            const students = await resp.json();
            document.getElementById('modalCategoryTitle').innerText = `Студенты категории: ${categoryName}`;
            const modalContent = document.getElementById('modalContent');
            if (!students.length) modalContent.innerHTML = '<p style="text-align:center;">Нет студентов</p>';
            else {
                let html = '<table style="width:100%;"><tr><th>ФИО</th><th>Группа</th><th>Сумма (₽)</th><th>Месяц</th></tr>';
                students.forEach(s => { html += `<tr><td>${escapeHtml(s.full_name)}</td><td>${escapeHtml(s.group_name)}</td><td class="text-right">${new Intl.NumberFormat('ru-RU').format(s.amount)}</td><td>${s.month}</td></tr>`; });
                html += '</table>';
                modalContent.innerHTML = html;
            }
            document.getElementById('categoryModal').classList.add('active');
        } catch(e) { console.error(e); }
        finally { document.getElementById('loader').style.display = 'none'; }
    }
    function closeModal() { document.getElementById('categoryModal').classList.remove('active'); }
    function exportToCsv() {
        let csv = "Топ-10 студентов\nМесто,ФИО,Группа,Сумма (₽)\n";
        document.querySelectorAll('#topStudentsBody tr').forEach((tr, idx) => {
            const tds = tr.querySelectorAll('td');
            if(tds.length===4) csv += `${idx+1},${tds[0].innerText},${tds[1].innerText},${tds[2].innerText}\n`;
        });
        csv += "\nДетализация по категориям\nКатегория,Количество,Сумма (₽)\n";
        document.querySelectorAll('#categoriesBody tr').forEach(tr => {
            const tds = tr.querySelectorAll('td');
            if(tds.length===3) csv += `${tds[0].innerText},${tds[1].innerText},${tds[2].innerText}\n`;
        });
        const blob = new Blob(["\uFEFF"+csv], {type: 'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `report_${new Date().toISOString().slice(0,10)}.csv`;
        link.click();
    }
    function updateGenerateProtocolLink() {
        const year = document.getElementById('academic_year').value;
        const month_from = document.getElementById('month_from').value;
        const month_to = document.getElementById('month_to').value;
        const school = document.getElementById('school') ? document.getElementById('school').value : '{{ $user_school_code }}';
        let url = '{{ route("protocols.generate") }}?academic_year='+year;
        if (month_from != 0) url += `&month_from=${month_from}`;
        if (month_to != 0) url += `&month_to=${month_to}`;
        if (school && school !== 'all') url += `&school=${school}`;
        document.getElementById('generateProtocolBtn').href = url;
    }
    function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

    document.addEventListener('DOMContentLoaded', function() {
        loadColors();
        loadReportData();
        document.getElementById('academic_year').addEventListener('change', loadReportData);
        document.getElementById('month_from').addEventListener('change', loadReportData);
        document.getElementById('month_to').addEventListener('change', loadReportData);
        if (document.getElementById('school')) document.getElementById('school').addEventListener('change', loadReportData);
        document.getElementById('refreshBtn').addEventListener('click', loadReportData);
        document.getElementById('resetFilters').addEventListener('click', () => {
            document.getElementById('academic_year').value = '{{ $current_academic_year }}';
            document.getElementById('month_from').value = '0';
            document.getElementById('month_to').value = '0';
            if(document.getElementById('school')) document.getElementById('school').value = 'all';
            loadReportData();
        });
        document.querySelectorAll('#monthlyChartControls button').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#monthlyChartControls button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentMonthlyChartType = this.dataset.type;
                updateMonthlyChart();
            });
        });
        document.getElementById('exportCsvBtn').addEventListener('click', exportToCsv);
        window.addEventListener('click', (e) => { if(e.target === document.getElementById('categoryModal')) closeModal(); });
        document.getElementById('globalLineColorIndicator')?.addEventListener('click', (e) => { e.stopPropagation(); showColorPickerForGlobalLine(); });
    });
</script>
@endpush