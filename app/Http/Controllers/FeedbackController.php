<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::latest()->get();

        return view('feedbacks', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'feedback'      => 'required|string|min:20',
        ]);

        $feedback = Feedback::create($validated);
        $feedback->refresh();

        return response()->json([
            'message'  => 'Feedback submitted successfully!',
            'feedback' => $feedback,
        ], 201);
    }

    public function updateStatus(Feedback $feedback)
    {
        $feedback->update(['status' => 'Reviewed']);

        return response()->json([
            'message' => 'Feedback marked as reviewed.',
            'status'  => $feedback->status,
        ]);
    }
}
