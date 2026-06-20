@extends('layouts.app')

@section('title', 'Категории материальной поддержки')

@section('content')
<style>
    /* Стили для уведомлений */
    .action-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    }
    .action-notification.error {
        background-color: #dc3545;
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; visibility: visible; }
        to { opacity: 0; visibility: hidden; }
    }

    /* Кнопка выплат (фиолетовая, как в оригинале) */
    .btn-payout {
        background-color: #6f42c1;
        color: white;
        border: none;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    .btn-payout:hover {
        background-color: #5e35b1;
    }

    .payout-modal .modal-dialog { max-width: 350px; }
    .payout-modal .modal-body { padding: 1.5rem; }
    .payout-input {
        text-align: center;
        font-size: 2rem;
        font-weight: 600;
        color: #0d6efd;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 0.5rem;
        width: 100%;
        margin: 1rem 0;
    }
    .payout-input:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.25);
    }
    .payout-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    .payout-result {
        margin-top: 1rem;
        padding: 1rem;
        background-color: #e8f5e9;
        border-radius: 8px;
        text-align: center;
        display: none;
    }
    .payout-result.show {
        display: block;
    }
    .payout-result-text {
        font-size: 1.2rem;
        font-weight: 500;
        color: #2e7d32;
    }
    .editable-cell {
        cursor: pointer;
    }
    .editing {
        background-color: #fff8e1;
    }
    .row-highlight {
        background-color: #fff3cd !important;
        transition: background-color 1s;
    }
