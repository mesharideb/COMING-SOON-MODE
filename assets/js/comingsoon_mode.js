(function (jQuery, Drupal, drupalSettings) {
  Drupal.behaviors.comingsoonCountdown = {
    attach: function (context, settings) {
      
      const elements = once('comingsoonCountdown', '.countdown', context);
      elements.forEach(function (element) {
        
        const countdown = jQuery(element);
        const output = countdown.html();

        const countDownDate = function () {
          let timeleft = new Date(countdown.attr('data-count')).getTime() - new Date().getTime();

          let days = Math.floor(timeleft / (1000 * 60 * 60 * 24));
          let hours = Math.floor((timeleft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          let minutes = Math.floor((timeleft % (1000 * 60 * 60)) / (1000 * 60));
          let seconds = Math.floor((timeleft % (1000 * 60)) / 1000);

          countdown.html(output.replace('%d', days).replace('%h', hours).replace('%m', minutes).replace('%s', seconds));
          requestAnimationFrame(countDownDate);
        }

        if (!countdown.attr('data-count')) {
          return;
        } else {
          countDownDate();
          setInterval(countDownDate, 1000);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
