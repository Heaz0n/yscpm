<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderByRaw("
            CAST(SUBSTRING_INDEX(number, '.', 1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(number, '.', 2), '.', -1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(number, '.', 3), '.', -1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(number, '.', 4), '.', -1) AS UNSIGNED)
        ")->get();

        $payoutCount = session('payout_count', 4);

        return view('categories.index', compact('categories', 'payoutCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|regex:/^[\d]+(\.[\d]+)*$/',
            'category_name' => 'required|string|max:500',
            'category_short' => 'required|string|max:255',
            'documents_list' => 'required|string',
            'payment_frequency' => 'required|string|max:255',
            'max_amount' => 'required|numeric|min:0',
            'condition' => 'required|in:fixed,expense_limit',
        ]);

        Category::create([
            'number' => $validated['number'],
            'category_name' => $validated['category_name'],
            'category_short' => $validated['category_short'],
            'documents_list' => $validated['documents_list'],
            'payment_frequency' => $validated['payment_frequency'],
            'max_amount' => $validated['max_amount'],
            'amount_condition' => $validated['condition'],
        ]);

        return redirect()->route('categories.index')->with('notification', 'Категория успешно добавлена!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'number' => 'required|regex:/^[\d]+(\.[\d]+)*$/',
            'category_name' => 'required|string|max:500',
            'category_short' => 'required|string|max:255',
            'documents_list' => 'required|string',
            'payment_frequency' => 'required|string|max:255',
            'max_amount' => 'required|numeric|min:0',
            'condition' => 'required|in:fixed,expense_limit',
        ]);

        $category->update([
            'number' => $validated['number'],
            'category_name' => $validated['category_name'],
            'category_short' => $validated['category_short'],
            'documents_list' => $validated['documents_list'],
            'payment_frequency' => $validated['payment_frequency'],
            'max_amount' => $validated['max_amount'],
            'amount_condition' => $validated['condition'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Категория успешно обновлена!']);
        }

        return redirect()->route('categories.index')->with('notification', 'Категория успешно обновлена!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect()->route('categories.index')->with('notification', 'Категория успешно удалена!');
    }

    public function savePayoutCount(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:2|max:4',
        ]);

        session(['payout_count' => $request->count]);

        return response()->json(['status' => 'success']);
    }
}