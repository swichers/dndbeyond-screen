'use strict';

/**
 * Keeps a container updated with time since.
 */
export default class TimeSince {

  /**
   * Init TimeSince monitoring.
   */
  constructor() {
    this.timerTimeId = setInterval(() => { this.updateTimes(); }, 1000);
  }

  /**
   * Updates the timestamp since monitoring started.
   */
  updateTimes() {
    let timeTrackingContainers = document.querySelectorAll('.timeago');
    timeTrackingContainers.forEach(function (element) {
      // Adjust timestamps to account for ms.
      let timestamp = element.getAttribute('datetime') * 1000;
      element.textContent = this.timeSince(timestamp || Date.now());
    }.bind(this));
  }

  /**
   * Calculate the amount of time from the given date.
   *
   * @param {int} date
   *   The starting timestamp.
   * @returns {string}
   *   How long it has been since the given timestamp.
   */
  timeSince(date) {
    let second = 1;
    let minute = second * 60;
    let hour   = minute * 60;
    let day    = hour   * 24;
    let month  = day    * 30;
    let year   = day    * 365;

    let suffix = ' ago';

    let elapsed = Math.floor((Date.now() - date) / 1000);

    if (elapsed < 10) {
        return 'just now';
    }

    let a = elapsed < minute && [Math.floor(elapsed / second), 'second'] ||
            elapsed < hour   && [Math.floor(elapsed / minute), 'minute'] ||
            elapsed < day    && [Math.floor(elapsed / hour), 'hour']     ||
            elapsed < month  && [Math.floor(elapsed / day), 'day']       ||
            elapsed < year   && [Math.floor(elapsed / month), 'month']   ||
            [Math.floor(elapsed / year), 'year'];

    return a[0] + ' ' + a[1] + (a[0] === 1 ? '' : 's') + suffix;
  }

}
