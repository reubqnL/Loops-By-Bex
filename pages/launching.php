<!DOCTYPE html>
<html lang="en">
<body>
<main>
    <h1>Loops By Bex is launching soon</h1>
    <p id="elapsed-time"></p>

    <div class="waitlist">
        <form>
            <input type="email" placeholder="Your best email address">
        </form>
        <button class="joinWaitlist" onClick="joinWaitlist()">Join Waitlist</button>
    </div>
</main>

<script>
    const launchDate = new Date("Oct 26, 2026 13:22:00").getTime();

    function updateElapsedTime() {
        const now = new Date().getTime();

        const difference = now - launchDate;

        if (difference < 0) {
            document.getElementById("elapsed-time").innerHTML = "00d 00h 00m 00s";
            return;
        }

        // absolute math without capping days into weeks/months
        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);

        const format = num => String(num).padStart(2, '0');

        document.getElementById("elapsed-time").innerHTML =
            `${format(days)}d ${format(hours)}h ${format(minutes)}m ${format(seconds)}s`;
    }

    updateElapsedTime();

    setInterval(updateElapsedTime, 1000);
</script>
<script>
    function joinWaitlist() {
        return null
    }
</script>

</body>
</html>