function startTimer(duration, display, running = true) {
    var timer = duration, minutes, seconds;
    var x = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.text(minutes + ":" + seconds);
        if (--timer < 0) {
            clearInterval(x);
            display.text('EXPIRED');
            document.cookie = jsTimer.cookieName +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            window.location = document.location.href;
        }
    }, 1000);
}

jQuery(function ($) {
    var display = $('#countdown-timer');
    var timeClock = jQuery('.ymese-countdown-timer').data('time_expired');
    if (timeClock) {
        var minutes = 60 * timeClock;
        startTimer(minutes, display);
    }
});