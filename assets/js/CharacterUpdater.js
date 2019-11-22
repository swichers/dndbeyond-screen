'use strict';

/**
 * A helper class to manage character updates.
 */
export default class CharacterUpdater {

  /**
   * Create a CharacterUpdater instance.
   *
   * @param {int} characterId
   *   The character Id to monitor.
   */
  constructor(characterId) {
    this.characterId = characterId;
    // The default time to wait between updates (5min).
    this.timerInterval = 5 * 60 * 1000;

    this.startTimer();
  }

  /**
   * Updates the character information in its card.
   *
   * @returns {boolean}
   *   TRUE if an update was initiated, FALSE otherwise.
   */
  update() {

    if ( ! this.getCard()) {
      return false;
    }

    this.onLoading();

    fetch(this.getUpdateUrl(), {cache: 'no-cache'})
      .then((response) => {
        return response.text().then(data => {
          if (response.ok) {
            return data;
          }

          return Promise.reject({status: response.status, data});
        });
      })
      .then((data) => {
        this.onLoaded(data);
      })
      .catch((e) => {
        this.onError(e);
      });

    return true;
  }

  /**
   * Handle the new character data being returned.
   *
   * @param {string} data
   *   The new character HTML.
   */
  onLoaded(data) {
    let card = this.getCard();
    if (card) {
      card.outerHTML = data;
    }
  }

  /**
   * Handle loading.
   */
  onLoading() {
    let card = this.getCard();
    if (card) {
      card.querySelector('.load-status').classList.remove('d-none');
    }
  }

  /**
   * Handle a loading error.
   *
   * @param e
   *   The error object.
   */
  onError(e) {
    let card = this.getCard();
    if (card) {
      card.querySelector('.load-status').classList.add('d-none');
      card.querySelector('.load-error').classList.remove('d-none');
    }

    this.stopTimer();

    console.error(e);
  }

  /**
   * Start the update timer.
   */
  startTimer() {
    this.timerId = setInterval(() => this.update(), this.timerInterval);
  }

  /**
   * Stop the update timer.
   */
  stopTimer() {
    clearInterval(this.timerId);
  }

  /**
   * The selector to use to find the character card.
   *
   * @returns {string}
   *   The CSS selector to use.
   */
  getCharacterSelector() {
    return '.character[data-character-id="' + this.characterId + '"]';
  }

  /**
   * The Url to use when requesting updated markup.
   *
   * @returns {string}
   *   The Url to use.
   */
  getUpdateUrl() {
    return '/' + this.characterId + '/update';
  }

  /**
   * Get the DOM element that is the character card.
   *
   * @returns {Element}
   *   The character card DOM element.
   */
  getCard() {
    return document.querySelector(this.getCharacterSelector());
  }
}
