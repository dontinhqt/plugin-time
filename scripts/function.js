function startTimer(duration, display, running = true) {
    var timer = duration, minutes, seconds;
    if (!running) {
        clearInterval(this.x    );
        return;
    }
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
//
// jQuery(function ($) {
//     console.log("data: ", jsTimer);
//     var guiPause = $('#countdown-timer-pause');
//     var guiResume = $('#countdown-timer-resume').hide();
//     var display = $('#countdown-timer');
//     var running = true;
//     var minutes = 60 * 30;
//
//     var pause = function() {
//         running = false;
//         guiPause.hide();
//         guiResume.show();
//         startTimer(minutes, display, false);
//
//     };
//
//     var resume = function() {
//         running = true;
//         guiPause.show();
//         guiResume.hide();
//         startTimer(minutes, display, false);
//     };
//
//     jQuery('#countdown-timer-pause').on('click', pause);
//     jQuery('#countdown-timer-resume').on('click', resume);
//
//     startTimer(minutes, display);
// });

var CountDown = (function ($) {
    // Length ms
    var TimeOut = 10000;
    // Interval ms
    var TimeGap = 1000;

    var CurrentTime = ( new Date() ).getTime();
    var EndTime = ( new Date() ).getTime() + TimeOut;

    var GuiTimer = $('#countdown-timer');
    var GuiPause = $('#countdown-timer-pause');
    var GuiResume = $('#countdown-timer-resume').hide();

    var Running = true;

    var UpdateTimer = function() {

        // Run till timeout
        if( CurrentTime + TimeGap < EndTime ) {
            setTimeout( UpdateTimer, TimeGap );
        }
        // Countdown if running
        if( Running ) {
            CurrentTime += TimeGap;
            if( CurrentTime >= EndTime ) {
                GuiTimer.css('color','red');
            }
        }
        // Update Gui
        var Time = new Date();
        Time.setTime( EndTime - CurrentTime );
        var Minutes = Time.getMinutes();
        var Seconds = Time.getSeconds();
        console.log("===");
        GuiTimer.html(
            (Minutes < 10 ? '0' : '') + Minutes
            + ':'
            + (Seconds < 10 ? '0' : '') + Seconds );
    };

    var Pause = function() {
        Running = false;
        GuiPause.hide();
        GuiResume.show();
    };

    var Resume = function() {
        Running = true;
        GuiPause.show();
        GuiResume.hide();
    };

    var Start = function( Timeout ) {
        TimeOut = Timeout;
        CurrentTime = ( new Date() ).getTime();
        EndTime = ( new Date() ).getTime() + TimeOut;
        UpdateTimer();
    };

    return {
        Pause: Pause,
        Resume: Resume,
        Start: Start
    };
})(jQuery);

jQuery('#countdown-timer-pause').on('click',CountDown.Pause);
jQuery('#countdown-timer-resume').on('click',CountDown.Resume);

// ms
CountDown.Start(120000);