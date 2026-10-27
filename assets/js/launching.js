(() => {
    // Elapsed time count-up timer since 26 Oct 2026 13:22:00 UTC
    const launchStart = Date.parse('2026-10-26T13:22:00Z');
    const timerEl = document.getElementById('elapsed-time');

    function updateElapsedTime() {
        if (!timerEl) {
            return;
        }
        const difference = Date.now() - launchStart;
        if (difference < 0) {
            timerEl.textContent = '00d 00h 00m 00s';
            return;
        }

        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);
        const pad = (n) => String(n).padStart(2, '0');

        timerEl.textContent = `${pad(days)}d ${pad(hours)}h ${pad(minutes)}m ${pad(seconds)}s`;
    }

    updateElapsedTime();
    window.setInterval(updateElapsedTime, 1000);

    const emailInput = document.querySelector('.waitlist input[type="email"]');
    if (emailInput) {
        emailInput.addEventListener('invalid', () => {
            if (emailInput.value.trim() === '') {
                emailInput.setCustomValidity('Enter a valid email');
            } else {
                emailInput.setCustomValidity('Please enter a valid email address!');
            }
        });
        emailInput.addEventListener('input', () => {
            emailInput.setCustomValidity('');
        });
    }
})();
