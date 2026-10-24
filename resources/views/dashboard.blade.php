<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 font-sans p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Banking Dashboard</h1>
            <button id="open-modal" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded shadow-sm transition">
                + New Transaction
            </button>
        </div>

        <!-- Message Alert -->
        <div id="message-container" class="hidden mb-4 p-3 rounded text-sm font-medium"></div>

        <!-- Balance Section (Simple) -->
        <div class="bg-gray-50 border border-gray-200 p-5 rounded-lg mb-6 flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase">Current Balance</p>
                <p id="acc-balance" class="text-3xl font-bold text-gray-900 mt-1">₱0.00</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Account ID: <span id="display-id" class="font-bold text-gray-700">1</span></p>
                <p class="mt-1">Min. Balance: ₱100.00</p>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mb-2">
            <h2 class="text-md font-semibold text-gray-700 mb-3">Recent Transactions</h2>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-200">
                            <th class="py-3 px-4">Reference #</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Notes</th>
                            <th class="py-3 px-4">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body" class="text-gray-700 text-sm divide-y divide-gray-200">
                        <!-- Data galing sa API -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="transaction-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-sm w-full p-6 shadow-lg border border-gray-200">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">New Transaction</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>
            <form id="banking-form">
                <div class="mb-3">
                    <label class="block text-gray-600 text-xs font-semibold mb-1">Account ID:</label>
                    <input type="number" id="account_id" name="account_id" value="1" class="border border-gray-300 rounded w-full py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-600 text-xs font-semibold mb-1">Transaction Type:</label>
                    <select name="type" id="type" class="border border-gray-300 rounded w-full py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="deposit">Deposit</option>
                        <option value="withdrawal">Withdrawal</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-600 text-xs font-semibold mb-1">Amount (₱):</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="border border-gray-300 rounded w-full py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500" required placeholder="0.00">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-xs font-semibold mb-1">Notes (Optional):</label>
                    <input type="text" name="notes" id="notes" class="border border-gray-300 rounded w-full py-1.5 px-3 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Optional notes">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded transition">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#open-modal').on('click', function() {
            $('#transaction-modal').removeClass('hidden');
        });
        $('#close-modal').on('click', function() {
            $('#transaction-modal').addClass('hidden');
        });

        function loadDashboardData() {
            let accountId = $('#account_id').val() || 1;
            $('#display-id').text(accountId);
            
            $.ajax({
                url: '/api/accounts/' + accountId,
                method: 'GET',
                success: function(data) {
                    $('#acc-balance').text('₱' + parseFloat(data.balance).toLocaleString('en-US', {minimumFractionDigits: 2}));
                }
            });

            $.ajax({
                url: '/api/transactions/' + accountId,
                method: 'GET',
                success: function(transactions) {
                    let rows = '';
                    if(transactions.length === 0) {
                        rows = `<tr><td colspan="5" class="py-4 px-4 text-center text-gray-400 text-sm">No recent transactions</td></tr>`;
                    } else {
                        transactions.forEach(tx => {
                            let badgeColor = tx.type === 'deposit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                            
                            let d = new Date(tx.created_at);
                            let formattedDate = (d.getMonth() + 1) + '/' + d.getDate() + '/' + d.getFullYear() + ' ' + 
                                                d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                            rows += `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-mono text-xs text-gray-600">${tx.reference_number || '---'}</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-xs font-semibold ${badgeColor}">${tx.type.toUpperCase()}</span></td>
                                    <td class="py-3 px-4 font-semibold text-gray-800">₱${parseFloat(tx.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">${tx.notes || '---'}</td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">${formattedDate}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#transactions-table-body').html(rows);
                }
            });
        }

        $(document).ready(function() {
            loadDashboardData();
        });

        $('#banking-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/transactions',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#transaction-modal').addClass('hidden');
                    $('#message-container').removeClass('hidden bg-red-50 text-red-700').addClass('bg-green-50 text-green-700').text(response.message || 'Transaction successful!');
                    $('#amount').val('');
                    $('#notes').val('');
                    loadDashboardData();
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON?.message || 'Transaction failed.';
                    $('#message-container').removeClass('hidden bg-green-50 text-green-700').addClass('bg-red-50 text-red-700').text(errMsg);
                }
            });
        });
    </script>
</body>