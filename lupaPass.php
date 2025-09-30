<?php
    session_start();
    include 'conf/koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Court Booking</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            background: url('img/bg.jpg');
            background-size: cover;
            background-position: center;
        }
        input:focus {
            outline: none;
        }
        .success-animation {
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="auth-container w-full min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 relative z-10">
                <div class="text-center mb-8">
                    <h1 class="font-['Pacifico'] text-3xl text-primary">Futsal Court</h1>
                    <p class="text-gray-600 mt-2">Booking lapangan futsal jadi lebih mudah</p>
                </div>
                
                <div id="reset-form">
                    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Reset Password</h2>
                    <p class="text-gray-600 mb-6">Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password.</p>
                    <form id="password-reset-form">
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="reset-email">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="ri-mail-line text-gray-400"></i>
                                </div>
                                <input type="email" id="reset-email" name="email" required 
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" 
                                       placeholder="Masukkan email Anda"/>
                            </div>
                        </div>
                        <button type="submit" id="submit-button"
                                class="w-full bg-primary text-white py-2 px-4 rounded font-medium hover:bg-primary/90 transition-colors whitespace-nowrap mb-4">
                            Kirim Link Reset
                        </button>
                    </form>
                    <div class="text-center">
                        <a href="index.php" onclick="goBackToLogin()" class="text-primary font-medium hover:underline">Kembali ke Login</a>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div id="success-message" class="hidden text-center">
                    <div class="success-animation">
                        <div class="w-16 h-16 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-4">
                            <div class="w-10 h-10 flex items-center justify-center bg-primary rounded-full">
                                <i class="ri-check-line text-white text-xl"></i>
                            </div>
                        </div>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">
                            Link reset password telah dikirim ke email Anda.
                        </h2>
                        <p class="text-gray-600 mb-6">
                            Silahkan cek inbox atau folder spam Anda untuk instruksi selanjutnya. Link reset akan kedaluwarsa dalam 24 jam.
                        </p>
                        <div class="space-y-3">
                            <p class="text-gray-500 text-sm">Tidak menerima email? Cek folder spam atau</p>
                            <button id="resend-link" class="text-primary font-medium hover:underline">Kirim Ulang Link</button>
                        </div>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="index.php" onclick="goBackToLogin()" class="inline-flex items-center text-primary font-medium hover:underline">
                                <i class="ri-arrow-left-line mr-2"></i>
                                Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize EmailJS with better error handling
        let emailjsInitialized = false;
        
        function initializeEmailJS() {
            try {
                if (typeof emailjs === 'undefined') {
                    throw new Error('EmailJS library not loaded');
                }
                
                emailjs.init({
                    publicKey: 'RMzUy095HRMZGl5gO'
                });
                
                emailjsInitialized = true;
                console.log('EmailJS initialized successfully');
            } catch (error) {
                console.error('Failed to initialize EmailJS:', error);
                emailjsInitialized = false;
            }
        }

        // Initialize when page loads
        window.addEventListener('load', function() {
            setTimeout(initializeEmailJS, 1000); // Delay to ensure EmailJS is loaded
        });

        document.addEventListener("DOMContentLoaded", function () {
            const resetForm = document.getElementById("reset-form");
            const successMessage = document.getElementById("success-message");
            const passwordResetForm = document.getElementById("password-reset-form");
            const resendLinkBtn = document.getElementById("resend-link");
            const resetEmailInput = document.getElementById("reset-email");
            const submitButton = document.getElementById("submit-button");

            // Generate reset token
            function generateResetToken() {
                return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            }

            // Send reset email using EmailJS with better debugging
            function sendResetEmail(email, resetToken) {
            if (!emailjsInitialized) {
                throw new Error('EmailJS not initialized. Please refresh the page and try again.');
            }

            // Perbaiki cara generate reset link
            const currentURL = window.location.href;
            const baseURL = currentURL.replace(/\/[^\/]*$/, ''); // Hapus file saat ini dari URL
            
            // Atau lebih sederhana, gunakan origin dan pathname
            const resetLink = `${window.location.origin}${window.location.pathname.replace('lupaPass.php', 'reset-password.php')}?token=${resetToken}`;
            
            console.log('Generated reset link:', resetLink);
            
            const templateParams = {
                email: email,
                reset_link: resetLink,
                user_name: email.split('@')[0],
                expires_in: '24 jam'
            };

            console.log('=== EmailJS Debug Info ===');
            console.log('Service ID:', 'service_gibufwe');
            console.log('Template ID:', 'template_9cqym1s');
            console.log('Template Params:', templateParams);

            return emailjs.send('service_gibufwe', 'template_9cqym1s', templateParams)
                .then(function(response) {
                    console.log('Email sent successfully:', response);
                    
                    return fetch('save_reset_token.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            token: resetToken
                        })
                    });
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to save token to database');
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('Token saved to database:', data);
                    return { success: true, message: 'Email sent and token saved' };
                })
                .catch(function(error) {
                    console.error('Error in reset process:', error);
                    throw error;
                });
        }
            

            // Form submission handler
            passwordResetForm.addEventListener("submit", function (e) {
                e.preventDefault();

                const email = resetEmailInput.value.trim();
                if (!email) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Diperlukan',
                        text: 'Silakan masukkan alamat email Anda.',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }

                // Validate email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format Email Salah',
                        text: 'Silakan masukkan alamat email yang valid.',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }

                // Add loading state
                submitButton.innerHTML = '<i class="ri-loader-2-line animate-spin mr-2"></i>Mengirim...';
                submitButton.disabled = true;

                // Generate reset token
                const resetToken = generateResetToken();

                // Send email using EmailJS
                sendResetEmail(email, resetToken)
                    .then(function(response) {
                        console.log('Email sent successfully:', response);
                        
                        // Store email for resend functionality (use variable instead of localStorage)
                        window.lastResetEmail = email;
                        
                        // Show success message
                        resetForm.classList.add("hidden");
                        successMessage.classList.remove("hidden");
                        
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Terkirim!',
                            text: 'Link reset password telah dikirim ke email Anda.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    })
                    .catch(function(error) {
                        console.error('Failed to send email:', error);
                        
                        // More detailed error handling
                        let errorMessage = 'Terjadi kesalahan saat mengirim email.';
                        
                        if (error.text) {
                            console.error('EmailJS Error Details:', error.text);
                            if (error.text.includes('Invalid')) {
                                errorMessage = 'Konfigurasi email tidak valid. Silakan hubungi administrator.';
                            } else if (error.text.includes('Unauthorized')) {
                                errorMessage = 'Tidak memiliki izin untuk mengirim email. Silakan hubungi administrator.';
                            } else if (error.text.includes('Network')) {
                                errorMessage = 'Masalah koneksi internet. Silakan periksa koneksi Anda.';
                            }
                        } else if (error.message) {
                            errorMessage = error.message;
                        }
                        
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim Email',
                            text: errorMessage,
                            confirmButtonColor: '#10b981',
                            footer: 'Periksa console untuk detail error (F12)'
                        });
                    })
                    .finally(function() {
                        // Reset button state
                        submitButton.innerHTML = 'Kirim Link Reset';
                        submitButton.disabled = false;
                    });
            });

            // Resend link handler
            resendLinkBtn.addEventListener("click", function () {
                const savedEmail = window.lastResetEmail;
                if (savedEmail) {
                    resetEmailInput.value = savedEmail;
                }
                
                successMessage.classList.add("hidden");
                resetForm.classList.remove("hidden");
                resetEmailInput.focus();
            });

            // Email validation
            resetEmailInput.addEventListener("input", function () {
                const email = this.value.trim();
                const isValid = email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

                if (isValid) {
                    this.classList.remove("border-red-300");
                    this.classList.add("border-gray-300");
                    submitButton.disabled = false;
                    submitButton.classList.remove("bg-gray-300", "cursor-not-allowed");
                    submitButton.classList.add("bg-primary", "hover:bg-primary/90");
                } else if (email) {
                    this.classList.add("border-red-300");
                    this.classList.remove("border-gray-300");
                    submitButton.disabled = true;
                    submitButton.classList.add("bg-gray-300", "cursor-not-allowed");
                    submitButton.classList.remove("bg-primary", "hover:bg-primary/90");
                }
            });

            // Add floating label effect
            resetEmailInput.addEventListener("focus", function() {
                this.parentElement.parentElement.querySelector("label").classList.add("text-primary");
            });

            resetEmailInput.addEventListener("blur", function() {
                this.parentElement.parentElement.querySelector("label").classList.remove("text-primary");
            });
        });

        // Function to handle back to login
        function goBackToLogin() {
            // Replace with your actual login page URL
            window.location.href = 'index.php';
        }
    </script>
    <script src="js/script.js"></script>
    <script src="https://kit.fontawesome.com/ef9e5793a4.js" crossorigin="anonymous"></script>
</body>
</html>