
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | DeusAPP</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            color: #1f2937;
        }

        .card {
            width: 100%;
            max-width: 430px;
            padding: 32px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            font-size: 26px;
        }

        .description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin: 16px 0 8px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #message {
            display: none;
            margin-top: 18px;
            padding: 12px;
            border-radius: 8px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .success {
            display: block !important;
            color: #166534;
            background: #dcfce7;
        }

        .error {
            display: block !important;
            color: #991b1b;
            background: #fee2e2;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Reset your password</h1>
        <p class="description">
            Create a new password for your DeusAPP account.
        </p>

        <form id="resetForm">
            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ $email }}"
                autocomplete="email"
                required
            >

            <label for="password">New password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="password_confirmation">Confirm new password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit" id="submitButton">
                Update Password
            </button>

            <div id="message" role="status" aria-live="polite"></div>
        </form>
    </main>

    <script>
        const resetToken = @json($token);
        const resetForm = document.getElementById('resetForm');
        const submitButton = document.getElementById('submitButton');
        const messageBox = document.getElementById('message');

        resetForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            messageBox.className = '';
            messageBox.style.display = 'none';
            messageBox.textContent = '';
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';

            const payload = {
                token: resetToken,
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                password_confirmation:
                    document.getElementById('password_confirmation').value
            };

            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) {
                    const errors = result.errors
                        ? Object.values(result.errors).flat().join(' ')
                        : result.message;

                    throw new Error(errors || 'Unable to reset password.');
                }

                messageBox.textContent =
                    result.message || 'Your password has been updated successfully.';
                messageBox.className = 'success';
                resetForm.reset();
            } catch (error) {
                messageBox.textContent =
                    error.message || 'A connection error occurred. Please try again.';
                messageBox.className = 'error';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Update Password';
            }
        });
    </script>
</body>
</html>