</style>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <h2 class="mb-0"><i class="bi bi-cash-stack"></i> Категории для оказания материальной поддержки</h2>
        <div class="ms-auto">
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Добавить
            </button>
            <button class="btn-payout" data-bs-toggle="modal" data-bs-target="#payoutModal">
                <i class="bi bi-calendar-check"></i> Выплат: <span id="payoutCountDisplay">{{ session('payout_count', 4) }}</span>
            </button>
        </div>
    </div>

    <p class="mb-4">Ниже представлен перечень категорий и документов, необходимых для получения материальной поддержки.</p>

    @if(session('notification'))
        <div class="action-notification {{ strpos(session('notification'), 'Ошибка') === 0 ? 'error' : '' }}" id="actionNotification">
            <span>{{ session('notification') }}</span>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()"></button>
        </div>
    @endif

    @if($categories->isEmpty())
        <div class="alert alert-info">Нет данных для отображения. Добавьте первую категорию.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="categoriesTable">
                <thead class="table-primary">
                    <tr>
                        <th>№ п/п</th>
                        <th>Категория</th>
                        <th>Категория (сокращенно)</th>
                        <th>Перечень подтверждающих документов</th>
                        <th>Периодичность выплат</th>
                        <th>Максимальная сумма / Условие</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    @foreach($categories as $category)
                        @php
                            $currentCondition = $category->amount_condition ?? 'fixed';
                            $amountDisplay = $currentCondition === 'expense_limit'
                                ? 'В объёме затрат, но не более ' . number_format($category->max_amount, 2, '.', ' ') . ' руб.'
                                : number_format($category->max_amount, 2, '.', ' ') . ' руб.';
                        @endphp
                        <tr data-id="{{ $category->id }}">
                            <td class="editable-number editable-cell">{{ $category->number }}</td>
                            <td class="editable-name editable-cell">{{ $category->category_name }}</td>
                            <td class="editable-short editable-cell">{{ $category->category_short }}</td>
                            <td class="editable-docs editable-cell">
                                <ul class="mb-0">
                                    @foreach(explode("\n", $category->documents_list) as $doc)
                                        @if(trim($doc))
                                            <li>{{ $doc }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </td>
                            <td class="editable-frequency editable-cell">{{ $category->payment_frequency }}</td>
                            <td class="editable-amount editable-cell" data-raw="{{ $category->max_amount }}" data-condition="{{ $currentCondition }}">
                                {{ $amountDisplay }}
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-btn"
                                    data-id="{{ $category->id }}"
                                    data-number="{{ $category->number }}"
                                    data-category-name="{{ $category->category_name }}"
                                    data-category-short="{{ $category->category_short }}"
                                    data-documents-list="{{ $category->documents_list }}"
                                    data-payment-frequency="{{ $category->payment_frequency }}"
                                    data-max-amount="{{ $category->max_amount }}"
                                    data-condition="{{ $currentCondition }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Модальное окно для количества выплат -->
<div class="modal fade payout-modal" id="payoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Количество выплат за год</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <label for="payoutCount" class="form-label">Введите число от 2 до 4:</label>
                <input type="number" class="payout-input" id="payoutCount" min="2" max="4" step="1" value="{{ session('payout_count', 4) }}">
                <div class="text-danger small" id="payoutError" style="display: none;">Нужно целое число от 2 до 4</div>
                <div class="payout-description">
                    <i class="bi bi-info-circle"></i> Сколько раз в году будут производиться выплаты
                </div>
                <button class="btn btn-primary w-100 mt-3" id="confirmPayoutBtn">
                    <i class="bi bi-check-lg"></i> Утвердить
                </button>
                <div class="payout-result" id="payoutResult">
                    <div class="payout-result-text" id="payoutResultText"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Добавление категории</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}" id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newNumber" class="form-label">№ п/п <span class="text-muted">(например: 5.2.1)</span></label>
                        <input type="text" class="form-control" id="newNumber" name="number" required>
                        <div class="error-message text-danger" id="numberError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newCategoryName" class="form-label">Категория</label>
                        <input type="text" class="form-control" id="newCategoryName" name="category_name" required>
                        <div class="error-message text-danger" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newCategoryShort" class="form-label">Категория (сокращенно)</label>
                        <input type="text" class="form-control" id="newCategoryShort" name="category_short" required>
                        <div class="error-message text-danger" id="shortError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newDocumentsList" class="form-label">Перечень подтверждающих документов (каждый документ с новой строки)</label>
                        <textarea class="form-control" id="newDocumentsList" name="documents_list" rows="5" required></textarea>
                        <div class="error-message text-danger" id="docsError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newPaymentFrequency" class="form-label">Периодичность выплат</label>
                        <input type="text" class="form-control" id="newPaymentFrequency" name="payment_frequency" required>
                        <div class="error-message text-danger" id="frequencyError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Условие для суммы</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condition" id="addConditionFixed" value="fixed" checked>
                                <label class="form-check-label" for="addConditionFixed">Фиксированная максимальная сумма</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condition" id="addConditionExpense" value="expense_limit">
                                <label class="form-check-label" for="addConditionExpense">В объёме затрат, но не более</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newMaxAmount" class="form-label">Сумма (руб.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="newMaxAmount" name="max_amount" required>
                        <div class="error-message text-danger" id="amountError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">Добавить категорию</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Редактирование категории</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNumber" class="form-label">№ п/п <span class="text-muted">(например: 5.2.1)</span></label>
                        <input type="text" class="form-control" id="editNumber" name="number" required>
                        <div class="error-message text-danger" id="editNumberError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Категория</label>
                        <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                        <div class="error-message text-danger" id="editNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryShort" class="form-label">Категория (сокращенно)</label>
                        <input type="text" class="form-control" id="editCategoryShort" name="category_short" required>
                        <div class="error-message text-danger" id="editShortError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editDocumentsList" class="form-label">Перечень подтверждающих документов</label>
                        <textarea class="form-control" id="editDocumentsList" name="documents_list" rows="5" required></textarea>
                        <div class="error-message text-danger" id="editDocsError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentFrequency" class="form-label">Периодичность выплат</label>
                        <input type="text" class="form-control" id="editPaymentFrequency" name="payment_frequency" required>
                        <div class="error-message text-danger" id="editFrequencyError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Условие для суммы</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condition" id="editConditionFixed" value="fixed">
                                <label class="form-check-label" for="editConditionFixed">Фиксированная максимальная сумма</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condition" id="editConditionExpense" value="expense_limit">
                                <label class="form-check-label" for="editConditionExpense">В объёме затрат, но не более</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editMaxAmount" class="form-label">Сумма (руб.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="editMaxAmount" name="max_amount" required>
                        <div class="error-message text-danger" id="editAmountError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning" id="saveEditBtn">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Автоудаление уведомления
        const notif = document.getElementById('actionNotification');
        if (notif) {
            setTimeout(() => {
                notif.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => notif.remove(), 500);
            }, 5000);
        }

        const payoutCountDisplay = document.getElementById('payoutCountDisplay');
        let payoutCount = parseInt(payoutCountDisplay.textContent) || 4;

        function showNotification(message, isError = false) {
            const div = document.createElement('div');
            div.className = `action-notification ${isError ? 'error' : ''}`;
            div.innerHTML = `<span>${message}</span><button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()"></button>`;
            document.body.appendChild(div);
            setTimeout(() => {
                div.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => div.remove(), 500);
            }, 5000);
        }

        function getRawCellValue(cell) {
            if (cell.classList.contains('editable-docs')) {
                const items = cell.querySelectorAll('li');
                return Array.from(items).map(li => li.textContent).join('\n');
            } else if (cell.classList.contains('editable-amount')) {
                return cell.getAttribute('data-raw') || '0';
            }
            return cell.textContent.trim();
        }

        function setCellValue(cell, value) {
            if (cell.classList.contains('editable-docs')) {
                const docs = value.split('\n').filter(d => d.trim());
                cell.innerHTML = '<ul class="mb-0">' + docs.map(d => `<li>${escapeHtml(d)}</li>`).join('') + '</ul>';
            } else if (cell.classList.contains('editable-amount')) {
                const cond = cell.getAttribute('data-condition') || 'fixed';
                cell.setAttribute('data-raw', value);
                if (cond === 'expense_limit') {
                    cell.textContent = 'В объёме затрат, но не более ' + new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' руб.';
                } else {
                    cell.textContent = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' руб.';
                }
            } else {
                cell.textContent = value;
            }
        }

        function escapeHtml(str) {
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Inline редактирование
        document.querySelectorAll('#categoriesTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td:not(:last-child)');
            cells.forEach(cell => {
                cell.addEventListener('dblclick', function() {
                    if (this.classList.contains('editing')) return;
                    const original = getRawCellValue(this);
                    let input;
                    if (this.classList.contains('editable-docs')) {
                        input = document.createElement('textarea');
                        input.value = original;
                        input.rows = 5;
                    } else if (this.classList.contains('editable-amount')) {
                        input = document.createElement('input');
                        input.type = 'number';
                        input.step = '0.01';
                        input.min = '0';
                        input.value = original;
                    } else {
                        input = document.createElement('input');
                        input.type = 'text';
                        input.value = original;
                    }
                    input.classList.add('form-control');
                    this.innerHTML = '';
                    this.appendChild(input);
                    this.classList.add('editing');
                    input.focus();

                    const save = () => {
                        const newVal = input.value.trim();
                        if (this.classList.contains('editable-number') && !/^[\d]+(\.[\d]+)*$/.test(newVal)) {
                            showNotification('Неверный формат номера', true);
                            setCellValue(this, original);
                            this.classList.remove('editing');
                            return;
                        }
                        if (this.classList.contains('editable-amount') && (newVal === '' || isNaN(newVal) || parseFloat(newVal) < 0)) {
                            showNotification('Сумма должна быть неотрицательным числом', true);
                            setCellValue(this, original);
                            this.classList.remove('editing');
                            return;
                        }
                        const id = row.getAttribute('data-id');
                        let data = {
                            number: row.querySelector('.editable-number').textContent.trim(),
                            category_name: row.querySelector('.editable-name').textContent.trim(),
                            category_short: row.querySelector('.editable-short').textContent.trim(),
                            documents_list: getRawCellValue(row.querySelector('.editable-docs')),
                            payment_frequency: row.querySelector('.editable-frequency').textContent.trim(),
                            max_amount: row.querySelector('.editable-amount').getAttribute('data-raw') || '0',
                            condition: row.querySelector('.editable-amount').getAttribute('data-condition') || 'fixed'
                        };
                        if (this.classList.contains('editable-number')) data.number = newVal;
                        else if (this.classList.contains('editable-name')) data.category_name = newVal;
                        else if (this.classList.contains('editable-short')) data.category_short = newVal;
                        else if (this.classList.contains('editable-docs')) data.documents_list = newVal;
                        else if (this.classList.contains('editable-frequency')) data.payment_frequency = newVal;
                        else if (this.classList.contains('editable-amount')) data.max_amount = newVal;

                        fetch(`{{ url('categories') }}/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setCellValue(this, newVal);
                                const editBtn = row.querySelector('.edit-btn');
                                editBtn.setAttribute('data-number', data.number);
                                editBtn.setAttribute('data-category-name', data.category_name);
                                editBtn.setAttribute('data-category-short', data.category_short);
                                editBtn.setAttribute('data-documents-list', data.documents_list);
                                editBtn.setAttribute('data-payment-frequency', data.payment_frequency);
                                editBtn.setAttribute('data-max-amount', data.max_amount);
                                editBtn.setAttribute('data-condition', data.condition);
                                showNotification('Категория обновлена');
                                row.classList.add('row-highlight');
                                setTimeout(() => row.classList.remove('row-highlight'), 2000);
                            } else {
                                showNotification(res.message || 'Ошибка', true);
                                setCellValue(this, original);
                            }
                            this.classList.remove('editing');
                        })
                        .catch(err => {
                            showNotification('Ошибка сети', true);
                            setCellValue(this, original);
                            this.classList.remove('editing');
                        });
                    };

                    input.addEventListener('keydown', e => {
                        if (e.key === 'Enter' && !(input.tagName === 'TEXTAREA' && e.shiftKey)) {
                            e.preventDefault();
                            save();
                        }
                        if (e.key === 'Escape') {
                            setCellValue(this, original);
                            this.classList.remove('editing');
                        }
                    });
                    input.addEventListener('blur', () => setTimeout(save, 100));
                });
            });
        });

        // Модальное окно выплат
        const payoutModal = document.getElementById('payoutModal');
        const payoutInput = document.getElementById('payoutCount');
        const payoutError = document.getElementById('payoutError');
        const payoutResult = document.getElementById('payoutResult');
        const payoutResultText = document.getElementById('payoutResultText');
        const confirmPayoutBtn = document.getElementById('confirmPayoutBtn');

        if (payoutModal) {
            payoutModal.addEventListener('show.bs.modal', () => {
                payoutInput.value = payoutCount;
                payoutError.style.display = 'none';
                payoutResult.classList.remove('show');
            });
        }
        if (confirmPayoutBtn) {
            confirmPayoutBtn.addEventListener('click', () => {
                const val = payoutInput.value.trim();
                if (val === '') {
                    payoutError.style.display = 'block';
                    payoutResult.classList.remove('show');
                    return;
                }
                const num = Number(val);
                if (!Number.isInteger(num) || num < 2 || num > 4) {
                    payoutError.style.display = 'block';
                    payoutResult.classList.remove('show');
                    return;
                }
                fetch('{{ route("categories.savePayoutCount") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ count: num })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        payoutCount = num;
                        payoutCountDisplay.textContent = num;
                        payoutError.style.display = 'none';
                        const word = {2:'два раза в год',3:'три раза в год',4:'четыре раза в год'}[num];
                        payoutResultText.textContent = `✅ Утверждено: ${num} (${word})`;
                        payoutResult.classList.add('show');
                        showNotification(`Количество выплат изменено на ${num}`);
                    } else {
                        showNotification(data.message || 'Ошибка', true);
                    }
                })
                .catch(() => showNotification('Ошибка сети', true));
            });
        }

        // Редактирование через модалку
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editId').value = this.dataset.id;
                document.getElementById('editNumber').value = this.dataset.number;
                document.getElementById('editCategoryName').value = this.dataset.categoryName;
                document.getElementById('editCategoryShort').value = this.dataset.categoryShort;
                document.getElementById('editDocumentsList').value = this.dataset.documentsList;
                document.getElementById('editPaymentFrequency').value = this.dataset.paymentFrequency;
                document.getElementById('editMaxAmount').value = this.dataset.maxAmount;
                const cond = this.dataset.condition;
                if (cond === 'expense_limit') {
                    document.getElementById('editConditionExpense').checked = true;
                } else {
                    document.getElementById('editConditionFixed').checked = true;
                }
                document.querySelectorAll('#editForm .error-message').forEach(el => el.textContent = '');
            });
        });

        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                const id = document.getElementById('editId').value;
                const number = document.getElementById('editNumber').value.trim();
                if (!/^[\d]+(\.[\d]+)*$/.test(number)) {
                    document.getElementById('editNumberError').textContent = 'Неверный номер';
                    isValid = false;
                } else document.getElementById('editNumberError').textContent = '';
                if (!document.getElementById('editCategoryName').value.trim()) {
                    document.getElementById('editNameError').textContent = 'Введите название';
                    isValid = false;
                } else document.getElementById('editNameError').textContent = '';
                if (!document.getElementById('editCategoryShort').value.trim()) {
                    document.getElementById('editShortError').textContent = 'Введите сокращение';
                    isValid = false;
                } else document.getElementById('editShortError').textContent = '';
                if (!document.getElementById('editDocumentsList').value.trim()) {
                    document.getElementById('editDocsError').textContent = 'Введите документы';
                    isValid = false;
                } else document.getElementById('editDocsError').textContent = '';
                if (!document.getElementById('editPaymentFrequency').value.trim()) {
                    document.getElementById('editFrequencyError').textContent = 'Введите периодичность';
                    isValid = false;
                } else document.getElementById('editFrequencyError').textContent = '';
                const amount = document.getElementById('editMaxAmount').value;
                if (amount === '' || isNaN(amount) || parseFloat(amount) < 0) {
                    document.getElementById('editAmountError').textContent = 'Неверная сумма';
                    isValid = false;
                } else document.getElementById('editAmountError').textContent = '';
                if (!isValid) return;

                const formData = {
                    number: document.getElementById('editNumber').value,
                    category_name: document.getElementById('editCategoryName').value,
                    category_short: document.getElementById('editCategoryShort').value,
                    documents_list: document.getElementById('editDocumentsList').value,
                    payment_frequency: document.getElementById('editPaymentFrequency').value,
                    max_amount: document.getElementById('editMaxAmount').value,
                    condition: document.querySelector('input[name="condition"]:checked').value
                };
                fetch(`{{ url('categories') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(formData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        if (modal) modal.hide();
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.querySelector('.editable-number').textContent = formData.number;
                            row.querySelector('.editable-name').textContent = formData.category_name;
                            row.querySelector('.editable-short').textContent = formData.category_short;
                            const docsCell = row.querySelector('.editable-docs');
                            const docsList = formData.documents_list.split('\n').filter(d => d.trim());
                            docsCell.innerHTML = '<ul class="mb-0">' + docsList.map(d => `<li>${escapeHtml(d)}</li>`).join('') + '</ul>';
                            row.querySelector('.editable-frequency').textContent = formData.payment_frequency;
                            const amountCell = row.querySelector('.editable-amount');
                            amountCell.setAttribute('data-raw', formData.max_amount);
                            amountCell.setAttribute('data-condition', formData.condition);
                            if (formData.condition === 'expense_limit') {
                                amountCell.textContent = 'В объёме затрат, но не более ' + new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(formData.max_amount) + ' руб.';
                            } else {
                                amountCell.textContent = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(formData.max_amount) + ' руб.';
                            }
                            const editBtn = row.querySelector('.edit-btn');
                            editBtn.setAttribute('data-number', formData.number);
                            editBtn.setAttribute('data-category-name', formData.category_name);
                            editBtn.setAttribute('data-category-short', formData.category_short);
                            editBtn.setAttribute('data-documents-list', formData.documents_list);
                            editBtn.setAttribute('data-payment-frequency', formData.payment_frequency);
                            editBtn.setAttribute('data-max-amount', formData.max_amount);
                            editBtn.setAttribute('data-condition', formData.condition);
                            row.classList.add('row-highlight');
                            setTimeout(() => row.classList.remove('row-highlight'), 2000);
                        }
                        showNotification(data.message || 'Категория обновлена');
                    } else {
                        showNotification(data.message || 'Ошибка', true);
                    }
                })
                .catch(err => showNotification('Ошибка сети', true));
            });
        }

        // Очистка форм при закрытии модалок
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('hidden.bs.modal', () => {
                const form = addModal.querySelector('form');
                if (form) form.reset();
                document.querySelectorAll('#addForm .error-message').forEach(el => el.textContent = '');
            });
        }
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', () => {
                document.querySelectorAll('#editForm .error-message').forEach(el => el.textContent = '');
            });
        }

        // Валидация формы добавления
        const addForm = document.getElementById('addForm');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                let valid = true;
                const number = document.getElementById('newNumber').value.trim();
                if (!/^[\d]+(\.[\d]+)*$/.test(number)) {
                    document.getElementById('numberError').textContent = 'Введите корректный номер';
                    valid = false;
                } else document.getElementById('numberError').textContent = '';
                if (!document.getElementById('newCategoryName').value.trim()) {
                    document.getElementById('nameError').textContent = 'Введите категорию';
                    valid = false;
                } else document.getElementById('nameError').textContent = '';
                if (!document.getElementById('newCategoryShort').value.trim()) {
                    document.getElementById('shortError').textContent = 'Введите сокращение';
                    valid = false;
                } else document.getElementById('shortError').textContent = '';
                if (!document.getElementById('newDocumentsList').value.trim()) {
                    document.getElementById('docsError').textContent = 'Введите документы';
                    valid = false;
                } else document.getElementById('docsError').textContent = '';
                if (!document.getElementById('newPaymentFrequency').value.trim()) {
                    document.getElementById('frequencyError').textContent = 'Введите периодичность';
                    valid = false;
                } else document.getElementById('frequencyError').textContent = '';
                const amount = document.getElementById('newMaxAmount').value;
                if (amount === '' || isNaN(amount) || parseFloat(amount) < 0) {
                    document.getElementById('amountError').textContent = 'Введите сумму';
                    valid = false;
                } else document.getElementById('amountError').textContent = '';
                if (!valid) e.preventDefault();
            });
        }
    });
</script>
@endpush