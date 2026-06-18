<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Feedback Tracker</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f3f4f6; color: #1f2937; margin: 0; padding: 2rem 1rem;
        }
        .wrap { max-width: 960px; margin: 0 auto; }
        h1 { font-size: 1.6rem; margin: 0 0 1.5rem; }
        h2 { font-size: 1.1rem; margin: 0 0 1rem; }
        .card {
            background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem;
            padding: 1.5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05); margin-bottom: 2rem;
        }
        .field { margin-bottom: 1rem; }
        label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; }
        input, textarea {
            width: 100%; padding: .6rem .75rem; border: 1px solid #d1d5db;
            border-radius: .5rem; font-size: .95rem; font-family: inherit;
        }
        input:focus, textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .error { color: #dc2626; font-size: .8rem; margin-top: .3rem; display: none; }
        button {
            background: #4f46e5; color: #fff; border: none; padding: .6rem 1.1rem;
            border-radius: .5rem; font-size: .9rem; font-weight: 600; cursor: pointer;
        }
        button:hover { background: #4338ca; }
        button:disabled { opacity: .6; cursor: not-allowed; }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1rem; display: none; font-size: .9rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .7rem .75rem; border-bottom: 1px solid #f0f0f0; font-size: .9rem; }
        th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
        .badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-reviewed { background: #dbeafe; color: #1e40af; }
        .btn-sm { padding: .35rem .7rem; font-size: .8rem; background: #059669; }
        .btn-sm:hover { background: #047857; }
        .empty { text-align: center; color: #9ca3af; padding: 1.5rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Customer Feedback Tracker</h1>

        <div class="card">
            <h2>Submit Feedback</h2>
            <div id="successAlert" class="alert alert-success"></div>
            <form id="feedbackForm">
                @csrf
                <div class="field">
                    <label for="customer_name">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name">
                    <div class="error" data-error="customer_name"></div>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                    <div class="error" data-error="email"></div>
                </div>
                <div class="field">
                    <label for="feedback">Feedback <span style="font-weight:400;color:#9ca3af">(min 20 characters)</span></label>
                    <textarea id="feedback" name="feedback" rows="4"></textarea>
                    <div class="error" data-error="feedback"></div>
                </div>
                <button type="submit" id="submitBtn">Submit Feedback</button>
            </form>
        </div>

        <div class="card">
            <h2>All Feedback</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="feedbackTable">
                    @forelse ($feedbacks as $feedback)
                        <tr data-id="{{ $feedback->id }}">
                            <td>{{ $feedback->customer_name }}</td>
                            <td>{{ $feedback->email }}</td>
                            <td class="status-cell">
                                <span class="badge {{ $feedback->status === 'Reviewed' ? 'badge-reviewed' : 'badge-pending' }}">
                                    {{ $feedback->status }}
                                </span>
                            </td>
                            <td class="action-cell">
                                @if ($feedback->status !== 'Reviewed')
                                    <button class="btn-sm mark-reviewed" data-id="{{ $feedback->id }}">Mark Reviewed</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow"><td colspan="4" class="empty">No feedback submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            $('#feedbackForm').on('submit', function (e) {
                e.preventDefault();

                $('.error').hide().text('');
                $('#successAlert').hide();

                if (!validateForm()) {
                    return;
                }

                const $btn = $('#submitBtn');
                $btn.prop('disabled', true).text('Submitting...');

                $.ajax({
                    url: '{{ route('feedback.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#successAlert').text(res.message).show();
                        addRow(res.feedback);
                        $('#feedbackForm')[0].reset();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, messages) {
                                $('[data-error="' + field + '"]').text(messages[0]).show();
                            });
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Submit Feedback');
                    }
                });
            });

            $('#feedbackTable').on('click', '.mark-reviewed', function () {
                const $btn = $(this);
                const id = $btn.data('id');
                $btn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: '/feedback/' + id + '/status',
                    type: 'PATCH',
                    success: function (res) {
                        const $row = $('tr[data-id="' + id + '"]');
                        $row.find('.status-cell').html(
                            '<span class="badge badge-reviewed">' + res.status + '</span>'
                        );
                        $row.find('.action-cell').empty();
                    },
                    error: function () {
                        alert('Could not update status. Please try again.');
                        $btn.prop('disabled', false).text('Mark Reviewed');
                    }
                });
            });

            function validateForm() {
                let valid = true;
                const name = $('#customer_name').val().trim();
                const email = $('#email').val().trim();
                const feedback = $('#feedback').val().trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (name === '') {
                    showError('customer_name', 'The customer name field is required.');
                    valid = false;
                }

                if (email === '') {
                    showError('email', 'The email field is required.');
                    valid = false;
                } else if (!emailPattern.test(email)) {
                    showError('email', 'Please enter a valid email address.');
                    valid = false;
                }

                if (feedback === '') {
                    showError('feedback', 'The feedback field is required.');
                    valid = false;
                } else if (feedback.length < 20) {
                    showError('feedback', 'The feedback must be at least 20 characters.');
                    valid = false;
                }

                return valid;
            }

            function showError(field, message) {
                $('[data-error="' + field + '"]').text(message).show();
            }

            function addRow(fb) {
                $('#emptyRow').remove();
                const row =
                    '<tr data-id="' + fb.id + '">' +
                        '<td>' + escapeHtml(fb.customer_name) + '</td>' +
                        '<td>' + escapeHtml(fb.email) + '</td>' +
                        '<td class="status-cell"><span class="badge badge-pending">' + fb.status + '</span></td>' +
                        '<td class="action-cell"><button class="btn-sm mark-reviewed" data-id="' + fb.id + '">Mark Reviewed</button></td>' +
                    '</tr>';
                $('#feedbackTable').prepend(row);
            }

            function escapeHtml(str) {
                return $('<div>').text(str).html();
            }
        });
    </script>
</body>
</html>
