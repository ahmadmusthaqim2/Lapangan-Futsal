tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#10b981", secondary: "#f59e0b" },
            borderRadius: {
              none: "0px",
              sm: "4px",
              DEFAULT: "8px",
              md: "12px",
              lg: "16px",
              xl: "20px",
              "2xl": "24px",
              "3xl": "32px",
              full: "9999px",
              button: "8px",
            },
          },
        },
      };
document.addEventListener("DOMContentLoaded", function () {
  // ================ TOGGLE LOGIN/REGISTER ================
  const showRegisterBtn = document.getElementById("show-register");
  const showLoginBtn = document.getElementById("show-login");
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");

  if (showRegisterBtn && loginForm && registerForm) {
    showRegisterBtn.addEventListener("click", function (e) {
      e.preventDefault();
      loginForm.classList.add("hidden");
      registerForm.classList.remove("hidden");
    });
  }

  if (showLoginBtn && loginForm && registerForm) {
    showLoginBtn.addEventListener("click", function (e) {
      e.preventDefault();
      registerForm.classList.add("hidden");
      loginForm.classList.remove("hidden");
    });
  }

  // ================ TOGGLE PASSWORD VISIBILITY ================
  const togglePasswordBtns = document.querySelectorAll(".toggle-password");

  if (togglePasswordBtns.length > 0) {
    togglePasswordBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const container = this.closest(".relative");
        if (!container) return;

        const passwordField = container.querySelector("input");
        const icon = this.querySelector("i");

        if (!passwordField || !icon) return;

        if (passwordField.type === "password") {
          passwordField.type = "text";
          icon.classList.replace("ri-eye-off-line", "ri-eye-line");
        } else {
          passwordField.type = "password";
          icon.classList.replace("ri-eye-line", "ri-eye-off-line");
        }
      });
    });
  }

  // ================ PAGE NAVIGATION ================
  const loginButton = document.getElementById("login-button");
  const registerButton = document.getElementById("register-button");
  const nextToTimeButton = document.getElementById("next-to-time");
  const backToDateButton = document.getElementById("back-to-date");
  const nextToPaymentButton = document.getElementById("next-to-payment");
  const backToTimeButton = document.getElementById("back-to-time");
  
  const loginPage = document.getElementById("login-page");
  const userDatePage = document.getElementById("user-date-page");
  const timeSelectionPage = document.getElementById("time-selection-page");
  const paymentPage = document.getElementById("payment-page");

  // Navigasi dari login/register ke halaman utama
  const navigateToMain = () => {
    if (loginPage && userDatePage) {
      loginPage.classList.add("hidden");
      userDatePage.classList.remove("hidden");
    }
  };

  if (loginButton) loginButton.addEventListener("click", navigateToMain);
  if (registerButton) registerButton.addEventListener("click", navigateToMain);

  // Navigasi antar halaman utama
  if (nextToTimeButton && userDatePage && timeSelectionPage) {
    nextToTimeButton.addEventListener("click", () => {
      userDatePage.classList.add("hidden");
      timeSelectionPage.classList.remove("hidden");
    });
  }

  if (backToDateButton && userDatePage && timeSelectionPage) {
    backToDateButton.addEventListener("click", () => {
      timeSelectionPage.classList.add("hidden");
      userDatePage.classList.remove("hidden");
    });
  }

  if (nextToPaymentButton && timeSelectionPage && paymentPage) {
    nextToPaymentButton.addEventListener("click", () => {
      timeSelectionPage.classList.add("hidden");
      paymentPage.classList.remove("hidden");
    });
  }

  if (backToTimeButton && timeSelectionPage && paymentPage) {
    backToTimeButton.addEventListener("click", () => {
      paymentPage.classList.add("hidden");
      timeSelectionPage.classList.remove("hidden");
    });
  }

  // ================ PROFILE DROPDOWN ================
  const profileButton = document.getElementById("profile-button");
  const profileDropdown = document.getElementById("profile-dropdown");

  if (profileButton && profileDropdown) {
    profileButton.addEventListener("click", function () {
      profileDropdown.classList.toggle("hidden");
    });

    document.addEventListener("click", function (event) {
      const isClickInside = 
        profileButton.contains(event.target) || 
        profileDropdown.contains(event.target);
      
      if (!isClickInside) {
        profileDropdown.classList.add("hidden");
      }
    });
  }

  // ================ TIME SLOT SELECTION ================
  const timeSlots = document.querySelectorAll(".time-slot:not(.cursor-not-allowed)");
  const emptyState = document.querySelector(".empty-state");
  const selectedSlots = document.getElementById("selected-slots");

  if (timeSlots.length > 0 && emptyState && selectedSlots && nextToPaymentButton) {
    timeSlots.forEach((slot) => {
      slot.addEventListener("click", function () {
        this.classList.toggle("selected");
        
        const selectedCount = document.querySelectorAll(".time-slot.selected").length;
        const hasSelection = selectedCount > 0;
        
        emptyState.classList.toggle("hidden", hasSelection);
        selectedSlots.classList.toggle("hidden", !hasSelection);
        nextToPaymentButton.disabled = !hasSelection;
      });
    });
  }

  // ================ PAYMENT SELECTION ================
  const paymentOptions = document.querySelectorAll(".payment-option");
  const bankDetails = document.getElementById("bank-details");
  const ewalletDetails = document.getElementById("ewallet-details");
  const vaDetails = document.getElementById("va-details");

  if (paymentOptions.length > 0 && bankDetails && ewalletDetails && vaDetails) {
    paymentOptions.forEach((option) => {
      option.addEventListener("click", function () {
        const radio = this.querySelector('input[type="radio"]');
        if (!radio) return;
        
        radio.checked = true;
        paymentOptions.forEach(opt => opt.classList.remove("selected"));
        this.classList.add("selected");
        
        // Sembunyikan semua detail pembayaran
        [bankDetails, ewalletDetails, vaDetails].forEach(el => {
          el.classList.add("hidden");
        });
        
        // Tampilkan detail sesuai pilihan
        if (radio.id === "bank-transfer") bankDetails.classList.remove("hidden");
        else if (radio.id === "e-wallet") ewalletDetails.classList.remove("hidden");
        else if (radio.id === "virtual-account") vaDetails.classList.remove("hidden");
      });
    });
  }

        const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
              ];
              const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

              let currentDate = new Date();
              let selectedDate = new Date(currentDate);
              
              const savedDate = localStorage.getItem("selectedDate");
              if (savedDate) {
                selectedDate = new Date(savedDate); // Pakai tanggal yang disimpan
              } else {
                selectedDate = new Date(); // Default: hari ini
              }
              const calendarDaysEl = document.getElementById("calendar-days");
              const currentMonthEl = document.getElementById("current-month");
              const selectedDateEl = document.getElementById("selected-date");
              const prevMonthBtn = document.getElementById("prev-month");
              const nextMonthBtn = document.getElementById("next-month");

              function renderCalendar(date) {
                const year = date.getFullYear();
                const month = date.getMonth();

                currentMonthEl.textContent = `${monthNames[month]} ${year}`;

                calendarDaysEl.innerHTML = "";

                // Hari pertama di bulan ini (0=minggu,1=senin,...)
                const firstDayOfMonth = new Date(year, month, 1).getDay();

                // Jumlah hari di bulan ini
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Jumlah hari di bulan sebelumnya
                const daysInPrevMonth = new Date(year, month, 0).getDate();

                // Tampilkan hari dari bulan sebelumnya sebagai placeholder (abu-abu)
                for (let i = firstDayOfMonth - 1; i >= 0; i--) {
                  const day = daysInPrevMonth - i;
                  const dayEl = document.createElement("div");
                  dayEl.className = "text-center py-2 text-gray-400";
                  dayEl.textContent = day;
                  calendarDaysEl.appendChild(dayEl);
                }

                // Tampilkan hari bulan sekarang
                for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement("div");
                const thisDay = new Date(year, month, day);

                // Buat tanggal hari ini dengan jam 0:00:00 untuk perbandingan
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                thisDay.setHours(0, 0, 0, 0);

                // Tambahkan tampilan dasar
                dayEl.className = "text-center py-2 cursor-pointer selectable-day";
                dayEl.textContent = day;

                // Disable jika tanggal sudah lewat
                if (thisDay < today) {
                  dayEl.classList.add("text-gray-400", "cursor-not-allowed");
                } else {
                  dayEl.addEventListener("click", () => {
                    selectedDate = thisDay;
                    const year = selectedDate.getFullYear();
                    const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(selectedDate.getDate()).padStart(2, '0');
                    localStorage.setItem("selectedDate", `${year}-${month}-${day}`);
                    updateSelectedDateDisplay();
                    renderCalendar(currentDate);
                  });
                }

                // Tandai tanggal yang dipilih
                if (
                  selectedDate.getFullYear() === thisDay.getFullYear() &&
                  selectedDate.getMonth() === thisDay.getMonth() &&
                  selectedDate.getDate() === thisDay.getDate()
                ) {
                  dayEl.classList.add("bg-primary", "text-white", "rounded-full");
                }

                calendarDaysEl.appendChild(dayEl);
              }


                // Tampilkan hari dari bulan berikutnya agar grid tetap rapi (total 42 cells = 6 minggu)
                const totalCells = 42;
                const filledCells = firstDayOfMonth + daysInMonth;
                const nextDays = totalCells - filledCells;
                for (let i = 1; i <= nextDays; i++) {
                  const dayEl = document.createElement("div");
                  dayEl.className = "text-center py-2 text-gray-400";
                  dayEl.textContent = i;
                  calendarDaysEl.appendChild(dayEl);
                }
              }

              function updateSelectedDateDisplay() {
                const dayName = dayNames[selectedDate.getDay()];
                const dateNum = selectedDate.getDate();
                const monthName = monthNames[selectedDate.getMonth()];
                const year = selectedDate.getFullYear();

                selectedDateEl.textContent = `${dayName}, ${dateNum} ${monthName} ${year}`;
              }
              
              if (prevMonthBtn) {
                prevMonthBtn.addEventListener("click", () => {
                  currentDate.setMonth(currentDate.getMonth() - 1);
                  renderCalendar(currentDate);
                });
              }

              if (nextMonthBtn) {
                nextMonthBtn.addEventListener("click", () => {
                  currentDate.setMonth(currentDate.getMonth() + 1);
                  renderCalendar(currentDate);
                });
              }

              // Inisialisasi tampilan
              updateSelectedDateDisplay();
              renderCalendar(currentDate);

  // ================ PAYMENT COUNTDOWN ================
  const countdownTimer = document.getElementById("countdown-timer");
  
  if (countdownTimer) {
    const updateCountdown = () => {
      const now = new Date();
      const hours = 23 - now.getHours();
      const minutes = 59 - now.getMinutes();
      const seconds = 59 - now.getSeconds();
      
      countdownTimer.textContent = [
        hours.toString().padStart(2, "0"),
        minutes.toString().padStart(2, "0"),
        seconds.toString().padStart(2, "0")
      ].join(":");
    };

    updateCountdown();
    setInterval(updateCountdown, 1000);
  }
});