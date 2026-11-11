<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');

        $reviews = Review::with(['user', 'product.farmer'])
            ->when($status === 'menunggu', fn ($q) => $q->pending())
            ->when($status === 'disetujui', fn ($q) => $q->approved())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'status' => $status,
            'pendingCount' => Review::pending()->count(),
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        if ($review->is_approved) {
            return back()->with('error', 'Ulasan sudah disetujui.');
        }

        $review->update(['is_approved' => true]);

        return back()->with('success', 'Ulasan disetujui dan ditampilkan.');
    }

    /** Tolak = hapus ulasan; pembeli dapat menulis ulang yang sesuai ketentuan. */
    public function reject(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Ulasan ditolak dan dihapus.');
    }
}
